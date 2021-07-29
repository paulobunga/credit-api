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
            $table->unsignedBigInteger('upline_id')->default(0);
            $table->tinyInteger('level')->comment('0:referrer,1:master agent,2:agent,3:reseller');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('phone', 20);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('coin', 14, 4)->default(0);
            $table->decimal('commission_percentage', 5, 4)->default(0);
            $table->unsignedInteger('pending_limit')->default(0);
            $table->unsignedInteger('downline_slot')->default(0);
            $table->tinyInteger('status')->default(0)->comment('0:inactive,1:active,2:disabled');
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
