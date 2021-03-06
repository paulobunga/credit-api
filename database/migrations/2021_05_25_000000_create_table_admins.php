<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Admin;

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
            $table->string('name')->unique()->index();
            $table->string('username')->unique()->index();
            $table->string('password', 60);
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->string('timezone', 60)->default(env('APP_TIMEZONE'));
            $table->timestamps();
        });

        // insert default administrator
        $admin = Admin::create([
            'name' => 'administrator',
            'username' => 'admin@gmail.com',
            'password' => 'P@ssw0rd',
            'status' => true,
        ]);
        $admin->assignRole('Super Admin');
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
