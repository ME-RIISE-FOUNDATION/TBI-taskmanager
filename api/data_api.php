<?php
/**
 * Generic sync API used by the front-end DB layer.
 *
 *   GET  ?action=bootstrap                 -> { ok, data:{ entity: [...] , ... } }
 *   POST { action:'append',  entity, record }
 *   POST { action:'update',  entity, idField, idVal, upd }
 *   POST { action:'delete',  entity, idField, idVal }
 *   POST { action:'replace', entity, data:[...] }
 *
 * POST bodies are JSON. Writes are intentionally fire-and-forget friendly:
 * the front-end sends them with fetch keepalive and does not await a response.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/Store.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

function fail(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

try {
    $store = StoreFactory::make();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'bootstrap';
        if ($action !== 'bootstrap') fail(400, 'Unknown GET action');

        $data = [];
        foreach (array_keys(TBI_ENTITIES) as $entity) {
            $data[$entity] = $store->getAll($entity);
        }
        echo json_encode(['ok' => true, 'data' => $data]);
        exit;
    }

    // POST — JSON body
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) fail(400, 'Invalid JSON body');

    $action = $body['action'] ?? '';
    $entity = $body['entity'] ?? '';
    tbi_require_entity($entity);

    switch ($action) {
        case 'append':
            if (!isset($body['record']) || !is_array($body['record'])) fail(400, 'Missing record');
            $store->append($entity, $body['record']);
            break;

        case 'update':
            foreach (['idField', 'idVal', 'upd'] as $k) {
                if (!isset($body[$k])) fail(400, "Missing $k");
            }
            $store->update($entity, (string)$body['idField'], (string)$body['idVal'], (array)$body['upd']);
            break;

        case 'delete':
            foreach (['idField', 'idVal'] as $k) {
                if (!isset($body[$k])) fail(400, "Missing $k");
            }
            $store->delete($entity, (string)$body['idField'], (string)$body['idVal']);
            break;

        case 'replace':
            if (!isset($body['data']) || !is_array($body['data'])) fail(400, 'Missing data');
            $store->replace($entity, $body['data']);
            break;

        default:
            fail(400, "Unknown action: $action");
    }

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    error_log('[TBI] data_api error: ' . $e->getMessage());
    fail(500, 'Server error');
}
