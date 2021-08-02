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
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 20);
            $table->unsignedTinyInteger('type');
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
    }
}
