<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ParentSessions extends Eloquent  {
    use HasUuids;

    protected $table = 'wainwright_parent_sessions';
    protected $timestamp = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'player_id',
        'ws_id',
        'player_operator_id',
        'game_id',
        'game_provider',
        'currency',
        'state',
        'session_url',
        'operator_id',
        'request_ip',
        'token_original',
        'token_original_bridge',
        'active',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'extra_meta' => 'json',
        'user_agent' => 'json'
    ];

    public static function list()
    {
        $cache_length = 60;
        $limit = 25;
        if($request->limit) {
            if(is_numeric($request->limit)) {
                if($request->limit > 0) {
                    if($request->limit > 100) {
                        $limit = (int) 100;
                    } else {
                        $limit = (int) $request->limit;
                    }
                }
            }
        }

        return self::ParentSessions();

        $providers = collect(Gameslist::providers());
        $result = collect($this->providerslist_wainwright($providers, $limit))->paginate($limit);
        $response = [
                "code" => 200,
                "status" => "success",
                $result,
        ];
        return response()->json($response, 200);
    }



}