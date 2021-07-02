<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdjustUserFieldNullableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->string('password', 255)->nullable()->change();
            $table->string('cellphone', 255)->nullable()->change();

            DB::statement("ALTER TABLE users MODIFY approver_for_all_teams TINYINT(1)");
            DB::statement("ALTER TABLE users MODIFY role_id BIGINT(20) UNSIGNED");
            DB::statement("ALTER TABLE users MODIFY `status` ENUM('active', 'inactive')");

            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_field_nullable');
    }
}
