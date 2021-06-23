<?php

namespace Database\Seeders;

use App\Models\SubSession;
use Illuminate\Database\Seeder;

class SubSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubSession::create([
            "session_id" => 1,
            "title" => " Foco e Cumpre prazos",
            "order" => 100,
        ]);
        SubSession::create([
            "session_id" => 1,
            "title" => "Apresenta entrega final com qualidade",
            "order" => 200,
        ]);
        SubSession::create([
            "session_id" => 1,
            "title" => "Contribui de forma ativa com o atingimento das metas do time",
            "order" => 300,
        ]);
        SubSession::create([
            "session_id" => 2,
            "title" => "Ritmo de Evolução Pessoal e Profissional",
            "order" => 100,
        ]);
        SubSession::create([
            "session_id" => 2,
            "title" => "Conhecimento Técnico e visão sistêmica  entre os Projetos",
            "order" => 200,
        ]);
        SubSession::create([
            "session_id" => 3,
            "title" => "Engajamento dentro do time",
            "order" => 100,
        ]);
        SubSession::create([
            "session_id" => 3,
            "title" => "Pró atividade e inciativa",
            "order" => 200,
        ]);
        SubSession::create([
            "session_id" => 3,
            "title" => "Flexibilidade e Adaptabilidade",
            "order" => 300,
        ]);
    }
}
