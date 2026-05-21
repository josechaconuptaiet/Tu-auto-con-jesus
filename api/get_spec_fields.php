<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$search = $_GET['search'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(nombre LIKE ? OR slug LIKE ?)";
    $p = "%$search%";
    $params[] = $p;
    $params[] = $p;
}

if (!empty($filter_type)) {
    $where[] = "tipo = ?";
    $params[] = $filter_type;
}

$where_clause = implode(" AND ", $where);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM spec_fields WHERE $where_clause");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();

$sql = "SELECT * FROM spec_fields WHERE $where_clause ORDER BY sort_order ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'total' => $total,
    'fields' => $fields
]);
