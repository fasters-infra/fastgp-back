<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEletronicPointProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eletronic_point_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->time('entry_time');
            $table->time('break_time');
            $table->time('interval_return_time');
            $table->time('departure_time');
            $table->bigInteger('tolerance')->nullable();
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
        Schema::dropIfExists('eletronic_point_profiles');
    }
}
