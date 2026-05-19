<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$dbname = 'dealership_db';

try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // Crear tabla settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT
    )");

    // Crear tabla carousel_images
    $pdo->exec("CREATE TABLE IF NOT EXISTS carousel_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_path VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 1
    )");

    // Crear tabla services
    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        icon VARCHAR(50) NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Crear tabla cars
    $pdo->exec("CREATE TABLE IF NOT EXISTS cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        description TEXT
    )");

    // Crear tabla appointments
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_slot (appointment_date, appointment_time)
    )");

    // Crear tabla weekly_schedule
    // Table for Recurring Weekly Schedule
    $pdo->exec("CREATE TABLE IF NOT EXISTS weekly_schedule (
        id INT AUTO_INCREMENT PRIMARY KEY,
        day_of_week INT NOT NULL, -- 1=Lunes, 7=Domingo
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        slot_duration INT DEFAULT 60,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Table for Schedule Exceptions (Holidays, Special Hours)
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_exceptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exception_date DATE NOT NULL,
        start_time TIME NULL,
        end_time TIME NULL,
        slot_duration INT DEFAULT 60,
        is_closed BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (exception_date)
    )");

    // Drop old availability table if it exists
    $pdo->exec("DROP TABLE IF EXISTS availability");

    // Insertar configuración por defecto si no existen
    $default_settings = [
        ['logo', 'logo3.png'],
        ['favicon', 'logo3.png'],
        ['social_facebook', 'https://facebook.com'],
        ['social_instagram', 'https://instagram.com'],
        ['social_twitter', ''],
        ['social_youtube', ''],
        ['whatsapp_number', '+1234567890'],
        ['whatsapp_message_template', 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?'],
        ['calc_min_price', '5000'],
        ['calc_max_price', '100000'],
        ['calc_min_downpayment', '0'],
        ['calc_max_downpayment', '50000'],
        ['calc_default_apr', '5'],
        ['calc_terms', '12,24,36,48,60,72,84'],
        ['appointment_window_days', '30']
    ];
    $insert = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($default_settings as $setting) {
        $insert->execute($setting);
    }

    // Insertar servicios por defecto si la tabla está vacía
    $stmt = $pdo->query("SELECT COUNT(*) FROM services");
    if ($stmt->fetchColumn() == 0) {
        $default_services = [
            ['fas fa-handshake', 'VENTA', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
            ['fas fa-car-side', 'ARRENDAMIENTO', 'Arrendamiento puro con un concesionario profesional pronto.'],
            ['fas fa-file-invoice-dollar', 'FINANCIAMIENTO', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod.'],
            ['fas fa-hand-holding-usd', 'CONSIGNACIÓN', 'Arrendamiento puro con un concesionario profesional pronto.']
        ];
        $insert_service = $pdo->prepare("INSERT INTO services (icon, title, description) VALUES (?, ?, ?)");
        foreach ($default_services as $svc) {
            $insert_service->execute($svc);
        }
    }

    // Insertar horario semanal por defecto si está vacío
    $stmt = $pdo->query("SELECT COUNT(*) FROM weekly_schedule");
    if ($stmt->fetchColumn() == 0) {
        $default_schedule = [
            [1, '09:00:00', '18:00:00', 60], // Lunes
            [2, '09:00:00', '18:00:00', 60], // Martes
            [3, '09:00:00', '18:00:00', 60], // Miércoles
            [4, '09:00:00', '18:00:00', 60], // Jueves
            [5, '09:00:00', '18:00:00', 60], // Viernes
            [6, '09:00:00', '13:00:00', 60]  // Sábado
        ];
        $insert = $pdo->prepare("INSERT INTO weekly_schedule (day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?)");
        foreach ($default_schedule as $sch) {
            $insert->execute($sch);
        }
    }

    echo "Base de datos y tablas creadas exitosamente.\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
