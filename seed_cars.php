<?php
require_once __DIR__ . '/api/db_connect.php';

$carros = [
    [
        'title' => 'BMW X5 xDrive40i 2024',
        'price' => 89990.00,
        'description' => 'La BMW X5 2024 combina lujo y rendimiento con su motor de 6 cilindros en línea de 3.0 litros twin-turbo, tracción integral xDrive y una cabina premium con pantalla curva.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'X5 xDrive40i', 'anio' => '2024', 'color' => 'Blanco Alpino', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '3.0L Twin-Turbo I6', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => '2998 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Mercedes-Benz Clase E 300 2024',
        'price' => 75950.00,
        'description' => 'El Mercedes-Benz Clase E 300 2024 redefine la elegancia con su diseño sofisticado, tecnología de iluminación digital y un interior envuelto en cuero Nappa.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'E 300', 'anio' => '2024', 'color' => 'Negro Ónix', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '2.0L Turbo I4', 'traccion' => 'Trasera', 'puertas' => '4', 'cilindrada' => '1999 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'video', 'cta_whatsapp']
    ],
    [
        'title' => 'Audi Q7 55 TFSI 2024',
        'price' => 82450.00,
        'description' => 'Audi Q7 2024 con motor V6 3.0 TFSI, tracción quattro, suspensión neumática adaptativa y pantalla táctil MMI de doble panel.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Q7 55 TFSI', 'anio' => '2024', 'color' => 'Azul Navarra', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '3.0L V6 TFSI', 'traccion' => 'quattro AWD', 'puertas' => '5', 'cilindrada' => '2995 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'image_gallery', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Porsche Cayenne Turbo GT 2024',
        'price' => 198950.00,
        'description' => 'El Porsche Cayenne Turbo GT es un SUV de alto rendimiento con motor V8 biturbo de 4.0 litros, 650 hp y un chasis deportivo diseñado para la pista.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Cayenne Turbo GT', 'anio' => '2024', 'color' => 'Rojo Carmín', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '4.0L V8 Biturbo', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => '3996 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'cta_whatsapp']
    ],
    [
        'title' => 'Lexus RX 350h 2024',
        'price' => 58350.00,
        'description' => 'Lexus RX 350h híbrido con eficiencia excepcional, diseño coupé y la lujosa artesanía japonesa Takumi. El SUV premium más confiable.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'RX 350h', 'anio' => '2024', 'color' => 'Plateado Nórdico', 'transmision' => 'CVT', 'combustible' => 'Híbrido', 'kilometraje' => '0', 'motor' => '2.5L I4 Híbrido', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => '2487 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'cta_whatsapp', 'calculadora']
    ],
    [
        'title' => 'Tesla Model S Plaid 2024',
        'price' => 129990.00,
        'description' => 'Tesla Model S Plaid: 1,020 hp, aceleración de 0-60 mph en 1.99 segundos, autonomía de 396 millas y la pantalla giratoria de 17 pulgadas.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Model S Plaid', 'anio' => '2024', 'color' => 'Rojo Ultra', 'transmision' => 'Automática', 'combustible' => 'Eléctrico', 'kilometraje' => '0', 'motor' => 'Tri Motor Eléctrico', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => 'N/A'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Range Rover Sport SE 2024',
        'price' => 104500.00,
        'description' => 'Range Rover Sport 2024 con motor 3.0L I6 MHEV, capacidad todoterreno superior, suspensión adaptativa y un interior de lujo con cuero Windsor.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Sport SE', 'anio' => '2024', 'color' => 'Verde Santorini', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '3.0L I6 MHEV', 'traccion' => '4x4', 'puertas' => '5', 'cilindrada' => '2996 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'image_gallery', 'specs_tabla', 'cta_whatsapp', 'autos_relacionados']
    ],
    [
        'title' => 'Ford Mustang Dark Horse 2024',
        'price' => 66890.00,
        'description' => 'El Ford Mustang Dark Horse 2024 es la variante más potente y lista para pista con motor V8 5.0L Coyote, 500 hp y un chasis reforzado.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'Mustang Dark Horse', 'anio' => '2024', 'color' => 'Azul Emberglo', 'transmision' => 'Manual', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '5.0L V8 Coyote', 'traccion' => 'Trasera', 'puertas' => '2', 'cilindrada' => '5038 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora']
    ],
    [
        'title' => 'Chevrolet Corvette Stingray 2024',
        'price' => 112800.00,
        'description' => 'Corvette Stingray 2024 con motor V8 LT2 de 6.2L montado en posición central, 495 hp y un diseño que desafía a los superdeportivos europeos.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Corvette Stingray', 'anio' => '2024', 'color' => 'Naranja Torch', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '6.2L V8 LT2', 'traccion' => 'Trasera', 'puertas' => '2', 'cilindrada' => '6162 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'image_gallery', 'specs_tabla', 'descripcion', 'cta_whatsapp', 'autos_relacionados']
    ],
    [
        'title' => 'Toyota Land Cruiser 2024',
        'price' => 57450.00,
        'description' => 'La nueva Toyota Land Cruiser 2024 regresa con su legendaria durabilidad, motor i-FORCE MAX híbrido turbo de 4 cilindros y capacidades todoterreno inigualables.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'Land Cruiser 1958', 'anio' => '2024', 'color' => 'Verde Herencia', 'transmision' => 'Automática', 'combustible' => 'Híbrido', 'kilometraje' => '0', 'motor' => '2.4L Turbo Híbrido', 'traccion' => '4x4', 'puertas' => '5', 'cilindrada' => '2393 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'cta_whatsapp']
    ],
    [
        'title' => 'Honda Civic Type R 2024',
        'price' => 46890.00,
        'description' => 'Honda Civic Type R 2024 con motor 2.0L VTEC Turbo de 315 hp, transmisión manual de 6 velocidades y el mejor tiempo de tracción delantera en Nürburgring.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'Civic Type R', 'anio' => '2024', 'color' => 'Blanco Championship', 'transmision' => 'Manual', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '2.0L VTEC Turbo', 'traccion' => 'Delantera', 'puertas' => '5', 'cilindrada' => '1996 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Volvo XC90 Recharge 2024',
        'price' => 79950.00,
        'description' => 'Volvo XC90 Recharge: SUV híbrido enchufable con motor 2.0L turbo + eléctrico, 455 hp combinados, 7 asientos y la galardonada seguridad Volvo.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'XC90 Recharge', 'anio' => '2024', 'color' => 'Gris Cristal', 'transmision' => 'Automática', 'combustible' => 'Híbrido', 'kilometraje' => '0', 'motor' => '2.0L T8 Twin Engine', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => '1969 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'cta_whatsapp', 'calculadora']
    ],
    [
        'title' => 'Lamborghini Urus SE 2024',
        'price' => 278900.00,
        'description' => 'Lamborghini Urus SE 2024 híbrido enchufable con motor V8 4.0L biturbo + motor eléctrico, 789 hp combinados y un diseño agresivo inconfundible.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Urus SE', 'anio' => '2024', 'color' => 'Amarillo Giallo', 'transmision' => 'Automática', 'combustible' => 'Híbrido', 'kilometraje' => '0', 'motor' => '4.0L V8 Biturbo PHEV', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => '3996 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'video', 'cta_whatsapp']
    ],
    [
        'title' => 'Jeep Wrangler Rubicon 392 2024',
        'price' => 94990.00,
        'description' => 'Jeep Wrangler Rubicon 392 con el poderoso motor HEMI V8 6.4L de 470 hp, ejes Dana 44, bloqueo de diferenciales y la mejor capacidad todoterreno.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'Wrangler Rubicon 392', 'anio' => '2024', 'color' => 'Negro Ébano', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '6.4L HEMI V8', 'traccion' => '4x4', 'puertas' => '4', 'cilindrada' => '6417 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'cta_whatsapp']
    ],
    [
        'title' => 'Nissan GT-R NISMO 2024',
        'price' => 242890.00,
        'description' => 'Nissan GT-R NISMO 2024: el legendario Godzilla con motor VR38DETT V6 biturbo de 3.8L, 600 hp, tracción integral ATTESA E-TS y aerodinámica NISMO.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'GT-R NISMO', 'anio' => '2024', 'color' => 'Gris Bayside', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '3.8L V6 Biturbo', 'traccion' => 'AWD', 'puertas' => '2', 'cilindrada' => '3799 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Mazda CX-90 Turbo S 2024',
        'price' => 54950.00,
        'description' => 'Mazda CX-90 Turbo S 2024 con motor 3.3L I6 Turbo de 340 hp, tracción integral i-Activ AWD y el galardonado diseño Kodo en un SUV de 3 filas.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'CX-90 Turbo S', 'anio' => '2024', 'color' => 'Rojo Artisan', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '3.3L I6 Turbo', 'traccion' => 'AWD', 'puertas' => '5', 'cilindrada' => '3283 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'specs_tabla', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Rolls-Royce Ghost Series II 2024',
        'price' => 439000.00,
        'description' => 'Rolls-Royce Ghost Series II 2024: la cúspide del lujo automotriz con motor V12 6.75L biturbo, 563 hp, suspensión Magic Carpet Ride y el interior más silencioso del mundo.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Ghost Series II', 'anio' => '2024', 'color' => 'Plata Diamante', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '6.75L V12 Biturbo', 'traccion' => 'Trasera', 'puertas' => '4', 'cilindrada' => '6749 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'image_gallery', 'cta_whatsapp']
    ],
    [
        'title' => 'Hyundai Ioniq 6 Limited 2024',
        'price' => 48350.00,
        'description' => 'Hyundai Ioniq 6 Limited 2024 con autonomía de 361 millas, carga ultrarrápida 800V, diseño aerodinámico tipo streamliner y galardonado como Auto del Año.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => 'Ioniq 6 Limited', 'anio' => '2024', 'color' => 'Verde Digital', 'transmision' => 'Automática', 'combustible' => 'Eléctrico', 'kilometraje' => '0', 'motor' => 'Motor Eléctrico Dual', 'traccion' => 'AWD', 'puertas' => '4', 'cilindrada' => 'N/A'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
    [
        'title' => 'Ram 1500 TRX Final Edition 2024',
        'price' => 127890.00,
        'description' => 'Ram 1500 TRX Final Edition 2024 con motor V8 6.2L Supercharged Hellcat de 702 hp, suspensión de carrera y la camioneta más potente jamás fabricada.',
        'status' => 'active',
        'featured' => false,
        'specs' => ['modelo' => '1500 TRX Final Edition', 'anio' => '2024', 'color' => 'Anaranjado Havoc', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '6.2L V8 Supercharged Hellcat', 'traccion' => '4x4', 'puertas' => '4', 'cilindrada' => '6166 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'specs_tabla', 'descripcion', 'image_gallery', 'cta_whatsapp']
    ],
    [
        'title' => 'Bentley Continental GT Speed 2024',
        'price' => 315900.00,
        'description' => 'Bentley Continental GT Speed 2024 con motor W12 6.0L biturbo de 659 hp, tracción integral, chasis con vectorización de par y el lujo artesanal de Crewe.',
        'status' => 'active',
        'featured' => true,
        'specs' => ['modelo' => 'Continental GT Speed', 'anio' => '2024', 'color' => 'Veré Mantilla', 'transmision' => 'Automática', 'combustible' => 'Gasolina', 'kilometraje' => '0', 'motor' => '6.0L W12 Biturbo', 'traccion' => 'AWD', 'puertas' => '2', 'cilindrada' => '5998 cc'],
        'comps' => ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados']
    ],
];

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[\x{00f1}\x{00d1}]/u', 'n', $text);
    $text = preg_replace('/[áàäâã]/u', 'a', $text);
    $text = preg_replace('/[éèëê]/u', 'e', $text);
    $text = preg_replace('/[íìïî]/u', 'i', $text);
    $text = preg_replace('/[óòöôõ]/u', 'o', $text);
    $text = preg_replace('/[úùüûũ]/u', 'u', $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

$spec_field_map = [];
$stmt = $pdo->query("SELECT id, slug FROM spec_fields");
while ($row = $stmt->fetch()) {
    $spec_field_map[$row['slug']] = $row['id'];
}

$img_index = 0;
$upload_dir = __DIR__ . '/uploads/';

foreach ($carros as $index => $carro) {
    $slug = slugify($carro['title']);
    $slug_orig = $slug;
    $counter = 1;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE slug = ?");
    $stmt->execute([$slug]);
    while ($stmt->fetchColumn() > 0) {
        $slug = $slug_orig . '-' . $counter++;
        $stmt->execute([$slug]);
    }

    $primary_img = 'uploads/placeholder_car.jpg';
    $img_url = 'https://picsum.photos/seed/' . slugify($carro['title']) . '/800/600';

    $stmt = $pdo->prepare("INSERT INTO cars (title, slug, price, image_path, description, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$carro['title'], $slug, $carro['price'], $primary_img, $carro['description'], $carro['status'], $carro['featured'] ? 1 : 0]);
    $car_id = $pdo->lastInsertId();

    $img_binary = @file_get_contents($img_url);
    if ($img_binary !== false) {
        $img_filename = 'car_' . $car_id . '_primary_' . time() . '.jpg';
        file_put_contents($upload_dir . $img_filename, $img_binary);
        $primary_img = 'uploads/' . $img_filename;
        $pdo->prepare("UPDATE cars SET image_path = ? WHERE id = ?")->execute([$primary_img, $car_id]);
    }

    for ($gi = 0; $gi < 4; $gi++) {
        $gallery_url = 'https://picsum.photos/seed/' . slugify($carro['title']) . '-' . $gi . '/800/600';
        $gimg_binary = @file_get_contents($gallery_url);
        if ($gimg_binary !== false) {
            $gimg_filename = 'car_' . $car_id . '_gallery_' . $gi . '_' . time() . '.jpg';
            file_put_contents($upload_dir . $gimg_filename, $gimg_binary);
            $is_primary = ($gi === 0) ? 1 : 0;
            $pdo->prepare("INSERT INTO car_images (car_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)")->execute([$car_id, 'uploads/' . $gimg_filename, $is_primary, $gi]);
        }
    }

    $sort = 1;
    foreach ($carro['specs'] as $slug_spec => $valor) {
        $field_id = $spec_field_map[$slug_spec] ?? null;
        if ($field_id) {
            $pdo->prepare("INSERT INTO car_specs (car_id, spec_field_id, valor, sort_order) VALUES (?, ?, ?, ?)")->execute([$car_id, $field_id, $valor, $sort++]);
        }
    }

    $pdo->prepare("UPDATE cars SET image_path = ? WHERE id = ?")->execute([$primary_img, $car_id]);

    $comp_pos = 1;
    $all_comps = ['hero_slider', 'specs_destacadas', 'descripcion', 'image_gallery', 'specs_tabla', 'video', 'cta_whatsapp', 'calculadora', 'autos_relacionados'];
    $configs = [
        'hero_slider' => '{"show_title":true,"show_price":true}',
        'specs_destacadas' => '{"max_items":6}',
        'descripcion' => '{}',
        'image_gallery' => '{"layout":"grid"}',
        'specs_tabla' => '{}',
        'video' => '{}',
        'cta_whatsapp' => '{}',
        'calculadora' => '{}',
        'autos_relacionados' => '{"max_items":4}',
    ];

    foreach ($all_comps as $comp) {
        $is_active = in_array($comp, $carro['comps']) ? 1 : 0;
        $config = $configs[$comp] ?? '{}';
        $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$car_id, $comp, $config, $is_active, $comp_pos++]);
    }

    echo "✓ Creado: {$carro['title']} (ID: $car_id, Slug: $slug)\n";
}

echo "\n✅ 20 autos de prueba creados exitosamente.\n";
