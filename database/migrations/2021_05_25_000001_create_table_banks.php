<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('currency', 3);
            $table->unsignedTinyInteger('status');
            $table->timestamps();
        });
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('currency', 4);
            $table->text('payment_methods');
            $table->text('banks');
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['name', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_channels');
        Schema::dropIfExists('banks');
    }
}
