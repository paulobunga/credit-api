<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_daily_merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained();
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->unsignedInteger('turnover');
            $table->decimal('credit', 14, 4);
            $table->decimal('transaction_fee', 14, 4);
            $table->string('currency', 6);
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('report_daily_resellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                ->constrained();
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->unsignedInteger('turnover');
            $table->decimal('credit', 14, 4);
            $table->decimal('coin', 14, 4);
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_daily_merchants');
        Schema::dropIfExists('report_daily_resellers');
    }
}
