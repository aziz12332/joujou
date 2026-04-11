<?php
require_once __DIR__ . '/config.php';
setHeaders();
requireAdmin();

$isMultipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

if ($isMultipart) {
    $id       = intval($_POST['id']       ?? 0);
    $name     = trim($_POST['name']       ?? '');
    $cat      = trim($_POST['cat']        ?? '');
    $price    = floatval($_POST['price']  ?? 0);
    $oldPrice = $_POST['old_price'] !== '' ? floatval($_POST['old_price']) : null;
    $isNew    = intval($_POST['is_new']   ?? 0);
    $imgUrl   = trim($_POST['img_url']    ?? '');
    $img2Url  = trim($_POST['img2_url']   ?? '');
    $img3Url  = trim($_POST['img3_url']   ?? '');
} else {
    $body     = json_decode(file_get_contents('php://input'), true) ?? [];
    $id       = intval($body['id']        ?? 0);
    $name     = trim($body['name']        ?? '');
    $cat      = trim($body['cat']         ?? '');
    $price    = floatval($body['price']   ?? 0);
    $oldPrice = isset($body['old_price']) && $body['old_price'] !== '' ? floatval($body['old_price']) : null;
    $isNew    = intval($body['is_new']    ?? 0);
    $imgUrl   = trim($body['img_url']     ?? '');
    $img2Url  = trim($body['img2_url']    ?? '');
    $img3Url  = trim($body['img3_url']    ?? '');
}

if (!$name || !$cat || $price <= 0) fail('Champs requis manquants');

$finalImg  = $imgUrl;
$finalImg2 = $img2Url ?: null;
$finalImg3 = $img3Url ?: null;

if ($isMultipart && !empty($_FILES['img_file']['name'])) {
    $file     = $_FILES['img_file'];
    $allowed  = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($file['type'], $allowed)) fail('Type de fichier non autorisé');
    if ($file['size'] > MAX_UPLOAD_SIZE) fail('Fichier trop volumineux');
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_') . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dest)) fail('Erreur upload');
    $finalImg = UPLOAD_URL_BASE . $filename;
}

if (!$finalImg) fail('Image requise');

$db = getDB();
if ($id > 0) {
    $stmt = $db->prepare('UPDATE products SET name=?, cat=?, price=?, old_price=?, img=?, img2=?, img3=?, is_new=? WHERE id=?');
    $stmt->execute([$name, $cat, $price, $oldPrice, $finalImg, $finalImg2, $finalImg3, $isNew, $id]);
    ok(['message' => 'Produit mis à jour']);
} else {
    $stmt = $db->prepare('INSERT INTO products (name,cat,price,old_price,img,img2,img3,is_new) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$name, $cat, $price, $oldPrice, $finalImg, $finalImg2, $finalImg3, $isNew]);
    ok(['id' => $db->lastInsertId(), 'message' => 'Produit ajouté']);
}
