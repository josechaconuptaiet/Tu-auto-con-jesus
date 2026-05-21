<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        $car_id = $_GET['car_id'] ?? null;
        if ($car_id !== null) {
            $stmt = $pdo->prepare("SELECT * FROM car_components WHERE car_id = ? ORDER BY sort_order ASC");
            $stmt->execute([(int)$car_id]);
        } else {
            $stmt = $pdo->query("SELECT * FROM car_components WHERE car_id IS NULL ORDER BY sort_order ASC");
        }
        $components = $stmt->fetchAll();
        echo json_encode(['components' => $components]);
        break;

    case 'list_all':
        $stmt = $pdo->query("SELECT * FROM car_components ORDER BY car_id ASC, sort_order ASC");
        $components = $stmt->fetchAll();
        echo json_encode(['components' => $components]);
        break;

    case 'create':
        $car_id = $_POST['car_id'] ?? null;
        $component_type = trim($_POST['component_type'] ?? '');
        $config = $_POST['config'] ?? '{}';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($component_type)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de componente es requerido']);
            exit;
        }

        if ($car_id !== '' && $car_id !== null) {
            $car_id = (int)$car_id;
        } else {
            $car_id = null;
        }

        $stmt = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$car_id, $component_type, $config, $is_active, $sort_order]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'edit':
        $id = (int)($_POST['id'] ?? 0);
        $component_type = trim($_POST['component_type'] ?? '');
        $config = $_POST['config'] ?? '{}';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($id) || empty($component_type)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID y tipo de componente son requeridos']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE car_components SET component_type = ?, config = ?, is_active = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$component_type, $config, $is_active, $sort_order, $id]);

        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM car_components WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    case 'reorder':
        $car_id = $_POST['car_id'] ?? null;
        $order = $_POST['order'] ?? [];
        if (empty($order)) {
            http_response_code(400);
            echo json_encode(['error' => 'Orden es requerido']);
            exit;
        }

        if ($car_id !== '' && $car_id !== null) {
            $car_id = (int)$car_id;
            $stmt = $pdo->prepare("UPDATE car_components SET sort_order = ? WHERE id = ? AND car_id = ?");
            foreach ($order as $index => $comp_id) {
                $stmt->execute([(int)$index + 1, (int)$comp_id, $car_id]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE car_components SET sort_order = ? WHERE id = ? AND car_id IS NULL");
            foreach ($order as $index => $comp_id) {
                $stmt->execute([(int)$index + 1, (int)$comp_id]);
            }
        }

        echo json_encode(['success' => true]);
        break;

    case 'copy_defaults':
        $car_id = (int)($_POST['car_id'] ?? 0);
        if (empty($car_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID del auto es requerido']);
            exit;
        }

        $stmt = $pdo->query("SELECT component_type, config, is_active, sort_order FROM car_components WHERE car_id IS NULL ORDER BY sort_order ASC");
        $defaults = $stmt->fetchAll();

        $insert = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($defaults as $comp) {
            $insert->execute([$car_id, $comp['component_type'], $comp['config'], $comp['is_active'], $comp['sort_order']]);
        }

        echo json_encode(['success' => true, 'count' => count($defaults)]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
