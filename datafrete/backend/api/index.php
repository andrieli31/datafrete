<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Dependências não instaladas. Execute: composer install na raiz do projeto'
    ]);
    exit;
}

require_once $autoloadPath;

use App\Models\Distance;
use App\Services\BrasilApiService;
use App\Services\DistanceCalculator;
use App\Services\CsvImporter;
use App\Services\Logger;

$logger = new Logger();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    $path = '/';
    
    if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    }
    elseif (isset($_GET['route'])) {
        $path = '/' . $_GET['route'];
    }
    else {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        
        $requestUri = strtok($requestUri, '?');
        
        $basePath = dirname($scriptName);
        if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        $requestUri = str_replace('/index.php', '', $requestUri);
        
        if (!empty($requestUri) && $requestUri !== '/') {
            $path = $requestUri;
        }
    }
    switch ($path) {
        case '/distances':
            if ($method === 'GET') {
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $offset = ($page - 1) * $limit;

                $distanceModel = new Distance();
                $distances = $distanceModel->findAll($limit, $offset);
                $total = $distanceModel->count();

                echo json_encode([
                    'success' => true,
                    'data' => $distances,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit
                ]);
            } elseif ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($input['cep_origem']) || !isset($input['cep_destino'])) {
                    throw new \Exception("CEP origem e CEP destino são obrigatórios");
                }

                $cepOrigem = $input['cep_origem'];
                $cepDestino = $input['cep_destino'];

                $distanceModel = new Distance();
                $existing = $distanceModel->findByCeps($cepOrigem, $cepDestino);
                if ($existing) {
                    echo json_encode([
                        'success' => true,
                        'data' => $existing,
                        'message' => 'Distância já cadastrada'
                    ]);
                    exit;
                }

                $brasilApi = new BrasilApiService();
                $coordsOrigem = $brasilApi->getCoordinates($cepOrigem);
                $coordsDestino = $brasilApi->getCoordinates($cepDestino);

                $calculator = new DistanceCalculator();
                $distancia = $calculator->calculate(
                    $coordsOrigem['latitude'],
                    $coordsOrigem['longitude'],
                    $coordsDestino['latitude'],
                    $coordsDestino['longitude']
                );

                $id = $distanceModel->create($cepOrigem, $cepDestino, $distancia);
                $distance = $distanceModel->findByCeps($cepOrigem, $cepDestino);

                $logger->info("Nova distância calculada", [
                    'cep_origem' => $cepOrigem,
                    'cep_destino' => $cepDestino,
                    'distancia' => $distancia
                ]);

                echo json_encode([
                    'success' => true,
                    'data' => $distance,
                    'message' => 'Distância calculada e salva com sucesso'
                ]);
            } else {
                throw new \Exception("Método não permitido");
            }
            break;

        case '/import':
            if ($method === 'POST') {
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    throw new \Exception("Arquivo não enviado ou com erro");
                }

                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . $_FILES['file']['name'];
                $filePath = $uploadDir . $fileName;

                if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                    throw new \Exception("Erro ao fazer upload do arquivo");
                }

                $importer = new CsvImporter();
                $results = $importer->import($filePath);

                @unlink($filePath);

                echo json_encode([
                    'success' => true,
                    'data' => $results
                ]);
            } else {
                throw new \Exception("Método não permitido");
            }
            break;

        case '/validate-cep':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['cep'])) {
                    throw new \Exception("CEP é obrigatório");
                }

                $brasilApi = new BrasilApiService();
                $info = $brasilApi->getCepInfo($input['cep']);

                echo json_encode([
                    'success' => true,
                    'data' => $info
                ]);
            } else {
                throw new \Exception("Método não permitido");
            }
            break;

        default:
            throw new \Exception("Rota não encontrada");
    }

} catch (\Exception $e) {
    http_response_code(400);
    $logger->error("Erro na API", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

