<?php
require_once __DIR__ . '/api/db_connect.php';

// Keep image for the FIRST car of each model, clear the rest
$stmt = $pdo->query("SELECT MIN(c.id) as first_id FROM cars c GROUP BY c.marca_id, c.modelo");
$keepIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Clear all except the first ones
$placeholders = implode(',', array_fill(0, count($keepIds), '?'));
$pdo->prepare("UPDATE cars SET image_path = '' WHERE id NOT IN ($placeholders)")->execute($keepIds);

$with = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NOT NULL AND image_path != ''")->fetchColumn();
$empty = $pdo->query("SELECT COUNT(*) FROM cars WHERE image_path IS NULL OR image_path = ''")->fetchColumn();

echo "Autos CON imagen: $with/188\n";
echo "Autos SIN imagen: $empty/188\n";
echo "\n✅ Listo. Las $with imágenes de Wikipedia están en uploads/. ";
echo "Los $empty autos sin imagen los puedes añadir manualmente desde el admin.\n";
