<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdminWhiteLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_white_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->ipAddress('ip');
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
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
        Schema::dropIfExists('admin_white_lists');
    }
}
