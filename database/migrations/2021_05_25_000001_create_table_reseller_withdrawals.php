<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateTableResellerWithdrawals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reseller_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('order_id')->unique();
            $table->decimal('amount', 14, 4);
            $table->unsignedTinyInteger('status')
                ->comment('0:Created,1::Approved,2:Rejected');
            $table->json('info')->default(new Expression('(JSON_OBJECT())'));
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
        Schema::dropIfExists('reseller_withdrawals');
    }
}
