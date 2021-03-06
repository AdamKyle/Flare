<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdventureMonster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adventure_monster', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('adventure_id')->unsigned();
            $table->bigInteger('monster_id')->unsigned();
            $table->foreign('adventure_id')
                  ->references('id')->on('adventures');
            $table->foreign('monster_id')
                  ->references('id')->on('monsters');
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
        Schema::dropIfExists('adventure_monster');
    }
}
