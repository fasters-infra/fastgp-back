<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEletronicPointProfileToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('eletronic_point_profile_id')->nullable()->after('ie');
            $table->foreign('eletronic_point_profile_id')->references('id')->on('eletronic_point_profiles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['eletronic_point_profile_id']);
            $table->dropColumn('eletronic_point_profile_id');
        });
    }
}
