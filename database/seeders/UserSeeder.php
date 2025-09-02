<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Muhammad Sofyan Murtadlo',
            'username' => 'Sopen',
            'email' => 'sofyanmurtadlo6@gmail.com',
            'custom_fee' => 1,
            'fee' => 0,
            'is_active' => 1,
            'is_admin' => 1,
            'password' => Hash::make('$2a$08$56B3S9sFm/UBoqYxz/K8v.IR0bZ9HlFShaYiRWccny3uRX30EG2YG'),
        ]);
        $user0 = User::where('email', 'sofyanmurtadlo6@gmail.com')->first();
        $bank0 = new Bank();
        $bank0->user_id = $user0->id;
        $bank0->save();

        DB::table('users')->insert([
            'name' => 'Thorikul Khuluq',
            'username' => 'Epo',
            'custom_fee' => 1,
            'fee' => 0,
            'is_active' => 1,
            'is_admin' => 1,
            'email' => 'tkhuluq@gmail.com',
            'password' => Hash::make('$2a$08$56B3S9sFm/UBoqYxz/K8v.IR0bZ9HlFShaYiRWccny3uRX30EG2YG'),
        ]);

        $user1 = User::where('email', 'tkhuluq@gmail.com')->first();
        $bank1 = new Bank();
        $bank1->user_id = $user1->id;
        $bank1->save();
    }
}
