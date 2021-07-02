<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            "name"         => "Fasters",
            "fantasy_name" => "Fasters",
            "foundation"   => null,
            "website"      => "https://www.fasters.com.br/",
            "phone"        => "(11) 2450-7851",
            "description"  => "Empresa padrão criada pela seed :)",
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ]);
    }
}
