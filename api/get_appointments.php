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
$date_filter = $_GET['date_filter'] ?? '';
$shorthand = $_GET['shorthand'] ?? 'all';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($date_filter)) {
    $where[] = "appointment_date = ?";
    $params[] = $date_filter;
} elseif ($shorthand === 'today') {
    $where[] = "appointment_date = CURDATE()";
} elseif ($shorthand === 'upcoming') {
    $where[] = "appointment_date >= CURDATE()";
}

$where_clause = implode(" AND ", $where);

// Count total for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE $where_clause");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();

// Fetch data
$sql = "SELECT * FROM appointments WHERE $where_clause ORDER BY appointment_date DESC, appointment_time DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'total' => $total,
    'appointments' => $appointments
]);
