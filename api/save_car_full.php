<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$action = $input['action'] ?? '';
$mode = $input['mode'] ?? '';

if ($mode === 'create') {
    $title = trim($input['title'] ?? '');
    $slug = trim($input['slug'] ?? '');
    $marca_id = (int)($input['marca_id'] ?? 0);
    $modelo = trim($input['modelo'] ?? '');
    $price = (float)($input['price'] ?? 0);
    $description = trim($input['description'] ?? '');
    $status = $input['status'] ?? 'active';
    $featured = !empty($input['featured']) ? 1 : 0;

    if (empty($slug) && !empty($title)) {
        $s = $title;
        $s = preg_replace('/[áàäâã]/u', 'a', $s);
        $s = preg_replace('/[éèëê]/u', 'e', $s);
        $s = preg_replace('/[íìïî]/u', 'i', $s);
        $s = preg_replace('/[óòöôõ]/u', 'o', $s);
        $s = preg_replace('/[úùüûũ]/u', 'u', $s);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
    }

    if (empty($title) || empty($slug) || $marca_id === 0 || $price === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Campos requeridos faltantes']);
        exit;
    }

    $image_path = '';
    if (!empty($input['image_path'])) {
        $image_path = $input['image_path'];
    }

    $stmt = $pdo->prepare("INSERT INTO cars (marca_id, modelo, title, slug, price, image_path, description, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$marca_id, $modelo, $title, $slug, $price, $image_path, $description, $status, $featured]);
    $car_id = $pdo->lastInsertId();

    $comp_stmt = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) SELECT ?, component_type, config, is_active, sort_order FROM car_components WHERE car_id IS NULL ORDER BY sort_order ASC");
    $comp_stmt->execute([$car_id]);

    echo json_encode(['success' => true, 'car_id' => $car_id, 'message' => 'Auto creado exitosamente']);

} elseif ($mode === 'save_all') {
    $car_id = (int)($input['car_id'] ?? 0);
    $is_update = ($car_id > 0);

    $title = trim($input['title'] ?? '');
    $slug = trim($input['slug'] ?? '');
    $marca_id = (int)($input['marca_id'] ?? 0);
    $modelo = trim($input['modelo'] ?? '');
    $price = (float)($input['price'] ?? 0);
    $description = trim($input['description'] ?? '');
    $status = $input['status'] ?? 'active';
    $featured = !empty($input['featured']) ? 1 : 0;
    $image_path = $input['image_path'] ?? '';

    if (empty($slug) && !empty($title)) {
        $s = $title;
        $s = preg_replace('/[áàäâã]/u', 'a', $s);
        $s = preg_replace('/[éèëê]/u', 'e', $s);
        $s = preg_replace('/[íìïî]/u', 'i', $s);
        $s = preg_replace('/[óòöôõ]/u', 'o', $s);
        $s = preg_replace('/[úùüûũ]/u', 'u', $s);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
    }

    if (empty($title) || empty($slug) || $marca_id === 0 || $price === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Campos requeridos faltantes']);
        exit;
    }

    if ($is_update) {
        $stmt = $pdo->prepare("UPDATE cars SET marca_id = ?, modelo = ?, title = ?, slug = ?, price = ?, description = ?, status = ?, featured = ?, image_path = ? WHERE id = ?");
        $stmt->execute([$marca_id, $modelo, $title, $slug, $price, $description, $status, $featured, $image_path, $car_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cars (marca_id, modelo, title, slug, price, image_path, description, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$marca_id, $modelo, $title, $slug, $price, $image_path, $description, $status, $featured]);
        $car_id = $pdo->lastInsertId();

        $comp_stmt = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) SELECT ?, component_type, config, is_active, sort_order FROM car_components WHERE car_id IS NULL ORDER BY sort_order ASC");
        $comp_stmt->execute([$car_id]);
    }

    $pdo->prepare("DELETE FROM car_specs WHERE car_id = ?")->execute([$car_id]);
    $specs = $input['specs'] ?? [];
    $sort = 1;
    $spec_insert = $pdo->prepare("INSERT INTO car_specs (car_id, spec_field_id, etiqueta, valor, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($specs as $spec) {
        $label = trim($spec['label'] ?? '');
        $value = trim($spec['value'] ?? '');
        if ($value === '') continue;
        $spec_field_id = !empty($spec['spec_field_id']) ? (int)$spec['spec_field_id'] : null;
        $etiqueta = $spec_field_id ? null : $label;
        $spec_insert->execute([$car_id, $spec_field_id, $etiqueta, $value, $sort]);
        $sort++;
    }

    $pdo->prepare("DELETE FROM car_components WHERE car_id = ?")->execute([$car_id]);
    $components = $input['components'] ?? [];
    $comp_insert = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($components as $idx => $comp) {
        $config = json_encode($comp['config'] ?? []);
        $is_active = !empty($comp['is_active']) ? 1 : 0;
        $comp_insert->execute([$car_id, $comp['component_type'], $config, $is_active, $idx + 1]);
    }

    $pdo->prepare("DELETE FROM car_images WHERE car_id = ?")->execute([$car_id]);
    $images = $input['images'] ?? [];
    $seen = [];
    $img_insert = $pdo->prepare("INSERT INTO car_images (car_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
    $sort = 1;
    foreach ($images as $img) {
        $path = trim($img['image_path'] ?? '');
        if (empty($path)) continue;
        if (isset($seen[$path])) continue;
        $seen[$path] = true;
        $is_primary = !empty($img['is_primary']) ? 1 : 0;
        $img_insert->execute([$car_id, $path, $is_primary, $sort]);
        $sort++;
    }

    $pdo->prepare("DELETE FROM car_videos WHERE car_id = ?")->execute([$car_id]);
    $videos = $input['videos'] ?? [];
    $vid_insert = $pdo->prepare("INSERT INTO car_videos (car_id, url, titulo, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($videos as $idx => $vid) {
        $url = trim($vid['url'] ?? '');
        $titulo = trim($vid['titulo'] ?? '');
        if (empty($url)) continue;
        $vid_insert->execute([$car_id, $url, $titulo, $idx + 1]);
    }

    $msg = $is_update ? 'Auto actualizado exitosamente' : 'Auto creado exitosamente';
    echo json_encode(['success' => true, 'car_id' => $car_id, 'message' => $msg]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Acción no válida']);
}
