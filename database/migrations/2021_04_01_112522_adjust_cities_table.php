<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('uf');

            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');

            $table->unsignedBigInteger('state_id')->nullable()->after('name');
            $table->foreign('state_id')->references('id')->on('states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('uf', 2)->after('name');


            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
        });
    }
}
