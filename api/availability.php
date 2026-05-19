<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// Verify admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($method === 'GET') {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM availability ORDER BY available_date DESC");
        echo json_encode(['availability' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'appointments') {
        $stmt = $pdo->query("SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC");
        echo json_encode(['appointments' => $stmt->fetchAll()]);
        exit;
    }
}

if ($method === 'POST') {
    if ($action === 'add_date') {
        $date = $_POST['available_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '09:00:00';
        $end_time = $_POST['end_time'] ?? '17:00:00';
        $slot_duration = $_POST['slot_duration'] ?? 60;

        if (!$date) {
            echo json_encode(['success' => false, 'error' => 'Fecha es requerida']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO availability (available_date, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE start_time = ?, end_time = ?, slot_duration = ?");
            $stmt->execute([$date, $start_time, $end_time, $slot_duration, $start_time, $end_time, $slot_duration]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'remove_date') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM availability WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID requerido']);
        }
        exit;
    }

    if ($action === 'update_appointment_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if ($id && in_array($status, ['pendiente', 'confirmada', 'cancelada'])) {
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        }
        exit;
    }

    if ($action === 'delete_appointment') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID requerido']);
        }
        exit;
    }
}

echo json_encode(['error' => 'Acción inválida']);
