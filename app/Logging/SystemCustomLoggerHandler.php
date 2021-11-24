<?php
namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use App\Models\Log;

class SystemCustomLoggerHandler extends AbstractProcessingHandler{

    protected function write(array $record): void 
    {   
        Log::create([
            'message'       => $record['message'],
            'channel'       => $record['channel'],
            'level'         => $record['level'],
            'level_name'    => strtolower($record['level_name']),
            'context'       => json_encode($record['context']),
            'datetime'      => $record['datetime']->format('Y-m-d H:i:s'),
            'extra'         => json_encode($record['extra']),
        ]);
    }
}