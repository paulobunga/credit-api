<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReportResellerFunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_reseller_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                  ->constrained()
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->unsignedTinyInteger('type')
                ->comment('0:Top up Credit,1: Deduct Credit, 2: Top up Coin, 3:Deduct Coin');
            $table->decimal('amount', 14, 4);
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
        Schema::dropIfExists('report_reseller_funds');
    }
}
