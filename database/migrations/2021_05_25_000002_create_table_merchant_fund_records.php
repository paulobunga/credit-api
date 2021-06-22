<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

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
            $table->morphs('fundable');
            $table->unsignedTinyInteger('type')
                ->comment('0:Top up Credit,2:Withdraw Credit');
            $table->decimal('amount', 14, 4);
            $table->json('info')->default(new Expression('(JSON_ARRAY())'));
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
