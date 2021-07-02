<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkloadToUserOccupations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_occupations', function (Blueprint $table) {
            $table->integer('workload');
            $table->enum('workload_period', ['weekly', 'monthly', 'full']);
            $table->float('hour_value', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_occupations', function (Blueprint $table) {
            $table->dropColumn('workload');
            $table->dropColumn('workload_period', ['weekly', 'monthly', 'full']);
            $table->dropColumn('hour_value');
        });
    }
}
