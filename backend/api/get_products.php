<?php
require_once __DIR__ . '/config.php';
setHeaders();

$db   = getDB();
$cat  = $_GET['cat'] ?? '';
$id   = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare('SELECT id, name, cat, price, old_price AS old, img, img2, img3, is_new AS isNew FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) fail('Produit introuvable', 404);
    ok(['product' => $product]);
} elseif ($cat) {
    $stmt = $db->prepare('SELECT id, name, cat, price, old_price AS old, img, img2, img3, is_new AS isNew FROM products WHERE cat = ? ORDER BY created_at DESC');
    $stmt->execute([$cat]);
    ok(['products' => $stmt->fetchAll()]);
} else {
    $stmt = $db->query('SELECT id, name, cat, price, old_price AS old, img, img2, img3, is_new AS isNew FROM products ORDER BY created_at DESC');
    ok(['products' => $stmt->fetchAll()]);
}
