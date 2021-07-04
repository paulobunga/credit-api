<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateTableMerchantDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('reseller_bank_card_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('order_id', 20)->unique();
            $table->string('merchant_order_id', 60);
            $table->decimal('amount', 14, 4);
            $table->unsignedTinyInteger('status')
                  ->comment('1:Created,2:Waiting to Approve,3:Approved,4:Rejected,5:Enforced,6:Canceled');
            $table->string('callback_url');
            $table->string('reference_no');
            $table->json('info')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
            $table->unique(['merchant_id', 'merchant_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_deposits');
    }
}
