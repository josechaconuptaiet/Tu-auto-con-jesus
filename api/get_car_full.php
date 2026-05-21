<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$car_id = (int)($_GET['id'] ?? 0);
if (empty($car_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID del auto es requerido']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    http_response_code(404);
    echo json_encode(['error' => 'Auto no encontrado']);
    exit;
}

$car['image_path'] = $car['image_path'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY sort_order ASC");
$stmt->execute([$car_id]);
$car['images'] = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM car_videos WHERE car_id = ? ORDER BY sort_order ASC");
$stmt->execute([$car_id]);
$car['videos'] = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT cs.id, cs.valor, cs.etiqueta, cs.sort_order, sf.nombre, sf.slug, sf.tipo, cs.spec_field_id
    FROM car_specs cs
    LEFT JOIN spec_fields sf ON cs.spec_field_id = sf.id
    WHERE cs.car_id = ?
    ORDER BY cs.sort_order ASC
");
$stmt->execute([$car_id]);
$car['specs'] = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM car_components WHERE car_id = ? ORDER BY sort_order ASC");
$stmt->execute([$car_id]);
$car['components'] = $stmt->fetchAll();
foreach ($car['components'] as &$comp) {
    $comp['config'] = json_decode($comp['config'] ?? '{}', true) ?: [];
}

echo json_encode(['car' => $car]);
