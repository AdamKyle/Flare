<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameRaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_races', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('str_mod')->nullable()->default(0);
            $table->integer('dur_mod')->nullable()->default(0);
            $table->integer('dex_mod')->nullable()->default(0);
            $table->integer('chr_mod')->nullable()->default(0);
            $table->integer('int_mod')->nullable()->default(0);
            $table->integer('accuracy_mod')->nullable()->default(0);
            $table->integer('dodge_mod')->nullable()->default(0);
            $table->integer('deffense_mod')->nullable()->default(0);
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
        Schema::dropIfExists('game_races');
    }
}
