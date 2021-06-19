<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMerchantFundRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_fund_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_deposit_id')
                  ->constrained()
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->unsignedTinyInteger('type')
                ->comment('0:Top up Credit,1:Transaction Fee');
            $table->decimal('amount', 14, 4);
            $table->json('info');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_fund_records');
    }
}
