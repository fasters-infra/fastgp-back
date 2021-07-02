<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->enum('type', ['clients', 'groups', 'broadcast'])->nullable();
            $table->string('message');
            $table->string('payload');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->dateTime('send_at');
            $table->enum('status', ['success', 'error', 'scheduled'])->nullable();
            $table->string('log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
