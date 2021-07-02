<?php

namespace Database\Seeders;

use App\Models\Legend;
use Illuminate\Database\Seeder;

class LegendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Legend::create([
            "value" => 1,
            "label" => "Não atende",
        ]);
        Legend::create([
            "value" => 2,
            "label" => "Atende parcialmente",
        ]);
        Legend::create([
            "value" => 3,
            "label" => "Atende",
        ]);
        Legend::create([
            "value" => 4,
            "label" => "Supera",
        ]);
    }
}
