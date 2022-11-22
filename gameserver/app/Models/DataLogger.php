<?php
namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DataLogger extends Eloquent  {
    protected $table = 'wainwright_datalogger';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'uuid',
    ];
    protected $casts = [
        'data' => 'json',
        'extra_data' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function log_count() {
        $value = Cache::remember('datalogger:log_count', 150, function () {
            return Datalogger::count();
        });
        return $value;
    }
    public static function auto_clean()
    {
        Datalogger::truncate();
        Cache::pull('datalogger:log_count');
    }

    public static function save_log($type, $data, $extra_data = NULL)
    {
        if(self::log_count() > 5000) {
            self::auto_clean();
            save_log('Datalogger', 'Truncated datalogger collection automatic because surpassed 5000 entries.');
        }
        $data ??= [];
        $data = morph_array($data);
        $extra_data ??= [];
        $extra_data = morph_array($extra_data);
        $logger = new DataLogger();
        $logger->type = $type;
		$logger->uuid = Str::orderedUuid();
		$logger->data = $data;
        $logger->extra_data = $extra_data;
		$logger->timestamps = true;
		$logger->save();
        Log::debug($type.' - Datalogger: '.json_encode($data, JSON_PRETTY_PRINT));
    }

}