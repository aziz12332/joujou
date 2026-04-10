<?php
// api/delete_product.php — Delete a product (admin only)
require_once __DIR__ . '/config.php';
setHeaders();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = isset($body['id']) ? (int) $body['id'] : 0;

if (!$id) fail('ID invalide');

$db   = getDB();

// If image is a local upload, delete the file too
$stmt = $db->prepare('SELECT img FROM products WHERE id = ?');
$stmt->execute([$id]);
$row  = $stmt->fetch();
if ($row && str_starts_with($row['img'], UPLOAD_URL_BASE)) {
    $file = UPLOAD_DIR . basename($row['img']);
    if (file_exists($file)) unlink($file);
}

$stmt = $db->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

ok(['message' => 'Produit supprime']);
