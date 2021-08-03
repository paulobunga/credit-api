<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableResellerActivateCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reseller_activate_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedBigInteger('active_reseller_id')->default(0);
            $table->string('code', 40);
            $table->unsignedTinyInteger('status')->default(0)->comment('0:Pending,1:Activated,2:Expired');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->unique(['reseller_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reseller_activate_codes');
    }
}
