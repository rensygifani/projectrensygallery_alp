<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // $table->string('order_code')->unique()->after('id');
            // $table->string('order_code')->nullable()->after('id');

$table->unsignedBigInteger('subtotal')->default(0)->after('payment_method');
$table->unsignedBigInteger('ongkir')->default(0)->after('subtotal');

            $table->unsignedBigInteger('total')->change();
            $table->string('status')->default('pending')->after('total');
            $table->string('courier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
