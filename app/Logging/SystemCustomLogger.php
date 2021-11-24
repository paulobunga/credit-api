<?php

namespace App\Logging;

use Monolog\Logger;

class SystemCustomLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger("SystemCustomLogger");
        $handler = new SystemCustomLoggerHandler();
        $logger->pushHandler($handler);
        return $logger;
    }
}