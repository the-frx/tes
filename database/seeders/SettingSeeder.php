<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            'key' => 'Site_Name',
            'value' => 'Goldu',
        ]);
        DB::table('settings')->insert([
            'key' => 'Site_Description',
            'value' => 'Increase affiliate marketing success with our private affiliate network. Create smart links, access comprehensive reports, and maximize earnings.',
        ]);
        DB::table('settings')->insert([
            'key' => 'Default_Fee',
            'value' => '20',
        ]);
        DB::table('settings')->insert([
            'key' => 'Activation_Link',
            'value' => 'https://t.me/id241097',
        ]);
        DB::table('settings')->insert([
            'key' => 'IP_Dns',
            'value' => '127.0.0.1',
        ]);
        DB::table('settings')->insert([
            'key' => 'Default_Promotion',
            'value' => 'https://www.youtube.com/@gulalidesa',
        ]);

        DB::table('settings')->insert([
            'key' => 'Postback_Key',
            'value' => '$2a$12$3FMmdurPIjC7WcDEZwcuIu.UnaKzu2V7ex.o/VjDkrx6bpAtEHy.i',
        ]);
        DB::table('settings')->insert([
            'key' => 'BCDN_Key',
            'value' => '64a57e0c-b6ee-4f28-9a1051aeb45b-4c38-4e46',
        ]);
    }
}
