<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn(['city_id', 'street', 'number', 'complement', 'neighborhood', 'zipcode']);

            $table->unsignedBigInteger('address_id')->nullable()->after('description');
            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('zipcode', 10)->nullable();

            $table->unsignedBigInteger('city_id')->nullable()->after('zipcode');
            $table->foreign('city_id')->references('id')->on('cities');

            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });
    }
}
