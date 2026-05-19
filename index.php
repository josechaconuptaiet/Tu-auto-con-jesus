<?php
session_start();
require_once __DIR__ . '/api/db_connect.php';

// Cargar configuración global para usar en las vistas
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_db = $stmt->fetchAll();
$settings = [];
foreach ($settings_db as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

// Router básico
$route = $_GET['route'] ?? '';
$route = rtrim($route, '/');

// Limpiar la URL base si el proyecto está en una subcarpeta
$base_path = 'tuautoconjesusguerrero';
if (strpos($route, $base_path) === 0) {
    $route = substr($route, strlen($base_path));
    $route = ltrim($route, '/');
}

switch ($route) {
    case '':
    case 'home':
        require __DIR__ . '/views/home.php';
        break;
    
    // Rutas del admin
    case 'admin':
        require __DIR__ . '/admin/index.php';
        break;
    case 'admin/dashboard':
        require __DIR__ . '/admin/dashboard.php';
        break;
    case 'admin/logout':
        session_destroy();
        header("Location: " . $base_url . "admin");
        exit;

    default:
        // Manejar API u otras rutas
        if (strpos($route, 'api/') === 0) {
            $api_file = __DIR__ . '/' . $route . '.php';
            if (file_exists($api_file)) {
                require $api_file;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'API Endpoint not found']);
            }
        } else {
            require __DIR__ . '/views/404.php';
        }
        break;
}
?>
