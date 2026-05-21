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
$limit = max(1, min(48, $limit));

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$offset = max(0, $offset);

$q = trim($_GET['q'] ?? '');
$price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (float) $_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (float) $_GET['price_max'] : null;
$order_by = $_GET['order_by'] ?? 'date_desc';
$marca_id = isset($_GET['marca_id']) && $_GET['marca_id'] !== '' ? (int) $_GET['marca_id'] : null;
$modelo = trim($_GET['modelo'] ?? '');

$specs_filter = $_GET['specs'] ?? [];

$where = [];
$params = [];
$where[] = "c.status = 'active'";

if ($q !== '') {
    $where[] = '(c.title LIKE :like OR c.description LIKE :like)';
    $params[':like'] = '%' . $q . '%';
}

if ($price_min !== null) {
    $where[] = 'c.price >= :price_min';
    $params[':price_min'] = $price_min;
}

if ($price_max !== null) {
    $where[] = 'c.price <= :price_max';
    $params[':price_max'] = $price_max;
}

if ($marca_id !== null) {
    $where[] = 'c.marca_id = :marca_id';
    $params[':marca_id'] = $marca_id;
}

if ($modelo !== '') {
    $where[] = 'c.modelo = :modelo';
    $params[':modelo'] = $modelo;
}

if (!empty($specs_filter) && is_array($specs_filter)) {
    foreach ($specs_filter as $slug => $value) {
        $alias = 'spec_' . preg_replace('/[^a-zA-Z0-9_]/', '', $slug);
        $where[] = "c.id IN (
            SELECT cs.car_id FROM car_specs cs
            LEFT JOIN spec_fields sf ON cs.spec_field_id = sf.id
            WHERE (sf.slug = :{$alias}_slug OR cs.etiqueta = :{$alias}_label)
            AND cs.valor = :{$alias}_value
        )";
        $params[":{$alias}_slug"] = $slug;
        $params[":{$alias}_label"] = $slug;
        $params[":{$alias}_value"] = $value;
    }
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

switch ($order_by) {
    case 'price_asc':
        $order = 'c.price ASC';
        break;
    case 'price_desc':
        $order = 'c.price DESC';
        break;
    case 'name_asc':
        $order = 'c.title ASC';
        break;
    case 'name_desc':
        $order = 'c.title DESC';
        break;
    case 'date_asc':
        $order = 'c.created_at ASC';
        break;
    case 'date_desc':
    default:
        $order = 'c.created_at DESC';
        break;
}

$countSql = "SELECT COUNT(*) FROM cars c {$whereClause}";
try {
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al contar vehículos']);
    exit;
}

$sql = "SELECT c.id, c.title, c.slug, c.price, c.image_path, c.description, c.status, c.featured, c.created_at
        FROM cars c
        {$whereClause}
        ORDER BY {$order}
        LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar vehículos']);
    exit;
}

foreach ($rows as &$row) {
    $row['image_path'] = get_asset_url($row['image_path']);
}

$page = floor($offset / $limit) + 1;
$total_pages = ceil($total / $limit);

echo json_encode([
    'items' => $rows,
    'total' => $total,
    'page' => $page,
    'total_pages' => $total_pages,
    'limit' => $limit,
    'offset' => $offset,
]);
