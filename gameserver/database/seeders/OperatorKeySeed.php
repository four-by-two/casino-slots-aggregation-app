<?php

namespace Database\Seeders;

use App\Models\OperatorAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class OperatorKeySeed extends Seeder
{
    /**
     * Used for authentication
     *
     * @return void
     */
    public function run()
    {
        $user = OperatorAccess::create([
            'operator_key' => config('casinodog.debug_callback.api_key'),
            'operator_secret' => config('casinodog.debug_callback.api_secret'),
            'operator_access' => 'internal',
            'callback_url' => config('casinodog.debug_callback.callback_endpoint'),
            'ownedBy' => User::first()->id,
            'active' => 1,
        ]);
    }
}