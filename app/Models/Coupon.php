<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('usage_count')
            ->withTimestamps();
    }

    public function isValid(): bool
    {
        return $this->is_active 
            && Carbon::now()->between($this->start_date, $this->end_date)
            && ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    public function canBeUsedBy($userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->per_user_limit === null) {
            return true;
        }

        $userUsage = $this->users()->where('user_id', $userId)->first();
        
        return $userUsage === null || $userUsage->pivot->usage_count < $this->per_user_limit;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->min_purchase && $amount < $this->min_purchase) {
            return 0;
        }

        $discount = $this->type === 'percentage' 
            ? ($amount * $this->value / 100) 
            : $this->value;

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return round($discount, 2);
    }

    public function incrementUsage($userId): void
    {
        $this->increment('usage_count');

        $userPivot = $this->users()->where('user_id', $userId)->first();
        
        if ($userPivot) {
            $this->users()->updateExistingPivot($userId, [
                'usage_count' => $userPivot->pivot->usage_count + 1,
            ]);
        } else {
            $this->users()->attach($userId, ['usage_count' => 1]);
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now());
    }
}