<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Session::create([
            "title" => "ORIENTADO A RESULTADOS",
            "order" => 100,
        ]);
        Session::create([
            "title" => "VISÃO DE NEGÓCIO E INOVAÇÃO",
            "order" => 200,
        ]);
        Session::create([
            "title" => "CONSTRUÇÃO DE RELACIONAMENTO E PARCERIA /AGENTE DE MUDANÇAS",
            "order" => 300,
        ]);
    }
}
