<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccupationLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occupation_levels', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('occupation_id')->references('id')->on('occupations');
            $table->foreignId('level_id')->references('id')->on('levels');
            $table->string('responsabilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('occupation_levels');
    }
}
