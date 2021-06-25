<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdmins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password', 60);
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->timestamps();
        });

        // insert default administrator
        DB::table('admins')->insert([
            'name' => 'administrator',
            'username' => 'admin@gmail.com',
            'password' => app('hash')->make('P@ssw0rd'),
            'status' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
