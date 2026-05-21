<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 12;
        $limit = max(1, min(24, $limit));

        $cursor = null;
        if (isset($_GET['cursor']) && $_GET['cursor'] !== '') {
            $cursorVal = (int) $_GET['cursor'];
            if ($cursorVal > 0) {
                $cursor = $cursorVal;
            }
        }

        $q = trim($_GET['q'] ?? '');
        $marca_id = isset($_GET['marca_id']) && $_GET['marca_id'] !== '' ? (int) $_GET['marca_id'] : null;

        $where = [];
        $params = [];

        if ($cursor !== null) {
            $where[] = 'id < :cursor';
            $params[':cursor'] = $cursor;
        }

        if ($q !== '') {
            $where[] = '(title LIKE :like OR description LIKE :like)';
            $params[':like'] = '%' . $q . '%';
        }

        if ($marca_id !== null) {
            $where[] = 'marca_id = :marca_id';
            $params[':marca_id'] = $marca_id;
        }

        $where[] = "status = 'active'";

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $fetchLimit = $limit + 1;

        $sql = "SELECT id, title, slug, price, image_path, description, status, featured, created_at
                FROM cars
                {$whereClause}
                ORDER BY id DESC
                LIMIT {$fetchLimit}";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al cargar vehículos']);
            exit;
        }

        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            $rows = array_slice($rows, 0, $limit);
        }

        $nextCursor = null;
        if ($hasMore && !empty($rows)) {
            $nextCursor = (int) end($rows)['id'];
        }

        foreach ($rows as &$row) {
            $row['image_path'] = get_asset_url($row['image_path']);
        }

        echo json_encode([
            'items' => $rows,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ]);
        break;

    case 'get_by_slug':
        $slug = trim($_GET['slug'] ?? '');
        if (empty($slug)) {
            http_response_code(400);
            echo json_encode(['error' => 'Slug es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM cars WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            http_response_code(404);
            echo json_encode(['error' => 'Auto no encontrado']);
            exit;
        }

        $car['image_path'] = get_asset_url($car['image_path']);

        // Get images
        $stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$car['id']]);
        $car['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($car['images'] as &$img) {
            $img['image_path'] = get_asset_url($img['image_path']);
        }

        // Get videos
        $stmt = $pdo->prepare("SELECT * FROM car_videos WHERE car_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$car['id']]);
        $car['videos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get specs
        $stmt = $pdo->prepare("
            SELECT cs.id, cs.valor, cs.etiqueta, cs.sort_order, sf.nombre, sf.slug, sf.tipo
            FROM car_specs cs
            LEFT JOIN spec_fields sf ON cs.spec_field_id = sf.id
            WHERE cs.car_id = ?
            ORDER BY cs.sort_order ASC
        ");
        $stmt->execute([$car['id']]);
        $car['specs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get components
        $stmt = $pdo->prepare("SELECT * FROM car_components WHERE car_id = ? AND is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute([$car['id']]);
        $car['components'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['car' => $car]);
        break;

    case 'get_recent':
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 3;
        $stmt = $pdo->prepare("SELECT id, title, price, image_path FROM cars WHERE status = 'active' ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cars as &$car) {
            $car['image_path'] = get_asset_url($car['image_path']);
        }

        echo json_encode(['cars' => $cars]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción inválida']);
        break;
}
