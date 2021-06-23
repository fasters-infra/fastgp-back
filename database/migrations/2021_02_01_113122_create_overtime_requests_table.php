<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reason', 255);
            $table->enum('status', ['pending', 'denied', 'approved']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('changed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('overtime_requests');
    }
}
