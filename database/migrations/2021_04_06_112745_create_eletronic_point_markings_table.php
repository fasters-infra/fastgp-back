<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEletronicPointMarkingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eletronic_point_markings', function (Blueprint $table) {
            $table->id();
            $table->text('justification')->nullable();
            $table->boolean('need_justification')->default(false);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('justified_by')->nullable(true);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('justified_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eletronic_point_markings');
    }
}
