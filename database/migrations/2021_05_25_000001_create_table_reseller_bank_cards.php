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
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('bank_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('account_name', 64);
            $table->string('account_no', 64);
            $table->json('info')->default(new Expression('(JSON_ARRAY())'));
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
