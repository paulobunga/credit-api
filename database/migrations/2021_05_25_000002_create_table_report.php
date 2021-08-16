<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('currency', 3);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('report_monthly_merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                  ->constrained();
            $table->date('date');
            $table->unsignedInteger('turnover');
            $table->decimal('payin', 14, 4);
            $table->decimal('payout', 14, 4);
            $table->string('currency', 3);
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
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('report_monthly_resellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                  ->constrained();
            $table->date('date');
            $table->unsignedInteger('turnover');
            $table->decimal('payin', 14, 4);
            $table->decimal('payout', 14, 4);
            $table->decimal('coin', 14, 4);
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
        Schema::dropIfExists('report_monthly_merchants');
        Schema::dropIfExists('report_monthly_resellers');
    }
}
