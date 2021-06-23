<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('zipcode', 10)->nullable();

            $table->string('cpf', 14)->nullable();
            $table->string('rg', 32)->nullable();
            $table->date('birthday')->nullable();
            $table->string('photo')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('cellphone', 20);
            $table->string('natural')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('marital_status', ['single', 'married', 'engaged', 'divorced', 'widower', 'union'])->nullable();
            $table->enum('scholarity',[
                'elementary_compl',
                'elementary_incompl',
                'high_compl',
                'high_incompl',
                'college_compl',
                'college_incompl',
                'postgraduate_compl',
                'postgraduate_incompl'
            ])->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
