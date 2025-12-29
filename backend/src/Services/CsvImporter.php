<?php

namespace App\Services;

use App\Models\Distance;
use App\Services\BrasilApiService;
use App\Services\DistanceCalculator;
use App\Services\Logger;

class CsvImporter
{
    private $brasilApi;
    private $calculator;
    private $distanceModel;
    private $logger;
    private $maxCalculations = 100;

    public function __construct()
    {
        $this->brasilApi = new BrasilApiService();
        $this->calculator = new DistanceCalculator();
        $this->distanceModel = new Distance();
        $this->logger = new Logger();
    }

    public function import($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Arquivo não encontrado");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception("Erro ao abrir o arquivo");
        }

        $header = fgetcsv($handle);
        if (!$header || count($header) < 2) {
            fclose($handle);
            throw new \Exception("Arquivo CSV inválido. Deve conter colunas: CEP origem, CEP fim");
        }

        $results = [
            'success' => [],
            'errors' => [],
            'total' => 0
        ];

        $calculationCount = 0;

        while (($row = fgetcsv($handle)) !== false && $calculationCount < $this->maxCalculations) {
            if (count($row) < 2) {
                continue;
            }

            $cepOrigem = trim($row[0]);
            $cepDestino = trim($row[1]);

            if (empty($cepOrigem) || empty($cepDestino)) {
                $results['errors'][] = [
                    'cep_origem' => $cepOrigem,
                    'cep_destino' => $cepDestino,
                    'error' => 'CEPs vazios'
                ];
                continue;
            }

            try {
                $existing = $this->distanceModel->findByCeps($cepOrigem, $cepDestino);
                if ($existing) {
                    $results['success'][] = [
                        'cep_origem' => $cepOrigem,
                        'cep_destino' => $cepDestino,
                        'distancia' => $existing['distancia'],
                        'id' => $existing['id'],
                        'status' => 'já existente'
                    ];
                    continue;
                }

                $coordsOrigem = $this->brasilApi->getCoordinates($cepOrigem);
                $coordsDestino = $this->brasilApi->getCoordinates($cepDestino);

                $distancia = $this->calculator->calculate(
                    $coordsOrigem['latitude'],
                    $coordsOrigem['longitude'],
                    $coordsDestino['latitude'],
                    $coordsDestino['longitude']
                );

                $id = $this->distanceModel->create($cepOrigem, $cepDestino, $distancia);

                $results['success'][] = [
                    'cep_origem' => $cepOrigem,
                    'cep_destino' => $cepDestino,
                    'distancia' => $distancia,
                    'id' => $id,
                    'status' => 'criado'
                ];

                $calculationCount++;
                $results['total']++;

                usleep(200000);

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'cep_origem' => $cepOrigem,
                    'cep_destino' => $cepDestino,
                    'error' => $e->getMessage()
                ];

                $this->logger->error("Erro ao processar CEPs: {$cepOrigem} -> {$cepDestino}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        fclose($handle);

        if ($calculationCount >= $this->maxCalculations) {
            $results['warning'] = "Limite de {$this->maxCalculations} cálculos atingido. Processe o restante em outra importação.";
        }

        return $results;
    }
}

