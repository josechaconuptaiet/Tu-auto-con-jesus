<?php
require_once __DIR__ . '/api/db_connect.php';

echo "=== FETCHING REAL CAR IMAGES ===\n\n";

function downloadImage($url, $savePath) {
    $ctx = stream_context_create(['http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false) return false;
    $info = getimagesizefromstring($data);
    if ($info === false) return false;
    file_put_contents($savePath, $data);
    return true;
}

function getWikipediaImage($searchTerm) {
    $api = "https://en.wikipedia.org/w/api.php?action=query&titles=" . urlencode($searchTerm) . "&prop=pageimages&format=json&pithumbsize=1200";
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0']]);
    $resp = @file_get_contents($api, false, $ctx);
    if ($resp === false) return null;
    $data = json_decode($resp, true);
    if (!isset($data['query']['pages'])) return null;
    foreach ($data['query']['pages'] as $page) {
        if (isset($page['thumbnail']['source'])) {
            return $page['thumbnail']['source'];
        }
    }
    return null;
}

function searchWikipedia($query) {
    $api = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=" . urlencode($query) . "&format=json&srlimit=5";
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0']]);
    $resp = @file_get_contents($api, false, $ctx);
    if ($resp === false) return null;
    $data = json_decode($resp, true);
    if (!isset($data['query']['search'])) return null;
    foreach ($data['query']['search'] as $result) {
        $title = $result['title'];
        $img = getWikipediaImage($title);
        if ($img !== null) return $img;
    }
    return null;
}

function wikipediaSearchHasTitle($searchTerm, $expectedTitle) {
    $api = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=" . urlencode($searchTerm) . "&format=json&srlimit=3";
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0']]);
    $resp = @file_get_contents($api, false, $ctx);
    if ($resp === false) return false;
    $data = json_decode($resp, true);
    if (!isset($data['query']['search'])) return false;
    foreach ($data['query']['search'] as $result) {
        if (stripos($result['title'], $expectedTitle) !== false) return true;
    }
    $img = getWikipediaImage($searchTerm);
    if ($img !== null) return true;
    return false;
}

// Manual Wikipedia page title overrides for models that need specific pages
$modelOverrides = [
    'Toyota RAV4' => 'Toyota RAV4',
    'Toyota Camry' => 'Toyota Camry',
    'Toyota Corolla' => 'Toyota Corolla',
    'Toyota Tacoma' => 'Toyota Tacoma',
    'Toyota Corolla Cross' => 'Toyota Corolla Cross',
    'Toyota Highlander' => 'Toyota Highlander',
    'Toyota Grand Highlander' => 'Toyota Grand Highlander',
    'Toyota Sienna' => 'Toyota Sienna',
    'Toyota Tundra' => 'Toyota Tundra',
    'Toyota 4Runner' => 'Toyota 4Runner',
    'Toyota Sequoia' => 'Toyota Sequoia',
    'Chevrolet Silverado' => 'Chevrolet Silverado',
    'Chevrolet Equinox' => 'Chevrolet Equinox',
    'Chevrolet Trax' => 'Chevrolet Trax',
    'Chevrolet Traverse' => 'Chevrolet Traverse',
    'Chevrolet Tahoe' => 'Chevrolet Tahoe',
    'Chevrolet Suburban' => 'Chevrolet Suburban',
    'Honda CR-V' => 'Honda CR-V',
    'Honda Civic' => 'Honda Civic',
    'Honda Accord' => 'Honda Accord',
    'Honda HR-V' => 'Honda HR-V',
    'Honda Pilot' => 'Honda Pilot',
    'Honda Passport' => 'Honda Passport',
    'RAM Ram 1500' => 'Ram Pickup',
    'RAM Ram 2500' => 'Ram Pickup',
    'RAM Ram 3500' => 'Ram Pickup',
    'RAM Ram ProMaster' => 'Ram ProMaster',
    'GMC Sierra 1500' => 'GMC Sierra',
    'GMC Terrain' => 'GMC Terrain',
    'GMC Acadia' => 'GMC Acadia',
    'GMC Yukon' => 'GMC Yukon',
    'GMC Canyon' => 'GMC Canyon',
    'Jeep Wrangler' => 'Jeep Wrangler',
    'Jeep Gladiator' => 'Jeep Gladiator (JT)',
    'Jeep Cherokee' => 'Jeep Cherokee (KL)',
    'Jeep Grand Cherokee' => 'Jeep Grand Cherokee',
    'Jeep Compass' => 'Jeep Compass',
    'Mazda CX-5' => 'Mazda CX-5',
    'Mazda CX-30' => 'Mazda CX-30',
    'Mazda CX-50' => 'Mazda CX-50',
    'Mazda CX-90' => 'Mazda CX-90',
    'Mazda Mazda3' => 'Mazda3',
    'Buick Encore GX' => 'Buick Encore GX',
    'Buick Envision' => 'Buick Envision',
    'Buick Enclave' => 'Buick Enclave',
    'Buick Envista' => 'Buick Envista',
];

// Get all unique models from cars
$stmt = $pdo->query("SELECT DISTINCT c.modelo, m.nombre as marca FROM cars c JOIN marcas m ON c.marca_id = m.id ORDER BY m.nombre, c.modelo");
$models = $stmt->fetchAll(PDO::FETCH_ASSOC);

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$downloaded = 0;
$failed = [];

echo "Models to process: " . count($models) . "\n\n";

foreach ($models as $model) {
    $marca = $model['marca'];
    $modelo = $model['modelo'];
    $key = "$marca $modelo";
    
    echo "Processing: $key... ";
    
    // Determine search term
    $searchTerm = $modelOverrides[$key] ?? "$marca $modelo";
    
    // Try the override/page title first
    $imgUrl = getWikipediaImage($searchTerm);
    
    // If no image found, try searching
    if ($imgUrl === null) {
        $imgUrl = searchWikipedia("$marca $modelo 2026");
    }
    if ($imgUrl === null) {
        $imgUrl = searchWikipedia("$marca $modelo automobile");
    }
    
    if ($imgUrl === null) {
        echo "NO IMAGE FOUND\n";
        $failed[] = $key;
        continue;
    }
    
    // Generate a unique filename
    $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($key));
    $ext = 'jpg';
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)/i', $imgUrl, $m)) {
        $ext = strtolower($m[1]);
    }
    $filename = $safeKey . '_primary.' . $ext;
    $savePath = $uploadDir . $filename;
    
    // Download the image
    if (!downloadImage($imgUrl, $savePath)) {
        echo "DOWNLOAD FAILED\n";
        $failed[] = $key;
        continue;
    }
    
    $imagePath = 'uploads/' . $filename;
    
    // Update all cars of this model
    $stmtUpd = $pdo->prepare("UPDATE cars c JOIN marcas m ON c.marca_id = m.id SET c.image_path = ? WHERE m.nombre = ? AND c.modelo = ?");
    $stmtUpd->execute([$imagePath, $marca, $modelo]);
    $count = $stmtUpd->rowCount();
    
    echo "OK ($count cars, $imagePath)\n";
    $downloaded++;
    
    // Small delay to be polite to Wikipedia
    usleep(250000);
}

echo "\n=== SUMMARY ===\n";
echo "Images downloaded: $downloaded\n";
if (!empty($failed)) {
    echo "Failed models:\n";
    foreach ($failed as $f) echo "  - $f\n";
}

// For cars that still don't have image_path, set a placeholder
$stmt = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path = ''");
$emptyCars = $stmt->fetchColumn();
if ($emptyCars > 0) {
    echo "\nCars still without image: $emptyCars\n";
}
echo "\nDone!\n";
