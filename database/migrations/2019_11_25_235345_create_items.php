<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('item_suffix_id')->unsigned()->nullable();
            $table->bigInteger('item_prefix_id')->unsigned()->nullable();
            $table->boolean('market_sellable')->nullable()->default(false);
            $table->string('name');
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('default_position')->nullable();
            $table->integer('base_damage')->nullable();
            $table->integer('base_healing')->nullable();
            $table->integer('base_ac')->nullable();
            $table->integer('cost')->nullable();
            $table->decimal('base_damage_mod', 5, 4)->nullable();
            $table->decimal('base_healing_mod', 5, 4)->nullable();
            $table->decimal('base_ac_mod', 5, 4)->nullable();
            $table->decimal('str_mod', 5, 4)->nullable();
            $table->decimal('dur_mod', 5, 4)->nullable();
            $table->decimal('dex_mod', 5, 4)->nullable();
            $table->decimal('chr_mod', 5, 4)->nullable();
            $table->decimal('int_mod', 5, 4)->nullable();
            $table->string('effect')->nullable();
            $table->boolean('can_craft')->nullable()->default(false);
            $table->integer('skill_level_required')->nullable();
            $table->integer('skill_level_trivial')->nullable();
            $table->string('crafting_type')->nullable();
            $table->string('skill_name')->nullable();
            $table->decimal('skill_bonus', 5, 4)->nullable();
            $table->decimal('skill_training_bonus', 5, 4)->nullable();
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
        Schema::dropIfExists('items');
    }
}
