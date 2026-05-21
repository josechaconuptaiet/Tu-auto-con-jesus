<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Public actions
if ($action === 'list') {
    $stmt = $pdo->query("SELECT m.*, (SELECT COUNT(*) FROM cars c WHERE c.marca_id = m.id AND c.status = 'active') as car_count FROM marcas m ORDER BY m.nombre ASC");
    $marcas = $stmt->fetchAll();
    echo json_encode(['marcas' => $marcas]);
    exit;
}

if ($action === 'get_models') {
    $marca_id = (int)($_GET['marca_id'] ?? 0);
    if ($marca_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'marca_id es requerido']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT DISTINCT modelo FROM cars WHERE marca_id = ? AND status = 'active' ORDER BY modelo ASC");
    $stmt->execute([$marca_id]);
    $modelos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['modelos' => $modelos]);
    exit;
}

// Admin-only actions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

switch ($action) {
    case 'create':
        $nombre = trim($_POST['nombre'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $logo = null;

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre es requerido']);
            exit;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', preg_replace('/[áàäâã]/u', 'a', preg_replace('/[éèëê]/u', 'e', preg_replace('/[íìïî]/u', 'i', preg_replace('/[óòöôõ]/u', 'o', preg_replace('/[úùüûũ]/u', 'u', $nombre)))))), '-'));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
        }

        // Check unique slug
        $check = $pdo->prepare("SELECT COUNT(*) FROM marcas WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        // Handle logo upload
        if (!empty($_FILES['logo']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $logo_path = 'uploads/marca_' . time() . '_' . $_FILES['logo']['name'];
            if (move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../' . $logo_path)) {
                $logo = $logo_path;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO marcas (nombre, slug, logo) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $slug, $logo]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'edit':
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($id) || empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID y nombre son requeridos']);
            exit;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', preg_replace('/[áàäâã]/u', 'a', preg_replace('/[éèëê]/u', 'e', preg_replace('/[íìïî]/u', 'i', preg_replace('/[óòöôõ]/u', 'o', preg_replace('/[úùüûũ]/u', 'u', $nombre)))))), '-'));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
        }

        // Check unique slug (excluding current)
        $check = $pdo->prepare("SELECT COUNT(*) FROM marcas WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        $logo = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $logo_path = 'uploads/marca_' . time() . '_' . $_FILES['logo']['name'];
            if (move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../' . $logo_path)) {
                $logo = $logo_path;
                $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, slug = ?, logo = ? WHERE id = ?");
                $stmt->execute([$nombre, $slug, $logo, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, slug = ? WHERE id = ?");
                $stmt->execute([$nombre, $slug, $id]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, slug = ? WHERE id = ?");
            $stmt->execute([$nombre, $slug, $id]);
        }

        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID es requerido']);
            exit;
        }

        // Check if brand has cars
        $check = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE marca_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'No se puede eliminar: la marca tiene autos asociados']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM marcas WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
