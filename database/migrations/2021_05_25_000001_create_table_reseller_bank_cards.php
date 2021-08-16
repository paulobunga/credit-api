<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateTableResellerBankCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reseller_bank_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                ->constrained();
            $table->foreignId('bank_id')
                ->constrained();
            $table->foreignId('payment_channel_id')
                ->constrained();
            $table->string('account_name', 64)->default('');
            $table->string('account_no', 64);
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->unsignedTinyInteger('status')->default(1)->comment('0:Disabled,1:Enabled');
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
        Schema::dropIfExists('reseller_bank_cards');
    }
}
