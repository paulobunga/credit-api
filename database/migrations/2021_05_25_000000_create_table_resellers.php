<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableResellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->unique();
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('coin', 14, 4)->default(0);
            $table->decimal('transaction_fee');
            $table->integer('pending_limit')->default(5);
            $table->unsignedTinyInteger('status')->default(0)->comment('0:Disabled,1:Enabled');
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
        Schema::dropIfExists('resellers');
    }
}
