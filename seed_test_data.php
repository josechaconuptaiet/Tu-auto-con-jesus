<?php
require_once 'api/db_connect.php';

echo "Sembrando datos de prueba en la base de datos...\n";

try {
    // 1. Limpiar datos viejos si se desea, o simplemente insertar nuevos.
    // Vamos a limpiar citas previas de test para no acumular infinitamente, pero conservar el resto.
    $pdo->exec("DELETE FROM appointments WHERE first_name LIKE 'TestUser%'");
    $pdo->exec("DELETE FROM cars WHERE title LIKE 'TestCar%'");
    
    // 2. Sembrar Autos
    echo "Sembrando 25 Autos de prueba...\n";
    $car_brands = ['Audi', 'BMW', 'Mercedes-Benz', 'Porsche', 'Ferrari', 'Lamborghini', 'Tesla', 'Lexus', 'Jaguar', 'Land Rover'];
    $car_models = ['E-Tron', 'M4 Competition', 'AMG GT', '911 Carrera', '488 Pista', 'Huracan', 'Model S Plaid', 'LC 500', 'F-Type', 'Range Rover Sport'];
    
    for ($i = 1; $i <= 25; $i++) {
        $brand = $car_brands[array_rand($car_brands)];
        $model = $car_models[array_rand($car_models)];
        $title = "TestCar " . $brand . " " . $model . " " . rand(2021, 2026);
        $price = rand(45000, 250000);
        $image_path = "uploads/placeholder_car.jpg"; // Placeholder premium
        $description = "Coche de prueba premium " . $brand . " con alto equipamiento, rines de aleación, transmisión automática, asientos de piel y solo " . rand(5000, 35000) . " km.";
        
        $stmt = $pdo->prepare("INSERT INTO cars (title, price, image_path, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $price, $image_path, $description]);
    }

    // 3. Sembrar Citas
    echo "Sembrando 60 Citas de prueba...\n";
    $first_names = ['Juan', 'Pedro', 'Maria', 'Jose', 'Ana', 'Luis', 'Sofia', 'Carlos', 'Laura', 'Diego', 'Lucia', 'Miguel', 'Elena', 'David', 'Carmen'];
    $last_names = ['Gonzalez', 'Rodriguez', 'Gomez', 'Fernandez', 'Lopez', 'Diaz', 'Martinez', 'Perez', 'Sanchez', 'Romero', 'Torres', 'Ruiz', 'Ramirez', 'Flores'];
    $statuses = ['pendiente', 'confirmada', 'cancelada'];
    $hours = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '14:00:00', '15:00:00', '16:00:00'];
    
    $used_slots = [];
    
    // Obtener los que ya están en la base de datos para no colisionar con citas existentes
    $existing = $pdo->query("SELECT appointment_date, appointment_time FROM appointments")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($existing as $e) {
        $used_slots[$e['appointment_date'] . '_' . $e['appointment_time']] = true;
    }
    
    $inserted_count = 0;
    $attempts = 0;
    while ($inserted_count < 60 && $attempts < 500) {
        $attempts++;
        $first = "TestUser_" . $first_names[array_rand($first_names)];
        $last = $last_names[array_rand($last_names)] . "_" . rand(10, 99);
        $phone = "+52" . rand(1000000000, 9999999999);
        $status = $statuses[array_rand($statuses)];
        
        $day_diff = rand(-15, 15);
        $date = date('Y-m-d', strtotime("$day_diff days"));
        $time = $hours[array_rand($hours)];
        
        $slot_key = $date . '_' . $time;
        if (isset($used_slots[$slot_key])) {
            continue;
        }
        
        $used_slots[$slot_key] = true;
        
        $stmt = $pdo->prepare("INSERT INTO appointments (first_name, last_name, phone, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first, $last, $phone, $date, $time, $status]);
        $inserted_count++;
    }
    
    echo "Se sembraron $inserted_count citas con éxito tras $attempts intentos.\n";

    echo "¡Sembrado de datos completado exitosamente!\n";
} catch (Exception $e) {
    echo "Error sembrando datos: " . $e->getMessage() . "\n";
}
