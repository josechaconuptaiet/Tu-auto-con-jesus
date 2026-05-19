<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action !== 'list') {
    http_response_code(400);
    echo json_encode(['error' => 'Acción inválida']);
    exit;
}

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 12;
$limit = max(1, min(24, $limit));

$cursor = null;
if (isset($_GET['cursor']) && $_GET['cursor'] !== '') {
    $cursorVal = (int) $_GET['cursor'];
    if ($cursorVal > 0) {
        $cursor = $cursorVal;
    }
}

$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($cursor !== null) {
    $where[] = 'id < :cursor';
    $params[':cursor'] = $cursor;
}

if ($q !== '') {
    $where[] = '(title LIKE :like OR description LIKE :like)';
    $params[':like'] = '%' . $q . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$fetchLimit = $limit + 1;

$sql = "SELECT id, title, price, image_path, description
        FROM cars
        {$whereClause}
        ORDER BY id DESC
        LIMIT {$fetchLimit}";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar vehículos']);
    exit;
}

$hasMore = count($rows) > $limit;
if ($hasMore) {
    $rows = array_slice($rows, 0, $limit);
}

$nextCursor = null;
if ($hasMore && !empty($rows)) {
    $nextCursor = (int) end($rows)['id'];
}

foreach ($rows as &$row) {
    $row['image_path'] = get_asset_url($row['image_path']);
}

echo json_encode([
    'items' => $rows,
    'next_cursor' => $nextCursor,
    'has_more' => $hasMore,
]);
