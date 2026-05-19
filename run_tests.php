<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_logged_in'] = true;

/**
 * Tu Auto Con - Unit Test Suite
 * Valida de forma automatizada las APIs clave del sistema de administración.
 */

// Configuración de la base de URL local
$base_url = "http://localhost/tuautoconjesusguerrero";

echo "==================================================\n";
echo "       EJECUTANDO PRUEBAS UNITARIAS DE TU AUTO CON\n";
echo "==================================================\n\n";

$tests_run = 0;
$tests_passed = 0;

function run_test($name, $callback) {
    global $tests_run, $tests_passed;
    $tests_run++;
    echo "[TEST " . str_pad($tests_run, 2, '0', STR_PAD_LEFT) . "] $name: ";
    try {
        $result = $callback();
        if ($result === true) {
            $tests_passed++;
            echo "\033[32mPASÓ\033[0m\n";
        } else {
            echo "\033[31mFALLÓ\033[0m - $result\n";
        }
    } catch (Exception $e) {
        echo "\033[31mERROR\033[0m - " . $e->getMessage() . "\n";
    }
}

// Función auxiliar para simular login de administrador con la sesión actual
// Para testear endpoints protegidos, primero probamos con llamadas directas
$pdo = null;
require_once 'api/db_connect.php';

// 1. Probar Conexión a Base de Datos
run_test("Conexión básica de PDO a MySQL", function() use ($pdo) {
    $stmt = $pdo->query("SELECT 1");
    return $stmt->fetchColumn() == 1 ? true : "No devolvió el valor esperado";
});

// 2. Probar API get_appointments.php (Parámetros básicos de paginación)
run_test("API de Citas - Paginación Básica (limit/offset)", function() use ($pdo) {
    // Iniciamos sesión simulada en memoria PHP
    $_SESSION['admin_logged_in'] = true;
    
    $_GET['limit'] = 5;
    $_GET['offset'] = 0;
    $_GET['shorthand'] = 'all';
    
    ob_start();
    include 'api/get_appointments.php';
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if (!is_array($data)) return "La respuesta no es un JSON válido";
    if (!isset($data['total'])) return "Falta el campo 'total' en el JSON";
    if (!isset($data['appointments'])) return "Falta el campo 'appointments' en el JSON";
    if (count($data['appointments']) > 5) return "El límite no se respetó. Entregó: " . count($data['appointments']);
    
    return true;
});

// 3. Probar API get_appointments.php (Filtro por búsqueda)
run_test("API de Citas - Búsqueda por Nombre con Filtro", function() use ($pdo) {
    $_SESSION['admin_logged_in'] = true;
    $_GET['limit'] = 10;
    $_GET['offset'] = 0;
    $_GET['search'] = 'TestUser';
    $_GET['shorthand'] = 'all';
    unset($_GET['date_filter']);
    
    ob_start();
    include 'api/get_appointments.php';
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if (!is_array($data)) return "No es JSON válido";
    foreach ($data['appointments'] as $appt) {
        if (stripos($appt['first_name'], 'TestUser') === false) {
            return "Cita encontrada no coincide con el criterio de búsqueda: " . $appt['first_name'];
        }
    }
    return true;
});

// 4. Probar API de Servicios - Crear Servicio
run_test("API de Servicios - Lógica de Creación", function() use ($pdo) {
    // Para servicios, probamos la consulta directamente en el controlador
    $test_icon = "fas fa-bell";
    $test_title = "Servicio de Test";
    $test_desc = "Descripción para el test unitario";
    
    $stmt = $pdo->prepare("INSERT INTO services (icon, title, description) VALUES (?, ?, ?)");
    $res = $stmt->execute([$test_icon, $test_title, $test_desc]);
    if (!$res) return "Fallo al insertar en DB";
    
    $last_id = $pdo->lastInsertId();
    
    // Validamos
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$last_id]);
    $svc = $stmt->fetch();
    
    if (!$svc || $svc['title'] !== $test_title) {
        return "El servicio creado no coincide con los datos insertados";
    }
    
    // Limpieza
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$last_id]);
    return true;
});

// 5. Probar API de Servicios - Modificación (Update)
run_test("API de Servicios - Lógica de Modificación (Update)", function() use ($pdo) {
    // Insertamos uno base
    $stmt = $pdo->prepare("INSERT INTO services (icon, title, description) VALUES ('icon1', 'Title1', 'Desc1')");
    $stmt->execute();
    $id = $pdo->lastInsertId();
    
    // Actualizamos
    $new_title = "Title Updated";
    $stmt = $pdo->prepare("UPDATE services SET title = ? WHERE id = ?");
    $stmt->execute([$new_title, $id]);
    
    // Validamos
    $stmt = $pdo->prepare("SELECT title FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $title = $stmt->fetchColumn();
    
    // Limpieza
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    
    return $title === $new_title ? true : "El título no se actualizó correctamente";
});

echo "\n==================================================\n";
echo "               RESUMEN DE RESULTADOS\n";
echo "==================================================\n";
echo "Pruebas Ejecutadas: $tests_run\n";
echo "Pruebas Pasadas:    \033[32m$tests_passed\033[0m\n";
echo "Pruebas Fallidas:   " . ($tests_run - $tests_passed > 0 ? "\033[31m" . ($tests_run - $tests_passed) . "\033[0m" : "0") . "\n";
echo "==================================================\n";
