<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            "name"              => "Bruno",
            "email"             => "bruno@fasters.com.br",
            "password"          => bcrypt("123456"),
            "cpf"               => null,
            "rg"                => null,
            "birthday"          => null,
            "photo"             => null,
            "phone"             => null,
            "cellphone"         => "(11) 97361-9228",
            "natural"           => null,
            "nationality"       => null,
            "marital_status"    => null,
            "status"            => "active",
            "remember_token"    => null,
            'scholarity'        => 'postgraduate_incompl',
            "role_id"           => 1,
            'gender'            => 'male',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
            's3_bucket'         => '174536076601170ef3cfeb6.81218763'
        ]);

        DB::table('users')->insert([
            "name"              => "Katia",
            "email"             => "katia@fasters.com.br",
            "password"          => bcrypt("123456"),
            "cpf"               => null,
            "rg"                => null,
            "birthday"          => null,
            "photo"             => null,
            "phone"             => null,
            "cellphone"         => "(11) 97361-9228",
            "natural"           => null,
            "nationality"       => null,
            "marital_status"    => null,
            "status"            => "active",
            "remember_token"    => null,
            'scholarity'        => 'college_compl',
            "role_id"           => 1,
            'gender'            => 'female',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
            's3_bucket'         => '174536076601160ef3cfeb6.81218763'
        ]);
    }
}
