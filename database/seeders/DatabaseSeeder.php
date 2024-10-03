<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

//use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'test@prompthub.us',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name'     => 'Dalton Pierce',
            'email'    => 'dalton@prompthub.us',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name'     => 'Dan Cleary',
            'email'    => 'dan@prompthub.us',
            'password' => Hash::make('password'),
        ]);

        App::make(BaseAiPromptsConfigurationSeeder::class)->run();
    }
}
