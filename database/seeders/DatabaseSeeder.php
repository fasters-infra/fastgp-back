<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            //CountrySeeder::class,
            //CitySeeder::class,
            RolesSeeder::class,
            CompanySeeder::class,
            //OccupationSeeder::class,
            //LevelSeeder::class,
            UserSeeder::class,
            //LegendSeeder::class,
            //SessionSeeder::class,
            //SubSessionSeeder::class,
            //TeamSeeder::class,
            //TeamMembersSeeder::class,
            PermissionsSeeder::class,
            ModelHasSeeder::class,
            RoleHasPermissionsSeeder::class,
            ModelHasPermissionsSeeder::class,
            SocialMediaSeeder::class,
            RequiredFieldsSeeder::class
        ]);
    }
}
