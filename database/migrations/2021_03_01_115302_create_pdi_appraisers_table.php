<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdiAppraisersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdi_appraisers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appraiser_id');
            $table->unsignedBigInteger('rating_id');
            $table->foreign('appraiser_id')->references('id')->on('users');
            $table->foreign('rating_id')->references('id')->on('ratings');
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
        Schema::dropIfExists('pdi_appraisers');
    }
}
