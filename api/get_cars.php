<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$search = $_GET['search'] ?? '';
$shorthand = $_GET['shorthand'] ?? 'all';
$price_filter = $_GET['price_filter'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(c.title LIKE ? OR c.description LIKE ? OR c.slug LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($shorthand === 'recent') {
    $where[] = "c.id > (SELECT IFNULL(MAX(id), 0) - 8 FROM cars)";
} elseif ($shorthand === 'luxury') {
    $where[] = "c.price >= 100000";
} elseif ($shorthand === 'budget') {
    $where[] = "c.price < 100000";
}

if ($price_filter === '50k') {
    $where[] = "c.price < 50000";
} elseif ($price_filter === '100k') {
    $where[] = "c.price < 100000";
} elseif ($price_filter === '150k') {
    $where[] = "c.price < 150000";
} elseif ($price_filter === '150k_plus') {
    $where[] = "c.price >= 150000";
}

if ($status_filter !== 'all') {
    $where[] = "c.status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(" AND ", $where);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM cars c WHERE $where_clause");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM car_images ci WHERE ci.car_id = c.id) as image_count,
        (SELECT COUNT(*) FROM car_specs cs WHERE cs.car_id = c.id) as spec_count
        FROM cars c WHERE $where_clause ORDER BY c.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cars as &$car) {
    $car['image_path'] = get_asset_url($car['image_path']);
}

echo json_encode([
    'total' => $total,
    'cars' => $cars
]);
