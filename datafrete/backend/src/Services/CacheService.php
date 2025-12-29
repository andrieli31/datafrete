<?php

namespace App\Services;

class CacheService
{
    private $cacheDir;

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key)
    {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        
        if ($data === null || $data['expires'] < time()) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = 3600)
    {
        $file = $this->getCacheFile($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        file_put_contents($file, json_encode($data));
    }

    private function getCacheFile($key)
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
        return $this->cacheDir . '/' . md5($safeKey) . '.json';
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}

