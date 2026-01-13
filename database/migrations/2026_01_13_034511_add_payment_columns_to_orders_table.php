<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->nullable()->after('status');
            $table->timestamp('payment_time')->nullable()->after('payment_method');
            $table->string('midtrans_transaction_id')->nullable()->after('payment_time');
            $table->text('midtrans_response')->nullable()->after('midtrans_transaction_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_time', 'midtrans_transaction_id', 'midtrans_response']);
        });
    }
};