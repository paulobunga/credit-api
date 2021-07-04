<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;

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
            'online_bank',
            'upi',
            'wallet',
            'credit_card',
        ];
        foreach ($methods as $method) {
            PaymentMethod::create([
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
