<?php
require_once __DIR__ . '/api/db_connect.php';

echo "=== SEMBRANDO DATOS DE VEHÍCULOS ===\n\n";

// 1. Limpiar datos existentes
echo "Limpiando datos existentes...\n";
$pdo->exec("DELETE FROM car_components WHERE car_id IS NOT NULL");
$pdo->exec("DELETE FROM car_videos WHERE car_id IS NOT NULL");
$pdo->exec("DELETE FROM car_images WHERE car_id IS NOT NULL");
$pdo->exec("DELETE FROM car_specs WHERE car_id IS NOT NULL");
$pdo->exec("DELETE FROM cars");
echo "✓ Datos limpiados.\n\n";

// 2. Crear marcas
echo "Creando marcas...\n";
$pdo->exec("DELETE FROM marcas");
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('Toyota', 'toyota')");
$toyota_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('Chevrolet', 'chevrolet')");
$chevy_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('Honda', 'honda')");
$honda_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('RAM', 'ram')");
$ram_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('GMC', 'gmc')");
$gmc_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('Jeep', 'jeep')");
$jeep_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('Mazda', 'mazda')");
$mazda_id = $pdo->lastInsertId();
$pdo->exec("INSERT INTO marcas (nombre, slug) VALUES ('Buick', 'buick')");
$buick_id = $pdo->lastInsertId();
echo "✓ Marcas creadas.\n\n";

// 3. Obtener spec_fields
$spec_field_map = [];
$stmt = $pdo->query("SELECT id, slug FROM spec_fields");
while ($row = $stmt->fetch()) {
    $spec_field_map[$row['slug']] = $row['id'];
}

// 4. Definir todos los vehículos con precios reales (MSRP 2026 aprox.)
$vehiculos = [
    // TOYOTA
    ['marca_id' => $toyota_id, 'modelo' => 'RAV4', 'variante' => 'LE Hybrid', 'price' => 31725, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La RAV4 Hybrid LE 2026 combina eficiencia y versatilidad con su sistema híbrido de cuarta generación, tracción integral electrónica y el espacio interior más amplio de su clase.'],
    ['marca_id' => $toyota_id, 'modelo' => 'RAV4', 'variante' => 'XLE Hybrid', 'price' => 34000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La RAV4 Hybrid XLE añade llantas de aleación de 18", asiento del conductor eléctrico, techo corredizo y sistema de audio premium.'],
    ['marca_id' => $toyota_id, 'modelo' => 'RAV4', 'variante' => 'XLE Premium Hybrid', 'price' => 37000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La XLE Premium Hybrid incluye techo panorámico, asientos de SofTex, portón eléctrico y sensores de estacionamiento.'],
    ['marca_id' => $toyota_id, 'modelo' => 'RAV4', 'variante' => 'Limited Hybrid', 'price' => 40000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La tope de gama RAV4 Hybrid Limited con JBL audio, head-up display, techo panorámico fijo y asientos calefactados/ventilados.'],
    ['marca_id' => $toyota_id, 'modelo' => 'RAV4', 'variante' => 'XSE Hybrid', 'price' => 38000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La RAV4 Hybrid XSE con suspensión deportiva, parrilla exclusiva, llantas de 19" y interior con costuras contrastantes.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Camry', 'variante' => 'LE Hybrid', 'price' => 28500, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Camry Hybrid LE 2026 ofrece eficiencia excepcional con 51 mpg combinados, Toyota Safety Sense 3.0 y pantalla táctil de 8".'],
    ['marca_id' => $toyota_id, 'modelo' => 'Camry', 'variante' => 'SE Hybrid', 'price' => 31000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Camry Hybrid SE con suspensión deportiva, parrilla de malla negra, llantas de 18" y asientos deportivos.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Camry', 'variante' => 'XLE Hybrid', 'price' => 34000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Camry Hybrid XLE con asientos de cuero, techo corredizo, audio JBL y monitoreo de punto ciego.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Camry', 'variante' => 'XSE Hybrid', 'price' => 35500, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Camry Hybrid XSE combina lujo y deportividad con suspensión afinada, escape dual y asientos SofTex con costuras rojas.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Corolla', 'variante' => 'LE', 'price' => 22500, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Corolla LE 2026 es el sedán compacto más vendido del mundo, con Toyota Safety Sense 3.0 y 32 mpg combinados.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla', 'variante' => 'SE', 'price' => 25000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Corolla SE con suspensión deportiva, llantas de 18", parrilla deportiva y asientos con soporte lateral mejorado.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla', 'variante' => 'XSE', 'price' => 28000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Corolla XSE tope de gama con asientos de SofTex, audio JBL, head-up display y techo corredizo.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla', 'variante' => 'Hybrid LE', 'price' => 23500, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '1.8L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Corolla Hybrid LE ofrece 52 mpg combinados, el mejor rendimiento de su clase con tecnología híbrida probada.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla', 'variante' => 'Hybrid SE', 'price' => 26000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '1.8L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Corolla Hybrid SE combina eficiencia híbrida con estilo deportivo y suspensión afinada.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Tacoma', 'variante' => 'SR', 'price' => 32000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tacoma SR 2026 es la pickup mediana más capaz con motor turbo de 2.4L, chasis reforzado y Toyota Safety Sense.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tacoma', 'variante' => 'SR5', 'price' => 36000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tacoma SR5 añade pantalla táctil de 8", llantas de aleación de 17" y control de crucero adaptativo.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tacoma', 'variante' => 'TRD Sport', 'price' => 39000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tacoma TRD Sport con suspensión deportiva, capó con toma de aire, llantas de 18" y diferencial trasero locking.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tacoma', 'variante' => 'TRD Off-Road', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tacoma TRD Off-Road con modo Crawl Control, Multi-Terrain Select, skid plates y neumáticos todo terreno.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tacoma', 'variante' => 'Limited', 'price' => 45000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tacoma Limited con asientos de cuero calefactados, audio JBL, techo corredizo y monitoreo 360°.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tacoma', 'variante' => 'TRD Pro', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4 Hybrid', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tacoma TRD Pro con suspensión FOX, i-FORCE MAX hybrid, skid plates TRD y neumáticos BFGoodrich All-Terrain.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Corolla Cross', 'variante' => 'L', 'price' => 24000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Corolla Cross L 2026 es el SUV compacto más accesible de Toyota con Toyota Safety Sense 3.0.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla Cross', 'variante' => 'LE', 'price' => 26000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Corolla Cross LE con tracción integral, pantalla de 8", llantas de 17" y control de clima dual.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla Cross', 'variante' => 'XLE', 'price' => 29000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Corolla Cross XLE con techo panorámico, asientos de SofTex, audio premium y portón eléctrico.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla Cross', 'variante' => 'SE Hybrid', 'price' => 28000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.0L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Corolla Cross SE Hybrid con 42 mpg combinados, suspensión deportiva y estilo agresivo.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Corolla Cross', 'variante' => 'XSE Hybrid', 'price' => 31000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.0L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Corolla Cross XSE Hybrid tope de gama con llantas de 19", asientos SofTex y audio JBL.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Highlander', 'variante' => 'XLE', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Highlander XLE 2026 es el SUV familiar de 3 filas con motor turbo de 275 hp, 8 pasajeros y Toyota Safety Sense.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Highlander', 'variante' => 'XSE', 'price' => 45000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Highlander XSE con suspensión deportiva, parrilla exclusiva, llantas de 21" y asientos SofTex.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Highlander', 'variante' => 'Limited', 'price' => 49000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Highlander Limited con asientos de cuero, audio JBL de 11 parlantes, head-up display y techo panorámico.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Highlander', 'variante' => 'Hybrid XLE', 'price' => 44000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Highlander Hybrid XLE con 35 mpg combinados, 243 hp combinados y tracción integral electrónica.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Grand Highlander', 'variante' => 'XLE', 'price' => 44000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Highlander XLE 2026 ofrece espacio de 3 filas más amplio que el Highlander estándar con 8 pasajeros.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Grand Highlander', 'variante' => 'Limited', 'price' => 50000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Highlander Limited con asientos de cuero, audio JBL, techo panorámico y portón eléctrico.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Grand Highlander', 'variante' => 'XLE Hybrid', 'price' => 46000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Highlander Hybrid XLE con 35 mpg combinados y 243 hp para máxima eficiencia familiar.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Grand Highlander', 'variante' => 'Limited Hybrid', 'price' => 53000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Highlander Hybrid Limited con lujo, eficiencia y espacio para toda la familia.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Grand Highlander', 'variante' => 'MAX Limited Hybrid', 'price' => 60000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '2.4L Turbo Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Highlander MAX con i-FORCE MAX hybrid de 362 hp, la versión más potente y lujosa.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Sienna', 'variante' => 'LE', 'price' => 38000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sienna LE 2026 es la única minivan 100% híbrida con 36 mpg combinados y 8 pasajeros.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sienna', 'variante' => 'XLE', 'price' => 43000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sienna XLE con asientos captain, portón eléctrico, pantalla de 12" y tracción integral disponible.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sienna', 'variante' => 'XSE', 'price' => 46000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sienna XSE con suspensión deportiva, parrilla exclusiva, llantas de 20" y asientos SofTex.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sienna', 'variante' => 'Limited', 'price' => 50000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sienna Limited con asientos de cuero calefactados/ventilados, audio JBL de 12 parlantes y techo panorámico.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sienna', 'variante' => 'Platinum', 'price' => 53000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sienna Platinum tope de gama con asientos de cuero premium, head-up display, cámaras 360° y Digital Rearview Mirror.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Tundra', 'variante' => 'SR5', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tundra SR5 2026 con motor V6 twin-turbo de 389 hp, chasis de acero de alta resistencia y capacidad de remolque de 12,000 lbs.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tundra', 'variante' => 'Limited', 'price' => 50000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Tundra Limited con pantalla de 14", asientos SofTex calefactados, audio JBL y bed liner.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tundra', 'variante' => 'TRD Pro', 'price' => 72000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2027', 'puertas' => '4', 'desc' => 'La Tundra TRD Pro con i-FORCE MAX hybrid de 437 hp, suspensión FOX, skid plates y neumáticos BFGoodrich.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tundra', 'variante' => 'Platinum', 'price' => 62000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'anio' => '2027', 'puertas' => '4', 'desc' => 'La Tundra Platinum con asientos de cuero ventilados, audio JBL de 14 parlantes, techo panorámico y portón eléctrico.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tundra', 'variante' => '1794 Edition', 'price' => 65000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'anio' => '2027', 'puertas' => '4', 'desc' => 'La Tundra 1794 Edition con cuero saddle tan, madera de nogal, detalles premium y estilo rancho de lujo.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Tundra', 'variante' => 'Capstone', 'price' => 80000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2027', 'puertas' => '4', 'desc' => 'La Tundra Capstone es la más lujosa con cuero semi-anilina, madera genuina, i-FORCE MAX y acabados premium.'],

    ['marca_id' => $toyota_id, 'modelo' => '4Runner', 'variante' => 'SR5', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La 4Runner SR5 2026 completamente rediseñada con motor turbo de 2.4L, chasis de cuerpo sobre marco y capacidad todoterreno legendaria.'],
    ['marca_id' => $toyota_id, 'modelo' => '4Runner', 'variante' => 'TRD Off-Road', 'price' => 47000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La 4Runner TRD Off-Road con Crawl Control, Multi-Terrain Select, locking rear differential y skid plates.'],
    ['marca_id' => $toyota_id, 'modelo' => '4Runner', 'variante' => 'TRD Off-Road Premium', 'price' => 50000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La 4Runner TRD Off-Road Premium con asientos SofTex, audio JBL, portón eléctrico y KDSS.'],
    ['marca_id' => $toyota_id, 'modelo' => '4Runner', 'variante' => 'Limited', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La 4Runner Limited con asientos de cuero, audio JBL premium, techo corredizo y sistema de navegación.'],
    ['marca_id' => $toyota_id, 'modelo' => '4Runner', 'variante' => 'TRD Pro', 'price' => 55000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.4L Turbo I4 Hybrid', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La 4Runner TRD Pro con i-FORCE MAX hybrid, suspensión FOX, skid plates TRD y neumáticos todo terreno.'],

    ['marca_id' => $toyota_id, 'modelo' => 'Sequoia', 'variante' => 'SR5 Hybrid', 'price' => 62000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sequoia SR5 2026 con i-FORCE MAX hybrid de 437 hp, tracción integral y capacidad de remolque de 9,520 lbs.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sequoia', 'variante' => 'Limited Hybrid', 'price' => 68000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sequoia Limited con asientos de cuero, audio JBL de 14 parlantes, techo panorámico y portón eléctrico.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sequoia', 'variante' => 'Platinum Hybrid', 'price' => 75000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2027', 'puertas' => '5', 'desc' => 'La Sequoia Platinum con asientos de cuero ventilados, head-up display, cámaras 360° y suspensión adaptativa.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sequoia', 'variante' => 'TRD Pro Hybrid', 'price' => 78000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sequoia TRD Pro con suspensión FOX, skid plates, neumáticos BFGoodrich y modo de conducción todoterreno.'],
    ['marca_id' => $toyota_id, 'modelo' => 'Sequoia', 'variante' => 'Capstone Hybrid', 'price' => 80000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Sequoia Capstone tope de gama con cuero semi-anilina, madera genuina, audio JBL y acabados de lujo supremo.'],

    // CHEVROLET
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'WT', 'price' => 37000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado WT 2026 es la pickup de trabajo con motor turbo de 2.7L, 310 hp y capacidad de carga de hasta 2,280 lbs.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'Custom', 'price' => 43000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado Custom con llantas de 20", pantalla de 8", asientos de tela premium y Super Cruise disponible.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'LT', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado LT con motor V8 de 355 hp, pantalla de 13.4", Bose audio y asistencia de remolque.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'RST', 'price' => 53000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado RST con parrilla body-color, llantas de 22", suspensión deportiva y asientos de cuero.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'LTZ', 'price' => 58000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado LTZ con asientos de cuero calefactados/ventilados, head-up display, cámaras 360° y Bose premium audio.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'High Country', 'price' => 65000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado High Country con V8 de 6.2L y 420 hp, cuero perforado, madera genuina y Multi-Flex tailgate.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Silverado', 'variante' => 'ZR2', 'price' => 72000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Silverado ZR2 con suspensión Multimatic DSSV, 35" neumáticos, locking diffs front/rear y skid plates.'],

    ['marca_id' => $chevy_id, 'modelo' => 'Equinox', 'variante' => 'LS', 'price' => 28500, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Equinox LS 2026 completamente rediseñado con motor turbo de 1.5L, pantalla de 11.3" y Chevy Safety Assist.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Equinox', 'variante' => 'LT', 'price' => 30500, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Equinox LT con tracción integral, llantas de 19", asientos calefactados y control de clima dual.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Equinox', 'variante' => 'RS', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Equinox RS con parrilla negra, llantas de 20", suspensión deportiva y asientos de cuero.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Equinox', 'variante' => 'Active', 'price' => 34000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Equinox Active con barras de techo, molduras protectoras, neumáticos todo terreno y modo de terreno.'],

    ['marca_id' => $chevy_id, 'modelo' => 'Trax', 'variante' => 'LS', 'price' => 21500, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Trax LS 2026 es el SUV más accesible de Chevy con motor turbo de 3 cilindros, pantalla de 11" y Chevy Safety Assist.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Trax', 'variante' => '1RS', 'price' => 23000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Trax 1RS con parrilla negra, llantas de 19", asientos deportivos y detalles RS exclusivos.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Trax', 'variante' => 'LT', 'price' => 24000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Trax LT con pantalla de 11", asientos calefactados, control de clima y cámara de reversa HD.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Trax', 'variante' => '2RS', 'price' => 25000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Trax 2RS con techo negro, llantas de 19" negras, asientos de cuero y paquete de tecnología.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Trax', 'variante' => 'ACTIV', 'price' => 25500, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Trax ACTIV con molduras rugged, barras de techo, llantas de 19" y asientos de cuero perforado.'],

    ['marca_id' => $chevy_id, 'modelo' => 'Traverse', 'variante' => 'LS', 'price' => 38000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Traverse LS 2026 completamente rediseñado con motor turbo de 2.5L, 7-8 pasajeros y Chevy Safety Assist.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Traverse', 'variante' => 'LT', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Traverse LT con tracción integral, pantalla de 17.7", asientos calefactados y control de clima tri-zone.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Traverse', 'variante' => 'RS', 'price' => 47000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Traverse RS con parrilla negra, llantas de 22", asientos de cuero y suspensión deportiva.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Traverse', 'variante' => 'Z71', 'price' => 50000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Traverse Z71 con neumáticos todo terreno, skid plates, modo off-road y molduras protectoras.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Traverse', 'variante' => 'High Country', 'price' => 56000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Traverse High Country tope de gama con asientos de cuero perforados, audio Bose, head-up display y Super Cruise.'],

    ['marca_id' => $chevy_id, 'modelo' => 'Tahoe', 'variante' => 'LS', 'price' => 58000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Tahoe LS 2026 con motor V8 de 5.3L, 355 hp, 9 pasajeros y capacidad de remolque de 8,400 lbs.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Tahoe', 'variante' => 'LT', 'price' => 63000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Tahoe LT con pantalla de 17.7", asientos calefactados, control de clima tri-zone y Super Cruise disponible.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Tahoe', 'variante' => 'RST', 'price' => 68000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Tahoe RST con V8 de 6.2L y 420 hp, parrilla negra, llantas de 22" y suspensión Magnetic Ride.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Tahoe', 'variante' => 'Z71', 'price' => 70000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Tahoe Z71 con suspensión off-road, neumáticos todo terreno, skid plates y modo de terreno.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Tahoe', 'variante' => 'Premier', 'price' => 73000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Tahoe Premier con asientos de cuero ventilados, audio Bose de 10 parlantes, head-up display y Super Cruise.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Tahoe', 'variante' => 'High Country', 'price' => 78000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Tahoe High Country tope de gama con cuero perforado, madera genuina, Bose premium y acabados de lujo.'],

    ['marca_id' => $chevy_id, 'modelo' => 'Suburban', 'variante' => 'LS', 'price' => 61000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Suburban LS 2026 con espacio máximo de carga, motor V8 de 5.3L, 9 pasajeros y capacidad de remolque de 8,300 lbs.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Suburban', 'variante' => 'LT', 'price' => 66000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Suburban LT con pantalla de 17.7", asientos calefactados, control de clima tri-zone y Super Cruise disponible.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Suburban', 'variante' => 'RST', 'price' => 71000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Suburban RST con V8 de 6.2L y 420 hp, parrilla negra, llantas de 22" y suspensión Magnetic Ride.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Suburban', 'variante' => 'Z71', 'price' => 73000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Suburban Z71 con suspensión off-road, neumáticos todo terreno, skid plates y modo de terreno.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Suburban', 'variante' => 'Premier', 'price' => 76000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Suburban Premier con asientos de cuero ventilados, audio Bose de 10 parlantes, head-up display y Super Cruise.'],
    ['marca_id' => $chevy_id, 'modelo' => 'Suburban', 'variante' => 'High Country', 'price' => 80000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Suburban High Country tope de gama con cuero perforado, madera genuina, Bose premium y acabados de lujo supremo.'],

    // HONDA
    ['marca_id' => $honda_id, 'modelo' => 'CR-V', 'variante' => 'LX', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CR-V LX 2026 es el SUV compacto más vendido de Honda con Honda Sensing, pantalla de 7" y excelente espacio interior.'],
    ['marca_id' => $honda_id, 'modelo' => 'CR-V', 'variante' => 'EX', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CR-V EX con tracción integral, techo corredizo, arranque remoto y pantalla táctil de 9".'],
    ['marca_id' => $honda_id, 'modelo' => 'CR-V', 'variante' => 'EX-L', 'price' => 36000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CR-V EX-L con asientos de cuero calefactados, portón eléctrico, audio de 12 parlantes y sensor de estacionamiento.'],
    ['marca_id' => $honda_id, 'modelo' => 'CR-V', 'variante' => 'Sport Hybrid', 'price' => 37000, 'combustible' => 'Híbrido', 'transmision' => 'CVT', 'motor' => '2.0L I4 Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CR-V Sport Hybrid con 40 mpg combinados, tracción integral eléctrica y 204 hp combinados.'],

    ['marca_id' => $honda_id, 'modelo' => 'Civic', 'variante' => 'LX', 'price' => 24000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Civic LX 2026 es el sedán compacto referencia con Honda Sensing, pantalla de 7" y 33 mpg combinados.'],
    ['marca_id' => $honda_id, 'modelo' => 'Civic', 'variante' => 'Sport', 'price' => 26000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Civic Sport con llantas de 18", escape dual, asientos deportivos y modo de conducción Sport.'],
    ['marca_id' => $honda_id, 'modelo' => 'Civic', 'variante' => 'EX', 'price' => 28000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Civic EX con motor turbo, techo corredizo, arranque remoto y pantalla de 9".'],
    ['marca_id' => $honda_id, 'modelo' => 'Civic', 'variante' => 'Touring', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Civic Touring tope de gama con asientos de cuero, audio Bose de 12 parlantes, head-up display y navegación.'],

    ['marca_id' => $honda_id, 'modelo' => 'Accord', 'variante' => 'LX', 'price' => 28000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Accord LX 2026 es el sedán mediano premium con Honda Sensing, pantalla de 12.3" y espacio interior excepcional.'],
    ['marca_id' => $honda_id, 'modelo' => 'Accord', 'variante' => 'Sport', 'price' => 31000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Accord Sport con llantas de 19", suspensión deportiva, asientos de cuero sintético y modo Sport.'],
    ['marca_id' => $honda_id, 'modelo' => 'Accord', 'variante' => 'EX-L', 'price' => 35000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Accord EX-L con asientos de cuero calefactados/ventilados, techo corredizo, audio de 12 parlantes y head-up display.'],
    ['marca_id' => $honda_id, 'modelo' => 'Accord', 'variante' => 'Touring', 'price' => 38000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L Turbo I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Accord Touring con motor 2.0L turbo de 252 hp, asientos de cuero premium, Bose audio y Google Built-In.'],

    ['marca_id' => $honda_id, 'modelo' => 'HR-V', 'variante' => 'LX', 'price' => 24000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El HR-V LX 2026 es el subcompacto versátil con Honda Sensing, Magic Seat y diseño moderno.'],
    ['marca_id' => $honda_id, 'modelo' => 'HR-V', 'variante' => 'Sport', 'price' => 26000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El HR-V Sport con tracción integral, llantas de 18", parrilla negra y detalles deportivos.'],
    ['marca_id' => $honda_id, 'modelo' => 'HR-V', 'variante' => 'EX-L', 'price' => 29000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El HR-V EX-L con asientos de cuero, techo corredizo, navegación y portón eléctrico.'],

    ['marca_id' => $honda_id, 'modelo' => 'Pilot', 'variante' => 'EX-L', 'price' => 44000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Pilot EX-L 2026 es el SUV familiar de 8 pasajeros con V6 de 285 hp, Honda Sensing y tracción integral.'],
    ['marca_id' => $honda_id, 'modelo' => 'Pilot', 'variante' => 'Touring', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Pilot Touring con asientos de cuero ventilados, audio Bose de 10 parlantes, techo panorámico y monitoreo de punto ciego.'],
    ['marca_id' => $honda_id, 'modelo' => 'Pilot', 'variante' => 'TrailSport', 'price' => 50000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Pilot TrailSport con neumáticos todo terreno, suspensión elevada, modos de terreno y protección inferior.'],

    ['marca_id' => $honda_id, 'modelo' => 'Passport', 'variante' => 'EX-L', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Passport EX-L 2026 completamente rediseñado con V6 de 285 hp, tracción integral i-VTM4 y capacidad todoterreno.'],
    ['marca_id' => $honda_id, 'modelo' => 'Passport', 'variante' => 'TrailSport', 'price' => 46000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Passport TrailSport con suspensión off-road, neumáticos todo terreno, skid plates y modos de terreno.'],

    // RAM
    ['marca_id' => $ram_id, 'modelo' => 'Ram 1500', 'variante' => 'Limited', 'price' => 60000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.0L I6 Hurricane Turbo', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 1500 Limited 2026 con motor Hurricane twin-turbo de 420 hp, asientos de cuero y pantalla de 14.5".'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 1500', 'variante' => 'Limited Longhorn', 'price' => 65000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.0L I6 Hurricane Turbo', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 1500 Limited Longhorn con cuero western, madera genuina, audio Harman Kardon y Multi-Flex tailgate.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 1500', 'variante' => 'Tungsten', 'price' => 70000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.0L I6 Hurricane High-Output', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 1500 Tungsten tope de gama con cuero Nappa, pantalla de pasajero, audio Klipsch de 23 parlantes.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 1500', 'variante' => 'TRX / RHO', 'price' => 85000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.0L I6 Hurricane High-Output', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 1500 RHO con suspensión Bilstein, 35" neumáticos, 540 hp y modos de conducción off-road.'],

    ['marca_id' => $ram_id, 'modelo' => 'Ram 2500', 'variante' => 'Laramie', 'price' => 55000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.4L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 2500 Laramie con V8 Hemi, capacidad de remolque de 17,540 lbs y asientos de cuero.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 2500', 'variante' => 'Power Wagon', 'price' => 62000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.4L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 2500 Power Wagon con winch eléctrico, locking diffs, desconexión de sway bar y 33" neumáticos.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 2500', 'variante' => 'Limited Longhorn', 'price' => 72000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.7L I6 Cummins Turbo Diesel', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 2500 Limited Longhorn con Cummins turbo diesel, cuero western y capacidad de remolque de 20,000 lbs.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 2500', 'variante' => 'Limited', 'price' => 75000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.7L I6 Cummins Turbo Diesel', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 2500 Limited con Cummins diesel, asientos de cuero ventilados, audio Harman Kardon y cámara de remolque.'],

    ['marca_id' => $ram_id, 'modelo' => 'Ram 3500', 'variante' => 'Laramie', 'price' => 58000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.4L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 3500 Laramie con capacidad de remolque de 37,100 lbs, V8 Hemi y asientos de cuero.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 3500', 'variante' => 'Limited Longhorn', 'price' => 78000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.7L I6 Cummins Turbo Diesel', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 3500 Limited Longhorn con Cummins high-output diesel, remolque de 37,100 lbs y lujo premium.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram 3500', 'variante' => 'Limited', 'price' => 82000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.7L I6 Cummins Turbo Diesel High-Output', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram 3500 Limited tope de gama con Cummins HO de 430 hp, 1,075 lb-ft, cuero Nappa y tecnología avanzada.'],

    ['marca_id' => $ram_id, 'modelo' => 'Ram ProMaster', 'variante' => '2500', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram ProMaster 2500 es la van comercial con motor Pentastar V6, espacio de carga amplio y puerta trasera 270°.'],
    ['marca_id' => $ram_id, 'modelo' => 'Ram ProMaster', 'variante' => '3500', 'price' => 45000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Ram ProMaster 3500 con capacidad de carga máxima, techo alto y tecnología de asistencia al conductor.'],

    // GMC
    ['marca_id' => $gmc_id, 'modelo' => 'Sierra 1500', 'variante' => 'SLT', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Sierra 1500 SLT 2026 con V8 de 355 hp, asientos de cuero, pantalla de 13.4" y MultiPro tailgate.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Sierra 1500', 'variante' => 'AT4', 'price' => 58000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Sierra 1500 AT4 con V8 de 420 hp, suspensión off-road, 33" neumáticos y modo de terreno.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Sierra 1500', 'variante' => 'Denali', 'price' => 65000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Sierra 1500 Denali con asientos de cuero ventilados, audio Bose de 12 parlantes, Super Cruise y head-up display.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Sierra 1500', 'variante' => 'Denali Ultimate', 'price' => 75000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Sierra 1500 Denali Ultimate con cuero Alpine Umber, madera de nogal, Bose 15 parlantes y Super Cruise.'],

    ['marca_id' => $gmc_id, 'modelo' => 'Terrain', 'variante' => 'SLT', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Terrain SLT 2026 completamente rediseñado con motor turbo, pantalla de 15" y GMC Pro Safety Plus.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Terrain', 'variante' => 'AT4', 'price' => 37000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Terrain AT4 con neumáticos todo terreno, modos de terreno, molduras protectoras y suspensión elevada.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Terrain', 'variante' => 'Denali', 'price' => 40000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Terrain Denali con asientos de cuero, audio Bose, head-up display y Super Cruise disponible.'],

    ['marca_id' => $gmc_id, 'modelo' => 'Acadia', 'variante' => 'SLT', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Acadia SLT 2026 completamente rediseñado con motor turbo de 328 hp, 8 pasajeros y GMC Premium Pro Safety.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Acadia', 'variante' => 'AT4', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Acadia AT4 con suspensión off-road, neumáticos todo terreno, modos de terreno y protección inferior.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Acadia', 'variante' => 'Denali', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Acadia Denali con asientos de cuero ventilados, audio Bose de 12 parlantes, Super Cruise y head-up display.'],

    ['marca_id' => $gmc_id, 'modelo' => 'Yukon', 'variante' => 'SLT', 'price' => 62000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.3L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Yukon SLT 2026 con V8 de 355 hp, 9 pasajeros, capacidad de remolque de 8,400 lbs y pantalla de 15".'],
    ['marca_id' => $gmc_id, 'modelo' => 'Yukon', 'variante' => 'AT4', 'price' => 70000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Yukon AT4 con V8 de 420 hp, suspensión off-road Magnetic Ride, neumáticos todo terreno y modo de terreno.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Yukon', 'variante' => 'Denali', 'price' => 78000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Yukon Denali con asientos de cuero ventilados, audio Bose de 14 parlantes, Super Cruise y head-up display.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Yukon', 'variante' => 'Denali Ultimate', 'price' => 88000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '6.2L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'La Yukon Denali Ultimate con cuero Alpine Umber, madera de nogal, Bose 21 parlantes y acabados de lujo supremo.'],

    ['marca_id' => $gmc_id, 'modelo' => 'Canyon', 'variante' => 'AT4', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Canyon AT4 2026 con motor turbo de 310 hp, suspensión off-road, 33" neumáticos y modo de terreno.'],
    ['marca_id' => $gmc_id, 'modelo' => 'Canyon', 'variante' => 'Denali', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Canyon Denali con asientos de cuero, audio Bose, pantalla de 15" y Super Cruise disponible.'],

    // JEEP
    ['marca_id' => $jeep_id, 'modelo' => 'Wrangler', 'variante' => 'Sport', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Wrangler Sport 2026 con motor V6 Pentastar, techo removible, tracción 4x4 Command-Trac y capacidad todoterreno legendaria.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Wrangler', 'variante' => 'Sport S', 'price' => 36000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Wrangler Sport S con llantas de aleación de 17", pantalla de 8.4", asientos calefactados y volante de cuero.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Wrangler', 'variante' => 'Willys', 'price' => 40000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Wrangler Willys con neumáticos de 33", locking rear differential, skid plates y detalles Willys exclusivos.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Wrangler', 'variante' => 'Sahara', 'price' => 44000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Wrangler Sahara con pantalla de 12.3", asientos de cuero, techo corredizo y Alpine audio de 9 parlantes.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Wrangler', 'variante' => 'Rubicon', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Wrangler Rubicon con ejes Dana 44, locking diffs front/rear, desconexión de sway bar y neumáticos de 33".'],
    ['marca_id' => $jeep_id, 'modelo' => 'Wrangler', 'variante' => 'Rubicon X', 'price' => 55000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Wrangler Rubicon X con llantas de 17" beadlock, suspensión FOX, winch WARN y asientos de cuero premium.'],

    ['marca_id' => $jeep_id, 'modelo' => 'Gladiator', 'variante' => 'Sport', 'price' => 37000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Gladiator Sport 2026 es la pickup de Jeep con motor V6, caja de 5 pies y capacidad todoterreno Wrangler.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Gladiator', 'variante' => 'Willys', 'price' => 44000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Gladiator Willys con neumáticos de 33", locking rear differential, skid plates y detalles Willys.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Gladiator', 'variante' => 'Overland', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Gladiator Overland con asientos de cuero, Alpine audio, techo corredizo y portón eléctrico.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Gladiator', 'variante' => 'Rubicon', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Gladiator Rubicon con ejes Dana 44, locking diffs, desconexión de sway bar y capacidad de remolque de 7,650 lbs.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Gladiator', 'variante' => 'Mojave', 'price' => 55000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '4', 'desc' => 'La Gladiator Mojave con suspensión FOX, 33" neumáticos, skid plates y modo de conducción desert.'],

    ['marca_id' => $jeep_id, 'modelo' => 'Cherokee', 'variante' => 'Latitude', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Cherokee Latitude 2026 completamente rediseñado con motor turbo de 2.0L, pantalla de 12.3" y Jeep Active Drive.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Cherokee', 'variante' => 'Altitute', 'price' => 36000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Cherokee Altitude con parrilla negra, llantas de 20" negras, asientos de cuero y detalles oscuros.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Cherokee', 'variante' => 'Limited', 'price' => 40000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Cherokee Limited con asientos de cuero ventilados, Alpine audio de 12 parlantes, techo panorámico y head-up display.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Cherokee', 'variante' => 'Trailhawk', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Cherokee Trailhawk con neumáticos todo terreno, modos de terreno, skid plates y suspensión elevada.'],

    ['marca_id' => $jeep_id, 'modelo' => 'Grand Cherokee', 'variante' => 'Laredo', 'price' => 38000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Cherokee Laredo 2026 con V6 Pentastar de 293 hp, pantalla de 10.1" y Jeep Active Drive.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Grand Cherokee', 'variante' => 'Altitude', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Cherokee Altitude con parrilla negra, llantas de 20" negras, asientos de cuero y detalles oscuros.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Grand Cherokee', 'variante' => 'Limited', 'price' => 48000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Cherokee Limited con asientos de cuero ventilados, Alpine audio de 10 parlantes, techo panorámico y head-up display.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Grand Cherokee', 'variante' => 'Overland', 'price' => 55000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.7L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Cherokee Overland con V8 Hemi de 357 hp, asientos de Nappa leather, McIntosh audio y suspensión neumática.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Grand Cherokee', 'variante' => 'Summit', 'price' => 62000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.7L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Cherokee Summit con cuero Palermo, madera de nogal, McIntosh de 19 parlantes y Quadra-Lift.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Grand Cherokee', 'variante' => 'Summit Reserve', 'price' => 68000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '5.7L V8', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Grand Cherokee Summit Reserve tope de gama con cuero Nappa, madera de eucalipto, McIntosh 21 parlantes y head-up display.'],

    ['marca_id' => $jeep_id, 'modelo' => 'Compass', 'variante' => 'Sport', 'price' => 28000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Compass Sport 2026 completamente rediseñado con motor turbo de 2.0L, pantalla de 10.1" y Jeep Active Drive.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Compass', 'variante' => 'Latitude', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Compass Latitude con llantas de aleación de 18", pantalla de 10.1", asientos calefactados y control de clima dual.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Compass', 'variante' => 'Latitude Lux', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Compass Latitude Lux con asientos de cuero, Alpine audio, techo panorámico y portón eléctrico.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Compass', 'variante' => 'Limited', 'price' => 36000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Compass Limited con asientos de cuero ventilados, Alpine de 10 parlantes, head-up display y monitoreo 360°.'],
    ['marca_id' => $jeep_id, 'modelo' => 'Compass', 'variante' => 'Trailhawk', 'price' => 38000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Compass Trailhawk con neumáticos todo terreno, modos de terreno, skid plates y suspensión elevada.'],

    // MAZDA
    ['marca_id' => $mazda_id, 'modelo' => 'CX-5', 'variante' => 'Carbon Edition', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-5 Carbon Edition 2026 con detalles en negro brillante, llantas de 19" negras, asientos de cuero rojo y i-Activ AWD.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-5', 'variante' => 'Premium', 'price' => 34000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-5 Premium con asientos de Nappa leather, Bose de 10 parlantes, head-up display y techo corredizo.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-5', 'variante' => 'Premium Plus', 'price' => 37000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-5 Premium Plus con ventilación de asientos, monitoreo 360°, portón eléctrico y sistema de navegación.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-5', 'variante' => 'Turbo Signature', 'price' => 40000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-5 Turbo Signature con 256 hp, cuero Caturra Brown, madera de Sen, Bose premium y acabados de lujo.'],

    ['marca_id' => $mazda_id, 'modelo' => 'CX-30', 'variante' => 'Carbon Edition', 'price' => 27000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-30 Carbon Edition con detalles en negro, llantas de 18" negras, asientos de cuero rojo y i-Activ AWD.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-30', 'variante' => 'Premium', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-30 Premium con asientos de Nappa leather, Bose de 12 parlantes, head-up display y techo corredizo.'],

    ['marca_id' => $mazda_id, 'modelo' => 'CX-50', 'variante' => 'Premium', 'price' => 32000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-50 Premium 2026 con diseño rugged, asientos de Nappa leather, Bose de 12 parlantes y modo de terreno.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-50', 'variante' => 'Premium Plus', 'price' => 35000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-50 Premium Plus con ventilación de asientos, monitoreo 360°, portón eléctrico y sistema de navegación.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-50', 'variante' => 'Meridian Edition', 'price' => 38000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-50 Meridian Edition con motor turbo de 256 hp, neumáticos todo terreno, modos de terreno y molduras rugged.'],

    ['marca_id' => $mazda_id, 'modelo' => 'CX-90', 'variante' => 'Premium', 'price' => 40000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.3L I6 Turbo', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-90 Premium 2026 con motor I6 turbo de 280 hp, 7-8 pasajeros, asientos de Nappa leather y Bose de 12 parlantes.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-90', 'variante' => 'Premium Plus', 'price' => 45000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.3L I6 Turbo', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-90 Premium Plus con ventilación de asientos, monitoreo 360°, head-up display y sistema de navegación.'],
    ['marca_id' => $mazda_id, 'modelo' => 'CX-90', 'variante' => 'S Premium', 'price' => 50000, 'combustible' => 'Híbrido', 'transmision' => 'Automática', 'motor' => '3.3L I6 Turbo Hybrid', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El CX-90 S Premium con motor híbrido de 323 hp, cuero Nappa, madera de Sen, Bose premium y acabados de lujo.'],

    ['marca_id' => $mazda_id, 'modelo' => 'Mazda3', 'variante' => 'Carbon Edition', 'price' => 27000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Mazda3 Carbon Edition con detalles en negro, llantas de 18" negras, asientos de cuero rojo y i-Activ AWD.'],
    ['marca_id' => $mazda_id, 'modelo' => 'Mazda3', 'variante' => 'Premium', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Mazda3 Premium con asientos de Nappa leather, Bose de 12 parlantes, head-up display y techo corredizo.'],
    ['marca_id' => $mazda_id, 'modelo' => 'Mazda3', 'variante' => 'Turbo Premium Plus', 'price' => 35000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '4', 'desc' => 'El Mazda3 Turbo Premium Plus con 250 hp, ventilación de asientos, monitoreo 360° y acabados premium.'],

    // BUICK
    ['marca_id' => $buick_id, 'modelo' => 'Encore GX', 'variante' => 'Sport Touring', 'price' => 27000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.3L Turbo I3', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Encore GX Sport Touring 2026 con motor turbo de 3 cilindros, pantalla de 11", Buick Driver Confidence Plus.'],
    ['marca_id' => $buick_id, 'modelo' => 'Encore GX', 'variante' => 'Essence', 'price' => 30000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.3L Turbo I3', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Encore GX Essence con asientos de cuero, Bose de 8 parlantes, techo panorámico y portón eléctrico.'],
    ['marca_id' => $buick_id, 'modelo' => 'Encore GX', 'variante' => 'Avenir', 'price' => 33000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.3L Turbo I3', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Encore GX Avenir tope de gama con asientos perforados, head-up display, monitoreo 360° y acabados premium.'],

    ['marca_id' => $buick_id, 'modelo' => 'Envision', 'variante' => 'Sport Touring', 'price' => 35000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Envision Sport Touring 2026 con motor turbo de 2.0L, pantalla de 11", tracción integral y Buick Driver Confidence.'],
    ['marca_id' => $buick_id, 'modelo' => 'Envision', 'variante' => 'Essence', 'price' => 38000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Envision Essence con asientos de cuero, Bose de 10 parlantes, techo panorámico y head-up display.'],
    ['marca_id' => $buick_id, 'modelo' => 'Envision', 'variante' => 'Avenir', 'price' => 42000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '2.0L Turbo I4', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Envision Avenir tope de gama con asientos de cuero ventilados, Bose premium, Super Cruise y acabados de lujo.'],

    ['marca_id' => $buick_id, 'modelo' => 'Enclave', 'variante' => 'Essence', 'price' => 45000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Enclave Essence 2026 completamente rediseñado con V6 de 310 hp, 7-8 pasajeros y pantalla de 17.7".'],
    ['marca_id' => $buick_id, 'modelo' => 'Enclave', 'variante' => 'Avenir', 'price' => 52000, 'combustible' => 'Gasolina', 'transmision' => 'Automática', 'motor' => '3.6L V6', 'traccion' => 'AWD', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Enclave Avenir tope de gama con asientos de cuero ventilados, Bose premium, Super Cruise y acabados de lujo.'],

    ['marca_id' => $buick_id, 'modelo' => 'Envista', 'variante' => 'Sport Touring', 'price' => 23000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Envista Sport Touring 2026 es el crossover más accesible de Buick con motor turbo de 3 cilindros y pantalla de 11".'],
    ['marca_id' => $buick_id, 'modelo' => 'Envista', 'variante' => 'Avenir', 'price' => 26000, 'combustible' => 'Gasolina', 'transmision' => 'CVT', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'anio' => '2026', 'puertas' => '5', 'desc' => 'El Envista Avenir con asientos de cuero, Bose de 8 parlantes, techo panorámico y acabados premium.'],
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

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$default_components = [
    ['hero_slider', '{"show_title":true,"show_price":true}', 1, 1],
    ['specs_destacadas', '{"max_items":6}', 1, 2],
    ['descripcion', '{}', 1, 3],
    ['exterior_interior', '{"exterior_title":"Exterior","exterior_description":"","exterior_image":"","interior_title":"Interior","interior_description":"","interior_image":""}', 1, 4],
    ['image_gallery', '{"layout":"grid"}', 1, 5],
    ['specs_tabla', '{}', 1, 6],
    ['video', '{}', 0, 7],
    ['cta_whatsapp', '{}', 1, 8],
    ['calculadora', '{}', 1, 9],
    ['autos_relacionados', '{"max_items":4}', 1, 10],
];

$count = 0;
function get_marca_nombre($marca_id, $toyota_id, $chevy_id, $honda_id, $ram_id, $gmc_id, $jeep_id, $mazda_id, $buick_id) {
    if ($marca_id == $toyota_id) return 'Toyota';
    if ($marca_id == $chevy_id) return 'Chevrolet';
    if ($marca_id == $honda_id) return 'Honda';
    if ($marca_id == $ram_id) return 'RAM';
    if ($marca_id == $gmc_id) return 'GMC';
    if ($marca_id == $jeep_id) return 'Jeep';
    if ($marca_id == $mazda_id) return 'Mazda';
    if ($marca_id == $buick_id) return 'Buick';
    return 'Unknown';
}

function getVehicleType($modelo) {
    $modelo = strtolower($modelo);
    $pickups = ['silverado', 'tundra', 'tacoma', 'sierra', 'ram 1500', 'ram 2500', 'ram 3500', 'canyon', 'gladiator'];
    $sedans = ['camry', 'accord', 'corolla', 'civic', 'mazda3'];
    $minivans = ['sienna'];
    $suvs_compact = ['rav4', 'cr-v', 'equinox', 'cx-5', 'compass', 'cherokee', 'terrain', 'encore gx', 'envista', 'hr-v', 'corolla cross', 'trax', 'cx-30'];
    $suvs_mid = ['highlander', 'grand highlander', 'pilot', 'passport', 'traverse', 'acadia', 'cx-90', 'enclave', 'yukon', 'tahoe', 'suburban', 'sequoia', '4runner', 'wrangler', 'grand cherokee', 'compass'];
    $vans = ['promaster'];

    foreach ($pickups as $p) if (strpos($modelo, $p) !== false) return 'pickup';
    foreach ($sedans as $s) if (strpos($modelo, $s) !== false) return 'sedan';
    foreach ($minivans as $m) if (strpos($modelo, $m) !== false) return 'minivan';
    foreach ($vans as $v) if (strpos($modelo, $v) !== false) return 'van';
    foreach ($suvs_compact as $s) if (strpos($modelo, $s) !== false) return 'suv_compacto';
    foreach ($suvs_mid as $s) if (strpos($modelo, $s) !== false) return 'suv_mediano';
    return 'suv';
}

function generateExteriorDesc($marca, $modelo, $variante, $tipo) {
    $variante_lower = strtolower($variante);
    $es_hybrid = strpos($variante_lower, 'hybrid') !== false;
    $es_deportivo = strpos($variante_lower, 'sport') !== false || strpos($variante_lower, 'trd') !== false || strpos($variante_lower, 'rs') !== false || strpos($variante_lower, 'at4') !== false;
    $es_lujo = strpos($variante_lower, 'limited') !== false || strpos($variante_lower, 'platinum') !== false || strpos($variante_lower, 'denali') !== false || strpos($variante_lower, 'touring') !== false || strpos($variante_lower, 'tungsten') !== false || strpos($variante_lower, 'capstone') !== false;
    $es_offroad = strpos($variante_lower, 'off-road') !== false || strpos($variante_lower, 'trailhawk') !== false || strpos($variante_lower, 'rubicon') !== false || strpos($variante_lower, 'power wagon') !== false || strpos($variante_lower, 'trailsport') !== false || strpos($variante_lower, 'zr2') !== false || strpos($variante_lower, 'mojave') !== false || strpos($variante_lower, 'willys') !== false;

    $desc = '';

    if ($tipo === 'pickup') {
        $desc = "Diseño robusto y dominante con parrilla imponente de $marca, faros LED de última generación y líneas musculosas que reflejan potencia y capacidad. ";
        if ($es_lujo) $desc .= "Acabados cromados premium, llantas de aleación de gran tamaño y detalles exclusivos que elevan su presencia en cualquier entorno. ";
        elseif ($es_deportivo) $desc .= "Estética agresiva con detalles en negro, llantas deportivas y aerodinámica optimizada para el rendimiento. ";
        elseif ($es_offroad) $desc .= "Construcción todoterreno con mayor altura al suelo, neumáticos off-road, skid plates y detalles funcionales para cualquier terreno. ";
        else $desc .= "Estilo versátil que combina funcionalidad de trabajo con elegancia moderna. ";
        $desc .= "La caja de carga está diseñada para máxima utilidad con iluminación integrada y múltiples puntos de anclaje.";
    } elseif ($tipo === 'sedan') {
        $desc = "Silueta elegante y aerodinámica con líneas fluidas que destacan la sofisticación del $modelo. ";
        if ($es_lujo) $desc .= "Faros LED adaptativos, parrilla exclusiva con detalles cromados, llantas de aleación de diseño premium y acabados que reflejan lujo discreto. ";
        elseif ($es_deportivo) $desc .= "Alerón trasero integrado, parrilla deportiva con malla negra, llantas de aleación de diseño agresivo y doble salida de escape. ";
        else $desc .= "Faros LED con firma lumínica distintiva, parrilla moderna y llantas de aleación con diseño elegante. ";
        $desc .= "La carrocería presenta un coeficiente aerodinámico optimizado para eficiencia y estabilidad en carretera.";
    } elseif ($tipo === 'minivan') {
        $desc = "Diseño moderno y funcional con líneas aerodinámicas que optimizan la eficiencia. Puertas corredizas eléctricas de ambos lados para acceso sin esfuerzo, faros LED delgados y parrilla distintiva de $marca. ";
        $desc .= "El techo incluye rieles portaequipajes integrados y el portón trasero es completamente automatizado. ";
        if ($es_lujo) $desc .= "Detalles cromados y llantas de aleación premium completan su apariencia sofisticada y familiar.";
        else $desc .= "Llantas de aleación resistentes y acabados prácticos para el día a día familiar.";
    } elseif ($tipo === 'van') {
        $desc = "Diseño funcional y versátil optimizado para carga y trabajo. Amplias puertas laterales corredizas y portón trasero de 270 grados facilitan el acceso completo al área de carga. ";
        $desc .= "Faros halógenos de largo alcance, parrilla resistente y carrocería de paneles lisos para personalización.";
    } elseif ($tipo === 'suv_compacto') {
        $desc = "Diseño crossover moderno con líneas deportivas y musculosas. Faros LED afilados, parrilla característica de $marca y perfil dinámico que combina estilo urbano con capacidad todoterreno. ";
        if ($es_offroad) $desc .= "Detalles rugged con molduras protectoras, barras de techo y mayor altura al suelo para aventuras. ";
        elseif ($es_lujo) $desc .= "Detalles cromados refinados, llantas de aleación de diseño exclusivo y acabados premium que destacan su clase. ";
        elseif ($es_deportivo) $desc .= "Detalles deportivos con acentos negros, llantas de aleación de diseño exclusivo y suspensión ligeramente rebajada. ";
        else $desc .= "Molduras protectoras laterales, barras de techo integradas y llantas de aleación con diseño moderno. ";
        $desc .= "El portón trasero ofrece amplio acceso al área de carga versátil.";
    } else { // suv_mediano o suv
        $desc = "Presencia imponente con diseño SUV de tamaño completo, líneas robustas y musculosas que proyectan confianza. Faros LED con firma lumínica, parrilla prominente de $marca y gran altura al suelo. ";
        if ($es_lujo) $desc .= "Llantas de aleación de gran tamaño con diseño exclusivo, detalles cromados premium y techo panorámico de doble panel. ";
        elseif ($es_offroad) $desc .= "Neumáticos todo terreno de gran tamaño, skid plates, barras de techo reforzadas y suspensión elevada para cualquier terreno. ";
        elseif ($es_deportivo) $desc .= "Llantas deportivas de gran tamaño, detalles en negro brillante, escape dual y spoiler trasero integrado. ";
        else $desc .= "Llantas de aleación de diseño elegante, molduras protectoras y barras de techo integradas. ";
        $desc .= "Portón trasero eléctrico con apertura manos libres y amplio espacio de carga.";
    }

    return trim($desc);
}

function generateInteriorDesc($marca, $modelo, $variante, $tipo) {
    $variante_lower = strtolower($variante);
    $es_hybrid = strpos($variante_lower, 'hybrid') !== false;
    $es_deportivo = strpos($variante_lower, 'sport') !== false || strpos($variante_lower, 'trd') !== false || strpos($variante_lower, 'rs') !== false || strpos($variante_lower, 'at4') !== false;
    $es_lujo = strpos($variante_lower, 'limited') !== false || strpos($variante_lower, 'platinum') !== false || strpos($variante_lower, 'denali') !== false || strpos($variante_lower, 'touring') !== false || strpos($variante_lower, 'tungsten') !== false || strpos($variante_lower, 'capstone') !== false;
    $es_offroad = strpos($variante_lower, 'off-road') !== false || strpos($variante_lower, 'trailhawk') !== false || strpos($variante_lower, 'rubicon') !== false || strpos($variante_lower, 'power wagon') !== false || strpos($variante_lower, 'trailsport') !== false || strpos($variante_lower, 'zr2') !== false || strpos($variante_lower, 'mojave') !== false || strpos($variante_lower, 'willys') !== false;

    $desc = '';

    if ($tipo === 'pickup') {
        $desc = "Cabina espaciosa y funcional diseñada para el confort durante largas jornadas. ";
        if ($es_lujo) $desc .= "Asientos de cuero premium ventilados y calefactados, madera genuina en el tablero, pantalla táctil de gran formato y sistema de audio premium. ";
        elseif ($es_deportivo) $desc .= "Asientos deportivos con soporte lateral reforzado, volante con paletas de cambio, aluminio en el tablero y pantalla táctil con conectividad completa. ";
        else $desc .= "Asientos de tela resistente con ajuste eléctrico, volante multifunción, pantalla táctil con Apple CarPlay/Android Auto y amplio espacio de almacenamiento. ";
        $desc .= "Múltiples puertos USB, tomas de corriente de 120V y compartimentos versátiles para herramientas y dispositivos.";
    } elseif ($tipo === 'sedan') {
        $desc = "Cabina refinada con materiales de alta calidad y acabados impecables. ";
        if ($es_lujo) $desc .= "Asientos de cuero Napa ventilados y calefactados, madera real en el tablero, head-up display, sistema de audio premium de 12+ parlantes y ambientación interior. ";
        elseif ($es_deportivo) $desc .= "Asientos deportivos con combinación de tela y cuero, pedalera de aluminio, volante con base achatada y pantalla digital del conductor. ";
        else $desc .= "Asientos de tela premium con ajuste manual, volante de cuero, pantalla táctil de 7-9 pulgadas y sistema de audio de 6-8 parlantes. ";
        $desc .= "Amplio espacio para pasajeros traseros y maletero generoso para equipaje.";
    } elseif ($tipo === 'minivan') {
        $desc = "Interior diseñado para la máxima versatilidad familiar con configuración de 7-8 asientos. Magic Slide de segunda fila permite múltiples configuraciones. ";
        if ($es_lujo) $desc .= "Asientos de cuero calefactados y ventilados, sistema de entretenimiento trasero con pantallas, audio premium de 12 parlantes y refrigerador central. ";
        else $desc .= "Asientos de tela resistente fáciles de limpiar, múltiples puertos USB en todas las filas, control de clima tri-zone y amplios compartimentos de almacenamiento. ";
        $desc .= "El área de carga trasera es enorme con el sistema de asientos abatibles planos.";
    } elseif ($tipo === 'van') {
        $desc = "Interior espacioso y funcional orientado al trabajo y carga. Asientos delanteros ergonómicos con múltiples ajustes. ";
        $desc .= "Panel de instrumentos simple y legible, múltiples compartimentos de almacenamiento, conectividad Bluetooth básica y amplias superficies para instalar estantes y equipamiento. ";
        $desc .= "El espacio de carga trasero es completamente configurable.";
    } elseif ($tipo === 'suv_compacto') {
        $desc = "Interior moderno y versátil con materiales de calidad y tecnología avanzada. ";
        if ($es_lujo) $desc .= "Asientos de cuero con costuras contrastantes, tablero soft-touch, pantalla táctil de 9-12 pulgadas, audio premium y techo panorámico. ";
        elseif ($es_offroad) $desc .= "Asientos resistentes con material fácil de limpiar, ganchos de anclaje, protectores de umbrales y tecnología off-road integrada. ";
        elseif ($es_deportivo) $desc .= "Asientos con soporte lateral deportivo, pedalera de aluminio, volante con controles integrados y pantalla digital. ";
        else $desc .= "Asientos de tela premium con ajuste manual, volante multifunción, pantalla táctil central y sistema de audio de 6 parlantes. ";
        $desc .= "Espacio trasero versátil con asientos abatibles 60/40 y múltiples compartimentos.";
    } else { // suv_mediano o suv
        $desc = "Cabina premium de tres filas con espacio para 7-9 pasajeros y materiales de alta calidad en toda la superficie. ";
        if ($es_lujo) $desc .= "Asientos de cuero ventilados y calefactados en primera y segunda fila, madera real, techo panorámico de doble panel, head-up display y audio premium de 14+ parlantes. ";
        elseif ($es_offroad) $desc .= "Asientos resistentes con materiales duraderos, controles off-road centralizados, pantalla táctil de terreno y múltiples modos de conducción. ";
        elseif ($es_deportivo) $desc .= "Asientos deportivos con combinación de cuero y microfibra, volante calefactado, paletas de cambio y suspensiones deportivas ajustables. ";
        else $desc .= "Asientos de cuero SofTex o tela premium con ajuste eléctrico, volante calefactado, pantalla táctil de 8-12 pulgadas y control de clima tri-zone. ";
        $desc .= "Amplio espacio de carga con asientos abatibles planos y múltiples configuraciones de almacenamiento.";
    }

    return trim($desc);
}

foreach ($vehiculos as $v) {
    $marca_nombre = get_marca_nombre($v['marca_id'], $toyota_id, $chevy_id, $honda_id, $ram_id, $gmc_id, $jeep_id, $mazda_id, $buick_id);
    // Auto-assign 2027 for top-tier trims
    $top_trims = ['Capstone', 'Tungsten', 'Denali Ultimate', 'Summit Reserve', 'TRX / RHO', 'Turbo Signature', 'S Premium', 'Turbo Premium Plus', 'Rubicon X', 'High Country', 'ZR2', '1794 Edition', 'TRD Pro', 'Platinum'];
    $is_top = false;
    foreach ($top_trims as $tt) { if (strpos($v['variante'], $tt) !== false) { $is_top = true; break; } }
    $v['anio'] = $is_top ? '2027' : '2026';
    // Update year in description text to match actual model year
    $v['desc'] = preg_replace('/\b20\d{2}\b/', $v['anio'], $v['desc']);

    $title = $marca_nombre . " " . $v['modelo'] . " " . $v['variante'] . " " . $v['anio'];
    $slug = slugify($title);

    // Check unique slug
    $counter = 1;
    $orig_slug = $slug;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE slug = ?");
    $stmt->execute([$slug]);
    while ($stmt->fetchColumn() > 0) {
        $slug = $orig_slug . '-' . $counter++;
        $stmt->execute([$slug]);
    }

    $image_seed = strtolower(str_replace(' ', '-', $marca_nombre . '-' . $v['modelo'] . '-' . $v['variante']));
    $primary_img = 'uploads/placeholder_car.jpg';

    // Download image from picsum (skip if takes too long)
    $img_url = 'https://picsum.photos/seed/' . $image_seed . '/800/600';
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $img_binary = @file_get_contents($img_url, false, $ctx);
    if ($img_binary !== false) {
        $img_filename = 'car_' . slugify($v['modelo'] . '-' . $v['variante']) . '_' . time() . '.jpg';
        file_put_contents($upload_dir . $img_filename, $img_binary);
        $primary_img = 'uploads/' . $img_filename;
    }

    // Insert car
    $stmt = $pdo->prepare("INSERT INTO cars (marca_id, modelo, title, slug, price, image_path, description, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 0)");
    $stmt->execute([$v['marca_id'], $v['modelo'], $title, $slug, $v['price'], $primary_img, $v['desc']]);
    $car_id = $pdo->lastInsertId();

    // Insert gallery images (use same primary image as placeholder)
    for ($gi = 0; $gi < 2; $gi++) {
        $gallery_url = 'https://picsum.photos/seed/' . $image_seed . '-' . $gi . '/800/600';
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $gimg_binary = @file_get_contents($gallery_url, false, $ctx);
        if ($gimg_binary !== false) {
            $gimg_filename = 'car_' . $car_id . '_gallery_' . $gi . '_' . time() . '.jpg';
            file_put_contents($upload_dir . $gimg_filename, $gimg_binary);
            $is_primary = ($gi === 0) ? 1 : 0;
            $pdo->prepare("INSERT INTO car_images (car_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)")->execute([$car_id, 'uploads/' . $gimg_filename, $is_primary, $gi]);
        }
    }

    // Update primary image to first gallery image if available
    $pdo->prepare("UPDATE cars SET image_path = (SELECT image_path FROM car_images WHERE car_id = ? AND is_primary = 1 LIMIT 1) WHERE id = ?")->execute([$car_id, $car_id]);

    // Insert specs
    $specs = [
        ['marca', $marca_nombre],
        ['modelo', $v['modelo']],
        ['anio', $v['anio']],
        ['transmision', $v['transmision']],
        ['combustible', $v['combustible']],
        ['motor', $v['motor']],
        ['traccion', $v['traccion']],
        ['puertas', $v['puertas']],
    ];

    $sort = 1;
    foreach ($specs as $spec) {
        $field_id = $spec_field_map[$spec[0]] ?? null;
        if ($field_id) {
            $pdo->prepare("INSERT INTO car_specs (car_id, spec_field_id, valor, sort_order) VALUES (?, ?, ?, ?)")->execute([$car_id, $field_id, $spec[1], $sort++]);
        } else {
            $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, ?, ?, ?)")->execute([$car_id, ucfirst($spec[0]), $spec[1], $sort++]);
        }
    }

    // Insert default components with personalized exterior_interior
    $tipo = getVehicleType($v['modelo']);
    $ext_desc = generateExteriorDesc($marca_nombre, $v['modelo'], $v['variante'], $tipo);
    $int_desc = generateInteriorDesc($marca_nombre, $v['modelo'], $v['variante'], $tipo);

    foreach ($default_components as $comp) {
        $comp_type = $comp[0];
        $config = $comp[1];
        // Replace exterior_interior config with personalized descriptions
        if ($comp_type === 'exterior_interior') {
            $config = json_encode([
                'exterior_title' => 'Exterior',
                'exterior_description' => $ext_desc,
                'exterior_image' => '',
                'interior_title' => 'Interior',
                'interior_description' => $int_desc,
                'interior_image' => ''
            ]);
        }
        $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$car_id, $comp_type, $config, $comp[2], $comp[3]]);
    }

    $count++;
    echo "✓ $title ($" . number_format($v['price'], 2) . ")\n";
}

echo "\n✅ $count vehículos sembrados exitosamente.\n";
echo "   - Toyota: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $toyota_id)) . " unidades\n";
echo "   - Chevrolet: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $chevy_id)) . " unidades\n";
echo "   - Honda: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $honda_id)) . " unidades\n";
echo "   - RAM: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $ram_id)) . " unidades\n";
echo "   - GMC: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $gmc_id)) . " unidades\n";
echo "   - Jeep: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $jeep_id)) . " unidades\n";
echo "   - Mazda: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $mazda_id)) . " unidades\n";
echo "   - Buick: " . count(array_filter($vehiculos, fn($v) => $v['marca_id'] == $buick_id)) . " unidades\n";
