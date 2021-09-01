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
            $table->uuid('uuid')->unique();
            $table->string('name')->unique()->index();
            $table->string('username')->unique()->index();
            $table->string('phone', 20);
            $table->string('api_key', 30);
            $table->string('callback_url');
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->string('password', 60);
            $table->timestamps();
        });

        Schema::create('merchant_white_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->json('api')->default(new Expression('(JSON_ARRAY())'));
            $table->json('backend')->default(new Expression('(JSON_ARRAY())'));
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('merchant_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('currency', 6);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('transaction_fee', 5, 4)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['merchant_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_white_lists');
        Schema::dropIfExists('merchant_credits');
        Schema::dropIfExists('merchants');
    }
}
