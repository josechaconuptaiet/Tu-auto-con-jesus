<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$dbname = 'dealership_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truncar la tabla de autos para poblarla de cero
    $pdo->exec("TRUNCATE TABLE cars");

    $brands = ['Toyota', 'Honda', 'Nissan', 'Mazda', 'Ford', 'Chevrolet', 'Jeep', 'BMW', 'Mercedes-Benz', 'Audi', 'Lexus', 'Hyundai', 'Kia', 'Subaru', 'Porsche', 'Tesla'];
    $models = [
        'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Tacoma', 'Tundra', 'Prius', 'Highlander', 'Supra'],
        'Honda' => ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'HR-V', 'Ridgeline'],
        'Nissan' => ['Sentra', 'Altima', 'Rogue', 'Frontier', 'Pathfinder', '370Z', 'GT-R'],
        'Mazda' => ['Mazda3', 'Mazda6', 'CX-5', 'CX-9', 'MX-5 Miata'],
        'Ford' => ['F-150', 'Mustang', 'Explorer', 'Escape', 'Edge', 'Bronco', 'Focus'],
        'Chevrolet' => ['Silverado', 'Camaro', 'Corvette', 'Equinox', 'Tahoe', 'Suburban', 'Malibu'],
        'Jeep' => ['Wrangler', 'Grand Cherokee', 'Cherokee', 'Compass', 'Gladiator'],
        'BMW' => ['3 Series', '5 Series', '7 Series', 'X3', 'X5', 'M3', 'M5', 'i8'],
        'Mercedes-Benz' => ['C-Class', 'E-Class', 'S-Class', 'GLC', 'GLE', 'AMG GT', 'CLA'],
        'Audi' => ['A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7', 'R8', 'TT'],
        'Lexus' => ['IS', 'ES', 'RX', 'NX', 'GX', 'LC500'],
        'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade', 'Kona'],
        'Kia' => ['Forte', 'Optima', 'Sportage', 'Sorento', 'Telluride', 'Stinger'],
        'Subaru' => ['Impreza', 'Legacy', 'Outback', 'Forester', 'WRX', 'BRZ'],
        'Porsche' => ['911 Carrera', 'Cayenne', 'Macan', 'Panamera', 'Boxster', 'Taycan'],
        'Tesla' => ['Model 3', 'Model S', 'Model X', 'Model Y']
    ];

    $descriptions = [
        "Excelente estado, único dueño, mantenimientos al día en agencia. Listo para transferir.",
        "Equipamiento premium, interiores de cuero, techo panorámico, sistema de sonido de alta fidelidad.",
        "Super económico de combustible, ideal para el uso diario en la ciudad. Aire acondicionado helando.",
        "Deportivo impresionante, motor potente, rines especiales, transmisión automática con paletas al volante.",
        "Versión full extras, tracción 4x4 inteligente, ideal para aventuras familiares todo terreno.",
        "Híbrido de alta eficiencia, tecnología moderna, pantalla táctil con Apple CarPlay y Android Auto.",
        "Edición de lujo, asientos con calefacción, sensores de punto ciego y cámara de retroceso 360.",
        "Poco kilometraje original, guardado siempre bajo techo, huele a nuevo. ¡Oportunidad única!"
    ];

    $stmt = $pdo->prepare("INSERT INTO cars (title, price, image_path, description) VALUES (?, ?, ?, ?)");

    for ($i = 1; $i <= 100; $i++) {
        $brand = $brands[array_rand($brands)];
        $model = $models[$brand][array_rand($models[$brand])];
        $year = rand(2015, 2024);
        
        $title = "$brand $model $year";
        
        // Asignar un precio coherente al año y la marca
        if (in_array($brand, ['Porsche', 'Tesla', 'BMW', 'Mercedes-Benz', 'Audi', 'Lexus'])) {
            $price = rand(35000, 120000);
        } else {
            $price = rand(12000, 38000);
        }

        // Loremflickr con un lock para que la imagen sea consistente por cada ID
        $image_path = "https://loremflickr.com/800/600/car,luxury?lock=" . $i;
        
        $description = $descriptions[array_rand($descriptions)];

        $stmt->execute([$title, $price, $image_path, $description]);
    }

    echo "Se han insertado 100 autos con imágenes estables y reales exitosamente.\n";
} catch (PDOException $e) {
    die("Error al poblar la base de datos: " . $e->getMessage() . "\n");
}
?>
