<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'tuautoconjesus';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]));
}

// Calcular el base_url dinámico para soportar cualquier subcarpeta, dominio, IP o puerto
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_path = rtrim(dirname($script_name), '/\\');

// Asegurar que si estamos en el directorio admin o api, los removemos para obtener la raíz
if (preg_match('/\/(admin|api)$/i', $base_path)) {
    $base_path = preg_replace('/\/(admin|api)$/i', '', $base_path);
} elseif (preg_match('/\/(admin|api)\/.*$/i', $script_name)) {
    $base_path = preg_replace('/\/(admin|api)(\/.*)?$/i', '', $base_path);
}

$base_url = rtrim($base_path, '/') . '/';

function get_asset_url($path)
{
    global $base_url;
    if (!$path)
        return $base_url . 'logo3.png';
    // Limpiar rutas relativas viejas o barras iniciales
    $path = preg_replace('/^\/?(tuautoconjesusguerrero\/)?/i', '', ltrim($path, '/'));
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    return $base_url . $path;
}
?>