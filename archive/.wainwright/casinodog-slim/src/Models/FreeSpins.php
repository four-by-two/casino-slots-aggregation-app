<?php

namespace Wainwright\CasinoDog\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Wainwright\CasinoDog\CasinoDog;
use Carbon\Carbon;
class FreeSpins extends Eloquent  {
    protected $table = 'wainwright_freespins';
    protected $timestamp = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'player_id',
        'player_operator_id',
        'game_id',
        'total_spins',
        'spins_left',
        'total_win',
        'bet_amount',
        'operator_id',
        'expiration_stamp',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function add_freespins(Request $request)
    {
        $player_id = hash_hmac('md5', $request->currency.'*'.$request->player, $request->operator_key);
        $data = $request->toArray();
        $data_morp = new CasinoDog();
        $data ??= [];
        $data = $data_morp->morph_array($data);
        $frb = new FreeSpins();
        $frb->player_id = $player_id;
        $frb->player_operator_id = $request->player;
        $frb->currency = $request->currency;
        $frb->operator_key = $request->operator_key;
        $frb->total_spins = $request->freespins_count;
        $frb->bet_amount = $request->freespins_betamount;
        $frb->spins_left = $request->freespins_count;
        $frb->expiration_stamp = Carbon::now()->addHours(48)->timestamp;
        $frb->game_id = $request->game;
		$frb->timestamps = true;
        $frb->active = true;
		$frb->save();
        return $frb;
    }

    public function start_job($id)
    {
        GameslistImporterBatch::dispatch($id);
    }
}