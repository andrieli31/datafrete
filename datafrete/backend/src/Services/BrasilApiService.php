<?php

namespace App\Services;

use App\Services\CacheService;

class BrasilApiService
{
    private $cache;
    private $baseUrl = 'https://brasilapi.com.br/api/cep/v1/';

    public function __construct()
    {
        $this->cache = new CacheService();
    }

    public function getCepInfo($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) !== 8) {
            throw new \Exception("CEP inválido. Deve conter 8 dígitos.");
        }

        $cacheKey = "cep_{$cep}";
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $url = $this->baseUrl . $cep;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("CEP não encontrado na API Brasil API");
        }

        $data = json_decode($response, true);
        
        if (!$data || !isset($data['cep'])) {
            throw new \Exception("CEP não encontrado na API Brasil API");
        }

        if (!isset($data['location']['coordinates'])) {
            $coordinates = $this->geocodeAddress($data);
            if ($coordinates) {
                $data['location'] = [
                    'coordinates' => $coordinates
                ];
            } else {
                throw new \Exception("Não foi possível obter coordenadas para o CEP");
            }
        }

        $this->cache->set($cacheKey, $data, 86400);

        return $data;
    }

    public function getCoordinates($cep)
    {
        $info = $this->getCepInfo($cep);
        
        if (!isset($info['location']['coordinates'])) {
            throw new \Exception("Coordenadas não encontradas para o CEP");
        }

        $coords = $info['location']['coordinates'];
        return [
            'latitude' => $coords['latitude'],
            'longitude' => $coords['longitude']
        ];
    }

    private function geocodeAddress($addressData)
    {
        $address = '';
        if (isset($addressData['street'])) {
            $address .= $addressData['street'] . ', ';
        }
        if (isset($addressData['neighborhood'])) {
            $address .= $addressData['neighborhood'] . ', ';
        }
        if (isset($addressData['city'])) {
            $address .= $addressData['city'] . ', ';
        }
        if (isset($addressData['state'])) {
            $address .= $addressData['state'] . ', ';
        }
        $address .= 'Brasil';
        
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($address) . '&format=json&limit=1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DataFrete/1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Language: pt-BR,pt;q=0.9']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return null;
        }
        
        $results = json_decode($response, true);
        
        if (empty($results) || !isset($results[0]['lat']) || !isset($results[0]['lon'])) {
            return null;
        }
        
        return [
            'latitude' => (float)$results[0]['lat'],
            'longitude' => (float)$results[0]['lon']
        ];
    }
}

