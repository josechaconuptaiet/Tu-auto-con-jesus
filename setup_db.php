<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$dbname = 'tuautoconjesus';

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

    // Disable foreign key checks for table recreation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Crear tabla marcas
    $pdo->exec("DROP TABLE IF EXISTS marcas");
    $pdo->exec("CREATE TABLE marcas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        logo VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Crear tabla cars (renovada)
    $pdo->exec("DROP TABLE IF EXISTS cars");
    $pdo->exec("CREATE TABLE cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_id INT NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        title VARCHAR(100) NOT NULL,
        slug VARCHAR(150) UNIQUE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('active', 'draft', 'sold') DEFAULT 'active',
        featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE
    )");

    // Crear tabla cars (renovada)
    $pdo->exec("DROP TABLE IF EXISTS cars");
    $pdo->exec("CREATE TABLE cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_id INT NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        title VARCHAR(100) NOT NULL,
        slug VARCHAR(150) UNIQUE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('active', 'draft', 'sold') DEFAULT 'active',
        featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE
    )");

    // Crear tabla spec_fields
    $pdo->exec("CREATE TABLE IF NOT EXISTS spec_fields (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        tipo ENUM('text', 'number', 'select', 'color') DEFAULT 'text',
        opciones JSON NULL,
        obligatorio BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Crear tabla car_specs
    $pdo->exec("CREATE TABLE IF NOT EXISTS car_specs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        spec_field_id INT NULL,
        etiqueta VARCHAR(100),
        valor TEXT NOT NULL,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        FOREIGN KEY (spec_field_id) REFERENCES spec_fields(id) ON DELETE SET NULL
    )");

    // Crear tabla car_images
    $pdo->exec("CREATE TABLE IF NOT EXISTS car_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    )");

    // Crear tabla car_videos
    $pdo->exec("CREATE TABLE IF NOT EXISTS car_videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        url VARCHAR(255) NOT NULL,
        titulo VARCHAR(200),
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    )");

    // Crear tabla car_components
    $pdo->exec("CREATE TABLE IF NOT EXISTS car_components (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NULL,
        component_type VARCHAR(50) NOT NULL,
        config JSON NULL,
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
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

    // Insertar campos de especificación por defecto si está vacío
    $stmt = $pdo->query("SELECT COUNT(*) FROM spec_fields");
    if ($stmt->fetchColumn() == 0) {
        $default_specs = [
            ['Marca', 'marca', 'select', '["Toyota", "Chevrolet"]', true, 1],
            ['Modelo', 'modelo', 'text', null, true, 2],
            ['Año', 'anio', 'number', null, true, 3],
            ['Color', 'color', 'text', null, true, 4],
            ['Transmisión', 'transmision', 'select', '["Automática", "Manual", "CVT"]', true, 5],
            ['Combustible', 'combustible', 'select', '["Gasolina", "Diésel", "Híbrido", "Eléctrico"]', true, 6],
            ['Kilometraje', 'kilometraje', 'number', null, false, 7],
            ['Motor', 'motor', 'text', null, false, 8],
            ['Tracción', 'traccion', 'select', '["Delantera", "Trasera", "4x4", "AWD"]', false, 9],
            ['Puertas', 'puertas', 'number', null, false, 10],
            ['Cilindrada', 'cilindrada', 'text', null, false, 11],
        ];
        $insert = $pdo->prepare("INSERT INTO spec_fields (nombre, slug, tipo, opciones, obligatorio, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_specs as $sp) {
            $insert->execute($sp);
        }
    }

    // Insertar componentes por defecto (template global) si está vacío
    $stmt = $pdo->query("SELECT COUNT(*) FROM car_components WHERE car_id IS NULL");
    if ($stmt->fetchColumn() == 0) {
        $default_components = [
            ['hero_slider', '{"show_title": true, "show_price": true}', true, 1],
            ['specs_destacadas', '{"max_items": 6}', true, 2],
            ['descripcion', '{}', true, 3],
            ['exterior_interior', '{"exterior_title":"Exterior","exterior_description":"","exterior_image":"","interior_title":"Interior","interior_description":"","interior_image":""}', true, 4],
            ['image_gallery', '{"layout": "grid"}', true, 5],
            ['specs_tabla', '{}', true, 6],
            ['video', '{}', true, 7],
            ['cta_whatsapp', '{}', true, 8],
            ['calculadora', '{}', true, 9],
            ['autos_relacionados', '{"max_items": 4}', true, 10],
            ['custom_html', '{"html":"","css":"","js":"","images":[]}', false, 11],
        ];
        $insert = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) VALUES (NULL, ?, ?, ?, ?)");
        foreach ($default_components as $comp) {
            $insert->execute($comp);
        }
    }

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Base de datos y tablas creadas exitosamente.\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
