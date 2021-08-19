<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateTableBanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('ident', 30)->unique()->index();
            $table->string('name', 100);
            $table->string('currency', 6);
            $table->unsignedTinyInteger('status');
            $table->timestamps();
        });
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('currency', 6);
            $table->text('payment_methods');
            $table->text('banks');
            $table->json('attributes')->default(new Expression('(JSON_ARRAY())'));
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['name', 'currency']);
        });
        Schema::create('reseller_bank_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                ->constrained();
            $table->foreignId('payment_channel_id')
                ->constrained();
            $table->json('attributes')->default(new Expression('(JSON_OBJECT())'));
            $table->unsignedTinyInteger('status')->default(1)->comment('0:Inactive,1:Active,2:Disabled');
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
        Schema::dropIfExists('payment_channels');
        Schema::dropIfExists('banks');
    }
}
