<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_id');
            $table->unsignedBigInteger('session_id');
            $table->foreign('rating_id')->references('id')->on('ratings');
            $table->foreign('session_id')->references('id')->on('sessions');
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
        Schema::dropIfExists('rating_sessions');
    }
}
