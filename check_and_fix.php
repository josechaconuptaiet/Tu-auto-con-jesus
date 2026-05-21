<?php
require_once __DIR__ . '/api/db_connect.php';

// Check which image files referenced by DB actually exist
$stmt = $pdo->query("SELECT DISTINCT image_path FROM cars WHERE image_path IS NOT NULL AND image_path != ''");
$files = $stmt->fetchAll(PDO::FETCH_COLUMN);
$missing = [];
foreach ($files as $f) {
    if (!file_exists(__DIR__ . '/' . $f)) {
        $missing[] = $f;
    }
}

if ($missing) {
    echo "MISSING FILES (" . count($missing) . "):\n";
    foreach ($missing as $f) {
        $pdo->prepare("UPDATE cars SET image_path = '' WHERE image_path = ?")->execute([$f]);
        echo "  Cleared: $f\n";
    }
} else {
    echo "ALL IMAGE FILES EXIST!\n";
}

$with = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NOT NULL AND image_path != ''")->fetchColumn();
$empty = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NULL OR image_path = ''")->fetchColumn();
echo "\nAutos con imagen: $with / 188\n";
echo "Autos sin imagen: $empty / 188\n";

// Now try to get images for the missing 4 models
echo "\n=== INTENTAR 4 MODELOS FALTANTES ===\n\n";

$missingModels = [
    ['marca' => 'GMC', 'modelo' => 'Canyon', 'pages' => ['GMC Canyon', 'Chevrolet Colorado', 'GMC Canyon (2015)', 'GMC Canyon (third generation)']],
    ['marca' => 'GMC', 'modelo' => 'Sierra 1500', 'pages' => ['GMC Sierra', 'GMC Sierra (fifth generation)', 'GMC Sierra 1500', 'Chevrolet Silverado']],
    ['marca' => 'GMC', 'modelo' => 'Yukon', 'pages' => ['GMC Yukon', 'Chevrolet Tahoe', 'GMC Yukon (2021)']],
    ['marca' => 'RAM', 'modelo' => 'Ram ProMaster', 'pages' => ['Ram ProMaster', 'Fiat Ducato', 'Ram Promaster', 'Ram ProMaster City']],
];

function getThumb($pageTitle) {
    $api = "https://en.wikipedia.org/w/api.php?action=query&titles=" . urlencode($pageTitle) . "&prop=pageimages&format=json&pithumbsize=960";
    $ch = curl_init($api);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    if (!$resp) return null;
    $data = json_decode($resp, true);
    foreach ($data['query']['pages'] ?? [] as $page) {
        if (!isset($page['missing']) && isset($page['thumbnail']['source'])) {
            return $page['thumbnail']['source'];
        }
    }
    return null;
}

$downloaded = 0;
foreach ($missingModels as $m) {
    $marca = $m['marca'];
    $modelo = $m['modelo'];
    echo "$marca $modelo...\n";
    
    $thumbUrl = null;
    foreach ($m['pages'] as $page) {
        echo "  Trying page: $page... ";
        $thumbUrl = getThumb($page);
        if ($thumbUrl) { echo "FOUND\n"; break; }
        echo "no image\n";
        usleep(300000);
    }
    
    if (!$thumbUrl) {
        echo "  NO IMAGE FROM ANY PAGE\n";
        continue;
    }
    
    $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($marca . '_' . $modelo));
    $filename = $safeKey . '_primary.jpg';
    $savePath = __DIR__ . '/uploads/' . $filename;
    
    $ch = curl_init($thumbUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FAILONERROR => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $imgData = curl_exec($ch);
    curl_close($ch);
    
    if ($imgData === false || strlen($imgData) < 500) {
        echo "  DOWNLOAD FAILED\n";
        continue;
    }
    
    file_put_contents($savePath, $imgData);
    
    $imagePath = 'uploads/' . $filename;
    $stmt = $pdo->prepare("UPDATE cars c JOIN marcas m ON c.marca_id = m.id SET c.image_path = ? WHERE m.nombre = ? AND c.modelo = ?");
    $stmt->execute([$imagePath, $marca, $modelo]);
    echo "  OK (" . $stmt->rowCount() . " cars)\n";
    $downloaded++;
    
    usleep(300000);
}

echo "\nNuevos descargados: $downloaded\n";

// Final
$with = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NOT NULL AND image_path != ''")->fetchColumn();
$empty = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NULL OR image_path = ''")->fetchColumn();
echo "Autos con imagen: $with / 188\n";
echo "Autos sin imagen: $empty / 188\n";
