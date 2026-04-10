<?php
// api/save_product.php — Add or edit a product (admin only)
// Supports both URL images and file uploads (multipart/form-data)
require_once __DIR__ . '/config.php';
setHeaders();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

// Read fields — works for both JSON and multipart
$isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart');

if ($isMultipart) {
    $id       = isset($_POST['id'])    ? (int) $_POST['id']    : null;
    $name     = trim($_POST['name']    ?? '');
    $cat      = trim($_POST['cat']     ?? '');
    $price    = (float) ($_POST['price'] ?? 0);
    $oldPrice = isset($_POST['old_price']) && $_POST['old_price'] !== '' ? (float) $_POST['old_price'] : null;
    $imgUrl   = trim($_POST['img_url'] ?? '');
    $isNew    = !empty($_POST['is_new']) && $_POST['is_new'] !== 'false' ? 1 : 0;
} else {
    $body     = json_decode(file_get_contents('php://input'), true) ?? [];
    $id       = isset($body['id'])        ? (int) $body['id']        : null;
    $name     = trim($body['name']        ?? '');
    $cat      = trim($body['cat']         ?? '');
    $price    = (float) ($body['price']   ?? 0);
    $oldPrice = isset($body['old_price']) && $body['old_price'] !== '' ? (float) $body['old_price'] : null;
    $imgUrl   = trim($body['img_url']     ?? '');
    $isNew    = !empty($body['is_new'])   ? 1 : 0;
}

// ── Validation ──
if (empty($name))  fail('Nom requis');
if ($price <= 0)   fail('Prix invalide');
$allowed_cats = ['bagues','colliers','bracelets','boucles'];
if (!in_array($cat, $allowed_cats)) fail('Categorie invalide');

// ── Handle image upload ──
$finalImg = $imgUrl;

if ($isMultipart && !empty($_FILES['img_file']['name'])) {
    $file     = $_FILES['img_file'];
    $maxSize  = MAX_UPLOAD_SIZE;
    $allowed  = ['image/jpeg','image/png','image/webp','image/gif'];

    if ($file['error'] !== UPLOAD_ERR_OK)   fail('Erreur upload fichier');
    if ($file['size'] > $maxSize)           fail('Fichier trop volumineux (max 5 MB)');

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed))         fail('Format non accepte (JPEG, PNG, WebP)');

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_', true) . '.' . strtolower($ext);
    $destPath = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) fail('Impossible de sauvegarder le fichier');

    $finalImg = UPLOAD_URL_BASE . $filename;
}

if (empty($finalImg)) fail('Image requise (URL ou fichier)');

$db = getDB();

if ($id) {
    // UPDATE
    $stmt = $db->prepare('UPDATE products SET name=?, cat=?, price=?, old_price=?, img=?, is_new=? WHERE id=?');
    $stmt->execute([$name, $cat, $price, $oldPrice, $finalImg, $isNew, $id]);
    ok(['message' => 'Produit mis a jour', 'id' => $id]);
} else {
    // INSERT
    $stmt = $db->prepare('INSERT INTO products (name,cat,price,old_price,img,is_new) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$name, $cat, $price, $oldPrice, $finalImg, $isNew]);
    ok(['message' => 'Produit ajoute', 'id' => (int) $db->lastInsertId()]);
}
