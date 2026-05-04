<?php
use GraphQL\GraphQL;
use GraphQL\Error\DebugFlag;
use HemaScorecard\GraphQL\SchemaBuilder;

// Headers first — before any output that could break them
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

ini_set('display_errors', '0');
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['errors' => [['message' => $err['message'], 'file' => $err['file'], 'line' => $err['line']]]]);
    }
});

try {
    require_once 'vendor/autoload.php';
    require_once __DIR__ . '/graphql/bootstrap.php';

    $input     = json_decode(file_get_contents('php://input'), true);
    $query     = $input['query']         ?? null;
    $variables = $input['variables']     ?? null;
    $opName    = $input['operationName'] ?? null;

    $schema  = SchemaBuilder::build();
    $context = ['userID' => $_SESSION['userID'] ?? null, 'eventID' => $_SESSION['eventID'] ?? null];

    $result = GraphQL::executeQuery($schema, $query, null, $context, $variables, $opName);

    echo json_encode($result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE));

} catch (\Throwable $e) {
    echo json_encode(['errors' => [['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]]]);
}
