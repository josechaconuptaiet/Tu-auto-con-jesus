<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . $base_url . 'admin');
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $icon = $_POST['icon'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!empty($icon) && !empty($title) && !empty($description)) {
        $stmt = $pdo->prepare("INSERT INTO services (icon, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$icon, $title, $description]);
    }
} elseif ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $icon = $_POST['icon'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!empty($icon) && !empty($title) && !empty($description) && $id > 0) {
        $stmt = $pdo->prepare("UPDATE services SET icon = ?, title = ?, description = ? WHERE id = ?");
        $stmt->execute([$icon, $title, $description, $id]);
    }
} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: ' . $base_url . 'admin/dashboard.php');
exit;
