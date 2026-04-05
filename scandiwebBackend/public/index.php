<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GraphQL\GraphQL;
use GraphQL\Error\DebugFlag;
use App\GraphQL\SchemaBuilder;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $schema = SchemaBuilder::build();

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    $query = $input['query'] ?? '';
    $variables = $input['variables'] ?? null;
    $operationName = $input['operationName'] ?? null;

    $result = GraphQL::executeQuery(
        $schema,
        $query,
        null,
        null,
        $variables,
        $operationName
    );

    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
        ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
        : DebugFlag::NONE;

    echo json_encode($result->toArray($debug));
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'errors' => [['message' => $e->getMessage()]],
    ]);
}
