<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('type', 10);
            $table->string('currency', 6);
            $table->string('description');
            $table->timestamps();

            $table->unique(['name', 'type', 'currency']);
        });

        Schema::create('model_has_teams', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id');

            $table->morphs('model');
            $table->index(['model_id', 'model_type'], 'model_has_teams_model_id_model_type_index');

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');

            $table->primary(
                ['team_id', 'model_id', 'model_type'],
                'model_has_teams_model_id_model_type_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_has_teams');
        Schema::dropIfExists('teams');
    }
}
