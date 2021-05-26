<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReportMerchantFunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_merchant_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                  ->constrained()
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->decimal('turnover', 14, 4);
            $table->decimal('transaction_fee', 14, 4);
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
        Schema::dropIfExists('report_merchant_funds');
    }
}
