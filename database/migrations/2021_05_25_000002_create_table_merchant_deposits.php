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
                ->constrained();
            $table->foreignId('reseller_bank_card_id')
                ->constrained();
            $table->string('method', 10);
            $table->string('order_id', 60)->unique();
            $table->string('merchant_order_id', 60);
            $table->decimal('amount', 14, 4);
            $table->string('currency', 6);
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('0:Created,1:Pending,2:Approved,3:Rejected,4:Enforced,5:Canceled');
            $table->unsignedTinyInteger('callback_status')
                ->default(0)
                ->comment('0:Created,1:Pending,2:Finish,3:Retry');
            $table->unsignedTinyInteger('attempts')
                ->default(0);
            $table->string('callback_url');
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
            $table->timestamp('notified_at')->nullable();
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
