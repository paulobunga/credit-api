<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTableTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)
                ->comment('0:Top up Credit,1: Deduct Credit, 2: Top up Coin, 3:Deduct Coin');
            $table->timestamp('created_at')->useCurrent();;
            $table->index('name');
        });
        DB::table('transaction_methods')->insert(['name' => 'TOPUP_CREDIT']);
        DB::table('transaction_methods')->insert(['name' => 'DEDUCT_CREDIT']);
        DB::table('transaction_methods')->insert(['name' => 'TOPUP_COIN']);
        DB::table('transaction_methods')->insert(['name' => 'DEDUCT_COIN']);
        DB::table('transaction_methods')->insert(['name' => 'TRANSACTION_FEE']);
        
        Schema::create('model_has_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type', 50);
            $table->index(['model_id', 'model_type'], 'model_id_model_type_index');
            $table->primary(
                ['transaction_id', 'model_id', 'model_type'],
                'model_has_transactions_model_id_model_type_primary'
            );
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_method_id')->constrained()->onDelete('cascade');;
            $table->decimal('amount', 14, 4);
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
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('model_has_transactions');
        Schema::dropIfExists('transaction_methods');
    }
}
