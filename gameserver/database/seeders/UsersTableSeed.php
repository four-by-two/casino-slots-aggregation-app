<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
        $user = User::create([
            'name' => 'casinoman',
            'email' => 'default@casinoman.app',
            'password' => Hash::make('casinomanPassword'),
        ]);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }
}
