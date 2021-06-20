<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // By Cash, By Bank Transfer, By Other
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->timestamps();
        });
        // insert data
        $methods = [
            'BY_CASH',
            'BY_BANK_TRANSFER',
            'BY_CREDIT_CARD',
            'BY_WALLET',
            'BY_OTHER'
        ];
        foreach ($methods as $method) {
            DB::table('payment_methods')->insert([
                'name' => $method,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
