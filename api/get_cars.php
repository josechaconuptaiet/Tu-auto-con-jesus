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

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Shorthands
if ($shorthand === 'recent') {
    // Para simplificar, ordenamos de forma que los de mayor ID aparezcan primero (esto ya se hace en ORDER BY),
    // pero podemos restringir a los últimos agregados o simplemente dejarlo así.
    // Para "recent" podemos no filtrar pero limitar, o podemos dejarlo como un comportamiento que maneja el frontend,
    // o filtrar por id mayor al max(id) - 10. Let's do id > (SELECT MAX(id) - 8 FROM cars)
    $where[] = "id > (SELECT IFNULL(MAX(id), 0) - 8 FROM cars)";
} elseif ($shorthand === 'luxury') {
    $where[] = "price >= 100000";
} elseif ($shorthand === 'budget') {
    $where[] = "price < 100000";
}

// Price Filter
if ($price_filter === '50k') {
    $where[] = "price < 50000";
} elseif ($price_filter === '100k') {
    $where[] = "price < 100000";
} elseif ($price_filter === '150k') {
    $where[] = "price < 150000";
} elseif ($price_filter === '150k_plus') {
    $where[] = "price >= 150000";
}

$where_clause = implode(" AND ", $where);

// Count total for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE $where_clause");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();

// Fetch data
$sql = "SELECT * FROM cars WHERE $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
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
