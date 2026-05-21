<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 6;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

// Get brands that have active cars
$stmt = $pdo->query("SELECT m.id, m.nombre, m.slug, m.logo FROM marcas m WHERE m.id IN (SELECT DISTINCT marca_id FROM cars WHERE status = 'active') ORDER BY m.nombre ASC");
$brand_rows = $stmt->fetchAll();

$brands = [];
foreach ($brand_rows as $brand) {
    // Count total cars for this brand
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE marca_id = ? AND status = 'active'");
    $count_stmt->execute([$brand['id']]);
    $total = (int)$count_stmt->fetchColumn();

    // Get cars for this brand with pagination
    $car_stmt = $pdo->prepare("SELECT c.id, c.title, c.slug, c.price, c.image_path FROM cars c WHERE c.marca_id = ? AND c.status = 'active' ORDER BY c.id DESC LIMIT $limit OFFSET $offset");
    $car_stmt->execute([$brand['id']]);
    $cars = $car_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cars as &$car) {
        $car['image_path'] = get_asset_url($car['image_path']);
    }
    unset($car);

    $brands[] = [
        'id' => $brand['id'],
        'nombre' => $brand['nombre'],
        'slug' => $brand['slug'],
        'logo' => get_asset_url($brand['logo']),
        'total' => $total,
        'has_more' => ($offset + $limit) < $total,
        'cars' => $cars
    ];
}

echo json_encode(['brands' => $brands]);
