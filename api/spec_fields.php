<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// The list action is public (needed for catalog filters)
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM spec_fields ORDER BY sort_order ASC");
    $fields = $stmt->fetchAll();
    echo json_encode(['fields' => $fields]);
    exit;
}

// All other actions require admin auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT * FROM spec_fields ORDER BY sort_order ASC");
        $fields = $stmt->fetchAll();
        echo json_encode(['fields' => $fields]);
        break;

    case 'create':
        $nombre = trim($_POST['nombre'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $tipo = $_POST['tipo'] ?? 'text';
        $opciones = $_POST['opciones'] ?? null;
        $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($nombre) || empty($slug)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y slug son requeridos']);
            exit;
        }

        if ($opciones && is_string($opciones)) {
            $decoded = json_decode($opciones, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $opciones = json_encode(explode(',', $opciones));
            } else {
                $opciones = json_encode($decoded);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO spec_fields (nombre, slug, tipo, opciones, obligatorio, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $slug, $tipo, $opciones, $obligatorio, $sort_order]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'edit':
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $tipo = $_POST['tipo'] ?? 'text';
        $opciones = $_POST['opciones'] ?? null;
        $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($id) || empty($nombre) || empty($slug)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID, nombre y slug son requeridos']);
            exit;
        }

        if ($opciones && is_string($opciones)) {
            $decoded = json_decode($opciones, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $opciones = json_encode(explode(',', $opciones));
            } else {
                $opciones = json_encode($decoded);
            }
        }

        $stmt = $pdo->prepare("UPDATE spec_fields SET nombre = ?, slug = ?, tipo = ?, opciones = ?, obligatorio = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$nombre, $slug, $tipo, $opciones, $obligatorio, $sort_order, $id]);

        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM spec_fields WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    case 'reorder':
        $order = $_POST['order'] ?? [];
        if (empty($order)) {
            http_response_code(400);
            echo json_encode(['error' => 'Orden es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE spec_fields SET sort_order = ? WHERE id = ?");
        foreach ($order as $index => $field_id) {
            $stmt->execute([(int)$index + 1, (int)$field_id]);
        }

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
