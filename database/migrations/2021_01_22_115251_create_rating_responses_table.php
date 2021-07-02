<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_id');
            $table->unsignedBigInteger('sub_session_id');
            $table->unsignedBigInteger('legend_id');
            $table->foreign('rating_id')->references('id')->on('ratings');
            $table->foreign('sub_session_id')->references('id')->on('sub_sessions');
            $table->foreign('legend_id')->references('id')->on('legends');
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
        Schema::dropIfExists('rating_responses');
    }
}
