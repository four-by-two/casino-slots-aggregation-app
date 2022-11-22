<?php

namespace App\Http\Controllers\Casinodog\API\Gameslist;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\Gameslist;

class ScaffoldGameslist extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('disableOnProduction');
    }

    /*
    *   Scaffold the default gameslist from internal storage
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
                'provider' => 'required|string|min:1|max:555',
        ]);

        $scaffold = NULL;
        if($request->provider === "all") {
            $scaffold = $this->scaffold_all_providers();
        } else {
            $select_provider = config("casinodog.games.{$request->provider}");
            if(!$select_provider) {
                abort(400, "Provider not found in casinodog config. Retry with valid name or use \'all\' as provider to scaffold all games from providers in config file.");
            }
            if(config("casinodog.games.{$request->provider}.active") === 0) {
                abort(400, "This provider is set inactive in config file. Set to active to scaffold.");
            }
            $scaffold = $this->scaffold_provider($request->provider);
        }

        $response_data = [
                'code' => 200,
                'status' => 'success',
                'data' => $scaffold,
        ];
        return response()->json($response_data, 200);

    }

    public function scaffold_provider($provider_id)
    {
        try {
            Artisan::call('casinodog:restore-default-gameslist '.$provider_id.' upsert');
            Cache::pull('providerlist:shortlist');
            Cache::pull('gameslist:shortlist');
            $provider_info = collect(Gameslist::provider_list())->where('pid', $provider_id)->first();
            $result_array[] = array($provider_id => array("status" => "success", "provider_info" => $provider_info));
        } catch(\Exception $e) {
            save_log('Error trying to scaffold gameslist', $e->getMessage());
            Cache::pull('providerlist:shortlist');
            Cache::pull('gameslist:shortlist');
            $provider_info = collect(Gameslist::provider_list())->where('pid', $provider_id)->first();
            $result_array[] = array($provider_id => array("status" => "error", "message" => $e->getMessage(), "provider_info" => $provider_info));
            abort(400, 'Error trying to scaffold gameslist: '. $e->getMessage());
        }
        return $result_array;
    }

    public function scaffold_all_providers()
    {
        foreach(config('casinodog.games') as $provider_id => $provider_table) {
            try {
                Artisan::call('casinodog:restore-default-gameslist '.$provider_id.' upsert');
                Cache::pull('providerlist:shortlist');
                Cache::pull('gameslist:shortlist');
                $provider_info = collect(Gameslist::provider_list())->where('pid', $provider_id)->first();
                $result_array[] = array($provider_id => array("status" => "success", "provider_info" => $provider_info));
            } catch(\Exception $e) {
                save_log('Error trying to scaffold gameslist', $e->getMessage());
                Cache::pull('providerlist:shortlist');
                Cache::pull('gameslist:shortlist');
                $provider_info = collect(Gameslist::provider_list())->where('pid', $provider_id)->first();
                $result_array[] = array($provider_id => array("status" => "success", "provider_info" => $provider_info));
            }
        }
        return $result_array;
    }
}
