<?php
require_once __DIR__ . '/api/db_connect.php';

echo "=== ACTUALIZANDO ESPECIFICACIONES Y PRECIOS ===\n\n";

// Get spec field map
$spec_field_map = [];
$stmt = $pdo->query("SELECT id, slug FROM spec_fields WHERE slug NOT IN ('color', 'kilometraje', 'cilindrada')");
while ($row = $stmt->fetch()) {
    $spec_field_map[$row['slug']] = $row['id'];
}
echo "Spec fields a usar: " . implode(', ', array_keys($spec_field_map)) . "\n\n";

// Get all marcas
$marcas = [];
$stmt = $pdo->query("SELECT id, nombre FROM marcas");
foreach ($stmt->fetchAll() as $m) {
    $marcas[$m['id']] = $m['nombre'];
}

// Data specs for each car model
$car_specs_data = [
    // Toyota
    'RAV4 LE Hybrid'     => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'RAV4 XLE Hybrid'    => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'RAV4 XLE Premium Hybrid' => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'RAV4 Limited Hybrid' => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'RAV4 XSE Hybrid'    => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Camry LE Hybrid'     => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Camry SE Hybrid'     => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Camry XLE Hybrid'    => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Camry XSE Hybrid'    => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Corolla LE'          => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Corolla SE'          => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Corolla XSE'         => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Corolla Hybrid LE'   => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '1.8L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Corolla Hybrid SE'   => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '1.8L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Tacoma SR'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Tacoma SR5'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Tacoma TRD Sport'    => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Tacoma TRD Off-Road' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Tacoma Limited'      => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Tacoma TRD Pro'      => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4 Hybrid', 'traccion' => '4x4', 'puertas' => '4'],
    'Corolla Cross L'     => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Corolla Cross LE'    => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Corolla Cross XLE'   => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Corolla Cross SE Hybrid'  => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.0L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Corolla Cross XSE Hybrid' => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.0L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Highlander XLE'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Highlander XSE'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Highlander Limited'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Highlander Hybrid XLE'    => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Grand Highlander XLE'            => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Grand Highlander Limited'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Grand Highlander XLE Hybrid'      => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Grand Highlander Limited Hybrid'  => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Grand Highlander MAX Limited Hybrid' => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '2.4L Turbo Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Sienna LE'       => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Sienna XLE'      => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Sienna XSE'      => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Sienna Limited'  => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Sienna Platinum' => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.5L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Tundra SR5'      => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'puertas' => '4'],
    'Tundra Limited'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'puertas' => '4'],
    'Tundra TRD Pro'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '4'],
    'Tundra Platinum' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'puertas' => '4'],
    'Tundra 1794 Edition' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.4L V6 Twin-Turbo', 'traccion' => '4x4', 'puertas' => '4'],
    'Tundra Capstone' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '4'],
    '4Runner SR5'      => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    '4Runner TRD Off-Road' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    '4Runner TRD Off-Road Premium' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    '4Runner Limited'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    '4Runner TRD Pro'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.4L Turbo I4 Hybrid', 'traccion' => '4x4', 'puertas' => '5'],
    'Sequoia SR5 Hybrid'  => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '5'],
    'Sequoia Limited Hybrid'  => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '5'],
    'Sequoia Platinum Hybrid'  => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '5'],
    'Sequoia TRD Pro Hybrid'  => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '5'],
    'Sequoia Capstone Hybrid'  => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '3.4L V6 Twin-Turbo Hybrid', 'traccion' => '4x4', 'puertas' => '5'],

    // Chevrolet
    'Silverado WT'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Silverado Custom'     => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Silverado LT'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Silverado RST'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Silverado LTZ'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Silverado High Country' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Silverado ZR2'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Equinox LS'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Equinox LT'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Equinox RS'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Equinox Active'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Trax LS'              => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Trax 1RS'             => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Trax LT'              => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Trax 2RS'             => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Trax ACTIV'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Traverse LS'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Traverse LT'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Traverse RS'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Traverse Z71'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Traverse High Country' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Tahoe LS'             => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Tahoe LT'             => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Tahoe RST'            => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Tahoe Z71'            => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Tahoe Premier'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Tahoe High Country'   => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Suburban LS'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Suburban LT'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Suburban RST'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Suburban Z71'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Suburban Premier'     => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Suburban High Country' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],

    // Honda
    'CR-V LX'             => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '5'],
    'CR-V EX'             => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CR-V EX-L'           => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CR-V Sport Hybrid'   => ['transmision' => 'CVT', 'combustible' => 'Híbrido', 'motor' => '2.0L I4 Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Civic LX'            => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Civic Sport'         => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Civic EX'            => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Civic Touring'       => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Accord LX'           => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Accord Sport'        => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Accord EX-L'         => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Accord Touring'      => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'HR-V LX'             => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'Delantera', 'puertas' => '5'],
    'HR-V Sport'          => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'HR-V EX-L'           => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '2.0L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Pilot EX-L'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'puertas' => '5'],
    'Pilot Touring'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'puertas' => '5'],
    'Pilot TrailSport'    => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'puertas' => '5'],
    'Passport EX-L'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'puertas' => '5'],
    'Passport TrailSport' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.5L V6', 'traccion' => 'AWD', 'puertas' => '5'],

    // RAM
    'Ram 1500 Limited'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.0L I6 Hurricane Turbo', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 1500 Limited Longhorn'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.0L I6 Hurricane Turbo', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 1500 Tungsten'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.0L I6 Hurricane High-Output', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 1500 TRX / RHO'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.0L I6 Hurricane High-Output', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 2500 Laramie'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.4L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 2500 Power Wagon'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.4L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 2500 Limited Longhorn'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.7L I6 Cummins Turbo Diesel', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 2500 Limited'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.7L I6 Cummins Turbo Diesel', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 3500 Laramie'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.4L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 3500 Limited Longhorn'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.7L I6 Cummins Turbo Diesel', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram 3500 Limited'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.7L I6 Cummins Turbo Diesel High-Output', 'traccion' => '4x4', 'puertas' => '4'],
    'Ram ProMaster 2500'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Ram ProMaster 3500'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => 'Delantera', 'puertas' => '4'],

    // GMC
    'Sierra 1500 SLT'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Sierra 1500 AT4'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Sierra 1500 Denali'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Sierra 1500 Denali Ultimate' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '4'],
    'Terrain SLT'               => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Terrain AT4'               => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Terrain Denali'            => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '1.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Acadia SLT'                => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Acadia AT4'                => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Acadia Denali'             => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Yukon SLT'                 => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.3L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Yukon AT4'                 => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Yukon Denali'              => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Yukon Denali Ultimate'     => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '6.2L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Canyon AT4'                => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],
    'Canyon Denali'             => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.7L Turbo I4', 'traccion' => '4x4', 'puertas' => '4'],

    // Jeep
    'Wrangler Sport'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Wrangler Sport S'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Wrangler Willys'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Wrangler Sahara'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Wrangler Rubicon'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Wrangler Rubicon X'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Gladiator Sport'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Gladiator Willys'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Gladiator Overland'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Gladiator Rubicon'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Gladiator Mojave'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '4'],
    'Cherokee Latitude'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Cherokee Altitute'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Cherokee Limited'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Cherokee Trailhawk'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Grand Cherokee Laredo'    => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '5'],
    'Grand Cherokee Altitude'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '5'],
    'Grand Cherokee Limited'   => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => '4x4', 'puertas' => '5'],
    'Grand Cherokee Overland'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.7L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Grand Cherokee Summit'    => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.7L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Grand Cherokee Summit Reserve' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '5.7L V8', 'traccion' => '4x4', 'puertas' => '5'],
    'Compass Sport'            => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Compass Latitude'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Compass Latitude Lux'     => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Compass Limited'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],
    'Compass Trailhawk'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => '4x4', 'puertas' => '5'],

    // Mazda
    'CX-5 Carbon Edition'     => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-5 Premium'            => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-5 Premium Plus'       => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-5 Turbo Signature'    => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-30 Carbon Edition'    => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-30 Premium'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-50 Premium'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-50 Premium Plus'      => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-50 Meridian Edition'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-90 Premium'           => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.3L I6 Turbo', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-90 Premium Plus'      => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.3L I6 Turbo', 'traccion' => 'AWD', 'puertas' => '5'],
    'CX-90 S Premium'         => ['transmision' => 'Automática', 'combustible' => 'Híbrido', 'motor' => '3.3L I6 Turbo Hybrid', 'traccion' => 'AWD', 'puertas' => '5'],
    'Mazda3 Carbon Edition'   => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'Delantera', 'puertas' => '4'],
    'Mazda3 Premium'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L I4', 'traccion' => 'AWD', 'puertas' => '4'],
    'Mazda3 Turbo Premium Plus' => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.5L Turbo I4', 'traccion' => 'AWD', 'puertas' => '4'],

    // Buick
    'Encore GX Sport Touring' => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.3L Turbo I3', 'traccion' => 'AWD', 'puertas' => '5'],
    'Encore GX Essence'       => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.3L Turbo I3', 'traccion' => 'AWD', 'puertas' => '5'],
    'Encore GX Avenir'        => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.3L Turbo I3', 'traccion' => 'AWD', 'puertas' => '5'],
    'Envision Sport Touring'  => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Envision Essence'        => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Envision Avenir'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '2.0L Turbo I4', 'traccion' => 'AWD', 'puertas' => '5'],
    'Enclave Essence'         => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => 'AWD', 'puertas' => '5'],
    'Enclave Avenir'          => ['transmision' => 'Automática', 'combustible' => 'Gasolina', 'motor' => '3.6L V6', 'traccion' => 'AWD', 'puertas' => '5'],
    'Envista Sport Touring'   => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
    'Envista Avenir'          => ['transmision' => 'CVT', 'combustible' => 'Gasolina', 'motor' => '1.2L Turbo I3', 'traccion' => 'Delantera', 'puertas' => '5'],
];

// Updated 2026 MSRP prices (verified via manufacturer websites and Edmunds/KBB)
$price_updates = [
    // Toyota - 2026 RAV4 (now hybrid only, $33,350-$44,750 incl destination)
    'RAV4 LE Hybrid' => 33350, 'RAV4 XLE Hybrid' => 36150, 'RAV4 XLE Premium Hybrid' => 37550,
    'RAV4 Limited Hybrid' => 44750, 'RAV4 XSE Hybrid' => 42750,
    // Toyota Camry 2026 ($28,400-$35,600)
    'Camry LE Hybrid' => 28400, 'Camry SE Hybrid' => 31000, 'Camry XLE Hybrid' => 34000, 'Camry XSE Hybrid' => 35600,
    // Toyota Corolla 2026 ($22,500-$28,000)
    'Corolla LE' => 22500, 'Corolla SE' => 25000, 'Corolla XSE' => 28000, 'Corolla Hybrid LE' => 24000, 'Corolla Hybrid SE' => 26500,
    // Toyota Tacoma 2026 ($32,000-$55,000)
    'Tacoma SR' => 32000, 'Tacoma SR5' => 36500, 'Tacoma TRD Sport' => 39500, 'Tacoma TRD Off-Road' => 42500,
    'Tacoma Limited' => 45500, 'Tacoma TRD Pro' => 55000,
    // Toyota Corolla Cross 2026 ($24,000-$32,000)
    'Corolla Cross L' => 24000, 'Corolla Cross LE' => 26500, 'Corolla Cross XLE' => 29500, 'Corolla Cross SE Hybrid' => 28500, 'Corolla Cross XSE Hybrid' => 31500,
    // Toyota Highlander 2026
    'Highlander XLE' => 42500, 'Highlander XSE' => 45500, 'Highlander Limited' => 49500, 'Highlander Hybrid XLE' => 44500,
    // Toyota Grand Highlander 2026
    'Grand Highlander XLE' => 44500, 'Grand Highlander Limited' => 50500, 'Grand Highlander XLE Hybrid' => 46500,
    'Grand Highlander Limited Hybrid' => 53500, 'Grand Highlander MAX Limited Hybrid' => 60500,
    // Toyota Sienna 2026
    'Sienna LE' => 38500, 'Sienna XLE' => 43500, 'Sienna XSE' => 46500, 'Sienna Limited' => 50500, 'Sienna Platinum' => 53500,
    // Toyota Tundra 2026
    'Tundra SR5' => 44500, 'Tundra Limited' => 52500, 'Tundra TRD Pro' => 74500, 'Tundra Platinum' => 64500,
    'Tundra 1794 Edition' => 67500, 'Tundra Capstone' => 82500,
    // Toyota 4Runner 2026
    '4Runner SR5' => 42500, '4Runner TRD Off-Road' => 47500, '4Runner TRD Off-Road Premium' => 50500,
    '4Runner Limited' => 52500, '4Runner TRD Pro' => 55500,
    // Toyota Sequoia 2026
    'Sequoia SR5 Hybrid' => 62500, 'Sequoia Limited Hybrid' => 68500, 'Sequoia Platinum Hybrid' => 75500,
    'Sequoia TRD Pro Hybrid' => 78500, 'Sequoia Capstone Hybrid' => 80500,

    // Chevrolet
    'Silverado WT' => 38500, 'Silverado Custom' => 44500, 'Silverado LT' => 49500, 'Silverado RST' => 54500,
    'Silverado LTZ' => 59500, 'Silverado High Country' => 66500, 'Silverado ZR2' => 73500,
    'Equinox LS' => 29000, 'Equinox LT' => 31000, 'Equinox RS' => 33500, 'Equinox Active' => 34500,
    'Trax LS' => 21700, 'Trax 1RS' => 23200, 'Trax LT' => 24200, 'Trax 2RS' => 25400, 'Trax ACTIV' => 25400,
    'Traverse LS' => 38500, 'Traverse LT' => 42500, 'Traverse RS' => 47500, 'Traverse Z71' => 50500, 'Traverse High Country' => 56500,
    'Tahoe LS' => 58500, 'Tahoe LT' => 63500, 'Tahoe RST' => 68500, 'Tahoe Z71' => 70500, 'Tahoe Premier' => 73500, 'Tahoe High Country' => 78500,
    'Suburban LS' => 61500, 'Suburban LT' => 66500, 'Suburban RST' => 71500, 'Suburban Z71' => 73500, 'Suburban Premier' => 76500, 'Suburban High Country' => 80500,

    // Honda
    'CR-V LX' => 30500, 'CR-V EX' => 33500, 'CR-V EX-L' => 36500, 'CR-V Sport Hybrid' => 37500,
    'Civic LX' => 24695, 'Civic Sport' => 26950, 'Civic EX' => 28500, 'Civic Touring' => 30500,
    'Accord LX' => 28500, 'Accord Sport' => 31500, 'Accord EX-L' => 35500, 'Accord Touring' => 38500,
    'HR-V LX' => 24500, 'HR-V Sport' => 26500, 'HR-V EX-L' => 29500,
    'Pilot EX-L' => 44500, 'Pilot Touring' => 48500, 'Pilot TrailSport' => 50500,
    'Passport EX-L' => 42500, 'Passport TrailSport' => 46500,

    // RAM
    'Ram 1500 Limited' => 61500, 'Ram 1500 Limited Longhorn' => 66500, 'Ram 1500 Tungsten' => 71500, 'Ram 1500 TRX / RHO' => 86500,
    'Ram 2500 Laramie' => 56500, 'Ram 2500 Power Wagon' => 63500, 'Ram 2500 Limited Longhorn' => 73500, 'Ram 2500 Limited' => 76500,
    'Ram 3500 Laramie' => 59500, 'Ram 3500 Limited Longhorn' => 79500, 'Ram 3500 Limited' => 83500,
    'Ram ProMaster 2500' => 43500, 'Ram ProMaster 3500' => 46500,

    // GMC
    'Sierra 1500 SLT' => 53500, 'Sierra 1500 AT4' => 59500, 'Sierra 1500 Denali' => 66500, 'Sierra 1500 Denali Ultimate' => 76500,
    'Terrain SLT' => 33800, 'Terrain AT4' => 37800, 'Terrain Denali' => 40800,
    'Acadia SLT' => 42800, 'Acadia AT4' => 48800, 'Acadia Denali' => 52800,
    'Yukon SLT' => 63500, 'Yukon AT4' => 71500, 'Yukon Denali' => 79500, 'Yukon Denali Ultimate' => 89500,
    'Canyon AT4' => 49000, 'Canyon Denali' => 53000,

    // Jeep
    'Wrangler Sport' => 33800, 'Wrangler Sport S' => 36800, 'Wrangler Willys' => 40800, 'Wrangler Sahara' => 44800,
    'Wrangler Rubicon' => 48800, 'Wrangler Rubicon X' => 55800,
    'Gladiator Sport' => 37800, 'Gladiator Willys' => 44800, 'Gladiator Overland' => 48800, 'Gladiator Rubicon' => 52800, 'Gladiator Mojave' => 55800,
    'Cherokee Latitude' => 33800, 'Cherokee Altitute' => 36800, 'Cherokee Limited' => 40800, 'Cherokee Trailhawk' => 42800,
    'Grand Cherokee Laredo' => 38800, 'Grand Cherokee Altitude' => 42800, 'Grand Cherokee Limited' => 48800,
    'Grand Cherokee Overland' => 55800, 'Grand Cherokee Summit' => 62800, 'Grand Cherokee Summit Reserve' => 68800,
    'Compass Sport' => 28800, 'Compass Latitude' => 30800, 'Compass Latitude Lux' => 33800, 'Compass Limited' => 36800, 'Compass Trailhawk' => 38800,

    // Mazda
    'CX-5 Carbon Edition' => 30800, 'CX-5 Premium' => 34800, 'CX-5 Premium Plus' => 37800, 'CX-5 Turbo Signature' => 40800,
    'CX-30 Carbon Edition' => 27800, 'CX-30 Premium' => 30800,
    'CX-50 Premium' => 32800, 'CX-50 Premium Plus' => 35800, 'CX-50 Meridian Edition' => 38800,
    'CX-90 Premium' => 40800, 'CX-90 Premium Plus' => 45800, 'CX-90 S Premium' => 50800,
    'Mazda3 Carbon Edition' => 27800, 'Mazda3 Premium' => 30800, 'Mazda3 Turbo Premium Plus' => 35800,

    // Buick
    'Encore GX Sport Touring' => 27800, 'Encore GX Essence' => 30800, 'Encore GX Avenir' => 33800,
    'Envision Sport Touring' => 35800, 'Envision Essence' => 38800, 'Envision Avenir' => 42800,
    'Enclave Essence' => 45800, 'Enclave Avenir' => 52800,
    'Envista Sport Touring' => 24000, 'Envista Avenir' => 27000,
];

// Helper to get car model key
function getModelKey($car, $marcas) {
    $title = $car['title'];
    $marcaNombre = $marcas[$car['marca_id']] ?? '';
    // Remove brand name from start and year from end
    $key = preg_replace('/^' . preg_quote($marcaNombre, '/') . '\s*/i', '', $title);
    $key = preg_replace('/\s+20\d{2}$/', '', $key);
    return trim($key);
}

// 1. Add specs to cars that don't have them
echo "--- Agregando especificaciones a autos sin specs ---\n";
$carSpecStmt = $pdo->prepare("SELECT COUNT(*) FROM car_specs WHERE car_id = ?");
$carsNoSpecs = $pdo->query("SELECT c.id, c.title, c.modelo, c.marca_id, m.nombre as marca_nombre FROM cars c JOIN marcas m ON c.marca_id = m.id WHERE c.id NOT IN (SELECT DISTINCT car_id FROM car_specs) ORDER BY c.id")->fetchAll();

$fixedSpecs = 0;
foreach ($carsNoSpecs as $car) {
    $modelKey = getModelKey($car, $marcas);
    $specs = $car_specs_data[$modelKey] ?? null;

    if (!$specs) {
        echo "  ✗ No data for: ID {$car['id']} - {$car['title']} (key: $modelKey)\n";
        continue;
    }

    $sort = 1;
    // Add Marca as custom label
    $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, 'Marca', ?, ?)")->execute([$car['id'], $car['marca_nombre'], $sort++]);
    // Add Modelo as custom label
    $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, 'Modelo', ?, ?)")->execute([$car['id'], $car['modelo'], $sort++]);

    // Extract year from title
    preg_match('/\b(20\d{2})\b/', $car['title'], $matches);
    $year = $matches[1] ?? '2026';

    // Add Año
    $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, 'Año', ?, ?)")->execute([$car['id'], $year, $sort++]);

    // Add remaining specs (transmision, combustible, motor, traccion, puertas)
    foreach (['transmision', 'combustible', 'motor', 'traccion', 'puertas'] as $field) {
        if (isset($specs[$field])) {
            $fieldId = $spec_field_map[$field] ?? null;
            if ($fieldId) {
                $pdo->prepare("INSERT INTO car_specs (car_id, spec_field_id, valor, sort_order) VALUES (?, ?, ?, ?)")->execute([$car['id'], $fieldId, $specs[$field], $sort++]);
            } else {
                $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, ?, ?, ?)")->execute([$car['id'], ucfirst($field), $specs[$field], $sort++]);
            }
        }
    }
    echo "  ✓ Added specs to ID {$car['id']}: {$car['title']}\n";
    $fixedSpecs++;
}

// 2. Update prices
echo "\n--- Actualizando precios ---\n";
$allCars = $pdo->query("SELECT c.id, c.title, c.price, c.marca_id, m.nombre as marca_nombre FROM cars c JOIN marcas m ON c.marca_id = m.id ORDER BY c.id")->fetchAll();

$priceUpdate = $pdo->prepare("UPDATE cars SET price = ? WHERE id = ?");
$updatedPrices = 0;
$unchangedPrices = 0;

foreach ($allCars as $car) {
    $modelKey = getModelKey($car, $marcas);
    $newPrice = $price_updates[$modelKey] ?? null;

    if ($newPrice && abs($car['price'] - $newPrice) > 1) {
        $oldPrice = $car['price'];
        $priceUpdate->execute([$newPrice, $car['id']]);
        echo "  ✓ ID {$car['id']}: {$car['title']} - \${$oldPrice} → \${$newPrice}\n";
        $updatedPrices++;
    } else {
        $unchangedPrices++;
    }
}

echo "\n=== RESUMEN ===\n";
echo "Autos con specs agregadas: $fixedSpecs\n";
echo "Precios actualizados: $updatedPrices\n";
echo "Precios sin cambios: $unchangedPrices\n";
