<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMerchants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('phone', 20);
            $table->uuid('merchant_id')->unique();
            $table->string('api_key', 30);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('transaction_fee', 5, 4)->default(0);
            $table->string('callback_url');
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->string('password', 60);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
}
