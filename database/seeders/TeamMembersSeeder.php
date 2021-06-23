<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class TeamMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('team_members')->insert([
            'member_id'      => 1,
            'team_id'        => 1,
            'is_team_leader' => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ]);

        DB::table('team_members')->insert([
            'member_id'      => 2,
            'team_id'        => 1,
            'is_team_leader' => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ]);

        DB::table('team_members')->insert([
            'member_id'      => 3,
            'team_id'        => 1,
            'is_team_leader' => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ]);
    }
}
