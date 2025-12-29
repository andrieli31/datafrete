<?php

namespace App\Services;

class Logger
{
    private $logDir;

    public function __construct()
    {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logDir = $logDir;
    }

    public function info($message, $context = [])
    {
        $this->log('INFO', $message, $context);
    }

    public function error($message, $context = [])
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, $context = [])
    {
        $this->log('WARNING', $message, $context);
    }

    private function log($level, $message, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];

        $logFile = $this->logDir . '/app_' . date('Y-m-d') . '.log';
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
}

