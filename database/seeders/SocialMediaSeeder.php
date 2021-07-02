<?php

namespace Database\Seeders;

use App\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SocialMedia::create([
            'name'  => 'Github',
            'url'   => 'https://github.com/',
        ]);
        SocialMedia::create([
            'name'  => 'Behance',
            'url'   => 'https://www.behance.net/',
        ]);
        SocialMedia::create([
            'name'  => 'Dribbble',
            'url'   => 'https://dribbble.com/',
        ]);
        SocialMedia::create([
            'name'  => 'Facebook',
            'url'   => 'https://www.facebook.com/',
        ]);
        SocialMedia::create([
            'name'  => 'Instagram',
            'url'   => 'https://www.instagram.com/',
        ]);
        SocialMedia::create([
            'name'  => 'LinkedIn',
            'url'   => 'https://www.linkedin.com/',
        ]);
        SocialMedia::create([
            'name'  => 'YouTube',
            'url'   => 'https://www.youtube.com/',
        ]);
    }
}
