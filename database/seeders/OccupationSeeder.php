<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class OccupationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('occupations')->insert([
            'name'             => 'Programador',
            'skills'           => 'Formado ou cursando ciência da computação',
            'responsabilities' => 'Analisar e desenvolver sistemas.',
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);
    }
}
