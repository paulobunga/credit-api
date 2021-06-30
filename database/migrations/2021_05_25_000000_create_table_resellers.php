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
            $table->string('username')->unique();
            $table->string('phone', 20);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('coin', 14, 4)->default(0);
            $table->decimal('transaction_fee', 5, 4)->default(0);
            $table->integer('pending_limit')->default(5);
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->string('password', 60);
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
