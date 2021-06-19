<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMerchantWithdrawalForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_withdrawal_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_withdrawal_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->decimal('amount', 14, 4);
            $table->json('info');
            $table->unsignedTinyInteger('status')
                  ->comment('1:Created,2:Waiting to Approve,3:Approved,4:Rejected,5:Enforced,6:Canceled');
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
        Schema::dropIfExists('merchant_withdrawal_forms');
    }
}
