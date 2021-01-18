<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description');
            $table->integer('attack');
            $table->integer('defence');
            $table->boolean('can_heal')->nullable()->default(false);
            $table->boolean('heal_amount')->nullable()->default(null);
            $table->boolean('siege_weapon')->nullable()->default(false);
            $table->boolean('attacks_walls')->nullable()->default(false);
            $table->boolean('attacks_buildings')->nullable()->default(false);
            $table->boolean('defender')->nullable()->default(false);
            $table->boolean('attacker')->nullable()->default(false);
            $table->bigInteger('weak_against_unit_id')->unsigned()->nullable();
            $table->string('primary_target')->nullable();
            $table->string('fall_back')->nullable();
            $table->integer('travel_time');
            $table->integer('wood_cost');
            $table->integer('clay_cost');
            $table->integer('stone_cost');
            $table->integer('iron_cost');
            $table->integer('required_population');
            $table->integer('time_to_recruit');
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
        Schema::dropIfExists('game_units');
    }
}