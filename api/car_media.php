<?php
session_start();
require_once __DIR__ . '/db_connect.php';

@ini_set('upload_max_filesize', '50M');
@ini_set('post_max_size', '50M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');
@ini_set('memory_limit', '256M');

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list_images':
        $car_id = (int)($_GET['car_id'] ?? 0);
        if (empty($car_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID del auto es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$car_id]);
        $images = $stmt->fetchAll();

        foreach ($images as &$img) {
            $img['image_path'] = get_asset_url($img['image_path']);
        }

        echo json_encode(['images' => $images]);
        break;

    case 'upload_image':
        $car_id = (int)($_POST['car_id'] ?? 0);
        $skip_db = ($car_id === 0);

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'No se recibió ninguna imagen']);
            exit;
        }

        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo no permitido']);
            exit;
        }

        $max_size = 10 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(['error' => 'La imagen es demasiado grande (máx 10MB)']);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'car_' . ($car_id ?: 'tmp') . '_' . uniqid('', true) . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/';
        $dest = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir la imagen']);
            exit;
        }

        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if ($skip_db) {
            echo json_encode(['success' => true, 'id' => 0, 'path' => 'uploads/' . $filename]);
        } else {
            if ($is_primary) {
                $pdo->prepare("UPDATE car_images SET is_primary = 0 WHERE car_id = ?")->execute([$car_id]);
            }
            $stmt = $pdo->prepare("INSERT INTO car_images (car_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$car_id, 'uploads/' . $filename, $is_primary, $sort_order]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'path' => 'uploads/' . $filename]);
        }
        break;

    case 'delete_image':
        $id = (int)($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetch();

        if ($img) {
            $file_path = __DIR__ . '/../' . $img['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $pdo->prepare("DELETE FROM car_images WHERE id = ?")->execute([$id]);
        }

        echo json_encode(['success' => true]);
        break;

    case 'set_primary':
        $id = (int)($_POST['id'] ?? 0);
        $car_id = (int)($_POST['car_id'] ?? 0);
        if (empty($id) || empty($car_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID e ID del auto son requeridos']);
            exit;
        }

        $pdo->prepare("UPDATE car_images SET is_primary = 0 WHERE car_id = ?")->execute([$car_id]);
        $pdo->prepare("UPDATE car_images SET is_primary = 1 WHERE id = ? AND car_id = ?")->execute([$id, $car_id]);

        echo json_encode(['success' => true]);
        break;

    case 'reorder_images':
        $car_id = (int)($_POST['car_id'] ?? 0);
        $order = $_POST['order'] ?? [];
        if (empty($car_id) || empty($order)) {
            http_response_code(400);
            echo json_encode(['error' => 'Orden es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE car_images SET sort_order = ? WHERE id = ? AND car_id = ?");
        foreach ($order as $index => $img_id) {
            $stmt->execute([(int)$index + 1, (int)$img_id, $car_id]);
        }

        echo json_encode(['success' => true]);
        break;

    case 'list_videos':
        $car_id = (int)($_GET['car_id'] ?? 0);
        if (empty($car_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID del auto es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM car_videos WHERE car_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$car_id]);
        $videos = $stmt->fetchAll();

        echo json_encode(['videos' => $videos]);
        break;

    case 'add_video':
        $car_id = (int)($_POST['car_id'] ?? 0);
        $url = trim($_POST['url'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($car_id) || empty($url)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID del auto y URL son requeridos']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO car_videos (car_id, url, titulo, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$car_id, $url, $titulo, $sort_order]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'delete_video':
        $id = (int)($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID es requerido']);
            exit;
        }

        $pdo->prepare("DELETE FROM car_videos WHERE id = ?")->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    case 'reorder_videos':
        $car_id = (int)($_POST['car_id'] ?? 0);
        $order = $_POST['order'] ?? [];
        if (empty($car_id) || empty($order)) {
            http_response_code(400);
            echo json_encode(['error' => 'Orden es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE car_videos SET sort_order = ? WHERE id = ? AND car_id = ?");
        foreach ($order as $index => $vid_id) {
            $stmt->execute([(int)$index + 1, (int)$vid_id, $car_id]);
        }

        echo json_encode(['success' => true]);
        break;

    case 'upload_component_image':
        $car_id = (int)($_POST['car_id'] ?? 0);
        $component_type = trim($_POST['component_type'] ?? '');
        $field = trim($_POST['field'] ?? '');
        if (empty($car_id) || empty($component_type) || empty($field)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID del auto, tipo de componente y campo son requeridos']);
            exit;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'No se recibió ninguna imagen']);
            exit;
        }

        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo no permitido']);
            exit;
        }

        $max_size = 10 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(['error' => 'La imagen es demasiado grande (máx 10MB)']);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $field_clean = preg_replace('/[^a-z0-9_]/', '_', $field);
        $filename = 'car_' . $car_id . '_' . $component_type . '_' . $field_clean . '_' . uniqid('', true) . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/';
        $dest = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir la imagen']);
            exit;
        }

        echo json_encode(['success' => true, 'path' => 'uploads/' . $filename]);
        break;

    case 'delete_component_image':
        $car_id = (int)($_POST['car_id'] ?? 0);
        $component_type = trim($_POST['component_type'] ?? '');
        $field = trim($_POST['field'] ?? '');
        $image_path = trim($_POST['image_path'] ?? '');
        if (empty($car_id) || empty($component_type) || empty($field) || empty($image_path)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }

        $file_path = __DIR__ . '/../' . $image_path;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
