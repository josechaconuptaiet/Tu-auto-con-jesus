<?php
require_once __DIR__ . '/api/db_connect.php';

echo "=== ASIGNAR IMÁGENES EXISTENTES A LA DB ===\n\n";

// Map filenames to (marca, modelo) in DB
$imageMap = [
    'buick_enclave_primary.jpg' => ['marca' => 'Buick', 'modelo' => 'Enclave'],
    'buick_encore_gx_primary.jpg' => ['marca' => 'Buick', 'modelo' => 'Encore GX'],
    'buick_envision_primary.jpg' => ['marca' => 'Buick', 'modelo' => 'Envision'],
    'buick_envista_primary.jpg' => ['marca' => 'Buick', 'modelo' => 'Envista'],
    'chevrolet_equinox_primary.jpg' => ['marca' => 'Chevrolet', 'modelo' => 'Equinox'],
    'chevrolet_silverado_primary.jpg' => ['marca' => 'Chevrolet', 'modelo' => 'Silverado'],
    'chevrolet_suburban_primary.jpg' => ['marca' => 'Chevrolet', 'modelo' => 'Suburban'],
    'chevrolet_tahoe_primary.jpg' => ['marca' => 'Chevrolet', 'modelo' => 'Tahoe'],
    'chevrolet_traverse_primary.jpg' => ['marca' => 'Chevrolet', 'modelo' => 'Traverse'],
    'chevrolet_trax_primary.jpg' => ['marca' => 'Chevrolet', 'modelo' => 'Trax'],
    'gmc_acadia_primary.jpg' => ['marca' => 'GMC', 'modelo' => 'Acadia'],
    'gmc_terrain_primary.jpg' => ['marca' => 'GMC', 'modelo' => 'Terrain'],
    'honda_accord_primary.jpg' => ['marca' => 'Honda', 'modelo' => 'Accord'],
    'honda_civic_primary.jpg' => ['marca' => 'Honda', 'modelo' => 'Civic'],
    'honda_cr-v_primary.jpg' => ['marca' => 'Honda', 'modelo' => 'CR-V'],
    'honda_hr-v_primary.jpg' => ['marca' => 'Honda', 'modelo' => 'HR-V'],
    'honda_passport_primary.jpg' => ['marca' => 'Honda', 'modelo' => 'Passport'],
    'honda_pilot_primary.jpg' => ['marca' => 'Honda', 'modelo' => 'Pilot'],
    'jeep_cherokee_primary.jpg' => ['marca' => 'Jeep', 'modelo' => 'Cherokee'],
    'jeep_gladiator_primary.jpg' => ['marca' => 'Jeep', 'modelo' => 'Gladiator'],
    'jeep_grand_cherokee_primary.jpg' => ['marca' => 'Jeep', 'modelo' => 'Grand Cherokee'],
    'jeep_wrangler_primary.jpg' => ['marca' => 'Jeep', 'modelo' => 'Wrangler'],
    'mazda_cx-30_primary.jpg' => ['marca' => 'Mazda', 'modelo' => 'CX-30'],
    'mazda_cx-5_primary.jpg' => ['marca' => 'Mazda', 'modelo' => 'CX-5'],
    'mazda_cx-50_primary.jpg' => ['marca' => 'Mazda', 'modelo' => 'CX-50'],
    'mazda_cx-90_primary.jpg' => ['marca' => 'Mazda', 'modelo' => 'CX-90'],
    'mazda_mazda3_primary.jpg' => ['marca' => 'Mazda', 'modelo' => 'Mazda3'],
    'ram_ram_1500_primary.jpg' => ['marca' => 'RAM', 'modelo' => 'Ram 1500'],
    'toyota_4runner_primary.jpg' => ['marca' => 'Toyota', 'modelo' => '4Runner'],
    'toyota_camry_primary.jpg' => ['marca' => 'Toyota', 'modelo' => 'Camry'],
    'toyota_corolla_primary.jpg' => ['marca' => 'Toyota', 'modelo' => 'Corolla'],
    'toyota_corolla_cross_primary.jpg' => ['marca' => 'Toyota', 'modelo' => 'Corolla Cross'],
    'toyota_grand_highlander_primary.jpg' => ['marca' => 'Toyota', 'modelo' => 'Grand Highlander'],
];

$updated = 0;
foreach ($imageMap as $filename => $info) {
    $imagePath = 'uploads/' . $filename;
    $filePath = __DIR__ . '/' . $imagePath;
    if (!file_exists($filePath)) {
        echo "  FILE NOT FOUND: $filename\n";
        continue;
    }
    
    $stmt = $pdo->prepare("UPDATE cars c JOIN marcas m ON c.marca_id = m.id SET c.image_path = ? WHERE m.nombre = ? AND c.modelo = ?");
    $stmt->execute([$imagePath, $info['marca'], $info['modelo']]);
    $count = $stmt->rowCount();
    echo "  {$info['marca']} {$info['modelo']} -> $filename ($count cars)\n";
    $updated += $count;
}

echo "\nTotal asignados: $updated cars\n\n";

// --- MISSING MODELS: try to fetch the remaining 13 ---
echo "=== DESCARGAR MODELOS FALTANTES ===\n\n";

$missingPages = [
    ['marca' => 'GMC', 'modelo' => 'Canyon', 'page' => 'GMC Canyon'],
    ['marca' => 'GMC', 'modelo' => 'Sierra 1500', 'page' => 'GMC Sierra'],
    ['marca' => 'GMC', 'modelo' => 'Yukon', 'page' => 'GMC Yukon'],
    ['marca' => 'Jeep', 'modelo' => 'Compass', 'page' => 'Jeep Compass'],
    ['marca' => 'RAM', 'modelo' => 'Ram 2500', 'page' => 'Ram pickup'],
    ['marca' => 'RAM', 'modelo' => 'Ram 3500', 'page' => 'Ram pickup'],
    ['marca' => 'RAM', 'modelo' => 'Ram ProMaster', 'page' => 'Ram ProMaster'], 
    ['marca' => 'Toyota', 'modelo' => 'Highlander', 'page' => 'Toyota Highlander'],
    ['marca' => 'Toyota', 'modelo' => 'RAV4', 'page' => 'Toyota RAV4'],
    ['marca' => 'Toyota', 'modelo' => 'Sequoia', 'page' => 'Toyota Sequoia'],
    ['marca' => 'Toyota', 'modelo' => 'Sienna', 'page' => 'Toyota Sienna'],
    ['marca' => 'Toyota', 'modelo' => 'Tacoma', 'page' => 'Toyota Tacoma'],
    ['marca' => 'Toyota', 'modelo' => 'Tundra', 'page' => 'Toyota Tundra'],
];

$downloaded = 0;
foreach ($missingPages as $m) {
    $marca = $m['marca'];
    $modelo = $m['modelo'];
    $page = $m['page'];
    
    echo "  $marca $modelo (page: $page)... ";
    
    // Try pageimages API
    $api = "https://en.wikipedia.org/w/api.php?action=query&titles=" . urlencode($page) . "&prop=pageimages|extracts&format=json&pithumbsize=960&exintro&explaintext";
    
    $ch = curl_init($api);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    
    $thumbUrl = null;
    if ($resp) {
        $data = json_decode($resp, true);
        foreach ($data['query']['pages'] ?? [] as $pageData) {
            if (!isset($pageData['missing']) && isset($pageData['thumbnail']['source'])) {
                $thumbUrl = $pageData['thumbnail']['source'];
            }
        }
    }
    
    if (!$thumbUrl) {
        echo "NO IMAGE\n";
        continue;
    }
    
    $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($marca . '_' . $modelo));
    $filename = $safeKey . '_primary.jpg';
    $savePath = __DIR__ . '/uploads/' . $filename;
    
    $ch2 = curl_init($thumbUrl);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FAILONERROR => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $imgData = curl_exec($ch2);
    curl_close($ch2);
    
    if ($imgData === false || strlen($imgData) < 500) {
        echo "DOWNLOAD FAILED\n";
        continue;
    }
    
    file_put_contents($savePath, $imgData);
    
    $imagePath = 'uploads/' . $filename;
    $stmt = $pdo->prepare("UPDATE cars c JOIN marcas m ON c.marca_id = m.id SET c.image_path = ? WHERE m.nombre = ? AND c.modelo = ?");
    $stmt->execute([$imagePath, $marca, $modelo]);
    $count = $stmt->rowCount();
    echo "OK ($count cars)\n";
    $downloaded++;
    
    usleep(300000);
}

echo "\nNuevos descargados: $downloaded\n";

// Final count
$stmt = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NOT NULL AND image_path != ''");
$withImg = $stmt->fetchColumn();
$total = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
echo "\nAutos CON imagen: $withImg / $total\n";
echo "Autos SIN imagen: " . ($total - $withImg) . "\n";

echo "\n✅ LISTO\n";
