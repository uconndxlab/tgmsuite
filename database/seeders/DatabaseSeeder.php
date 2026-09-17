<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::updateOrCreate(
            ['email' => 'admin@tgmsuite.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'joel@uconn.edu'],
            [
                'name' => 'i3 Testing and Temporary',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        $field = \App\Models\Field::firstOrCreate(
            ['name' => 'Memorial Stadium Turf'],
            [
                'address' => '1 Stadium Way',
                'city' => 'Storrs',
                'state' => 'CT',
                'zip' => '06269',
                'sports_played' => 'Soccer, Football',
                'turfgrass_species_present' => 'Kentucky Bluegrass, Perennial Ryegrass',
                'mowing_height' => '2.5 in',
                'description' => 'Main competition athletic field with natural turfgrass.',
            ]
        );

        $user->fields()->syncWithoutDetaching([$field->id => ['permission_level' => 'admin']]);
    }
}
