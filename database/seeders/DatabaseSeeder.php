<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Panggil seeder UserSeeder
        $this->call(UserSeeder::class);

        // Panggil seeder SettingSeeder
        $this->call(SettingSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->command->info('Seeded the countries!');
    }
}
