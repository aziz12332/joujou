<?php
// api/update_order.php — Change order status (admin only)
require_once __DIR__ . '/config.php';
setHeaders();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$ref    = trim($body['order_ref'] ?? '');
$status = trim($body['status']    ?? '');

if (empty($ref))    fail('Ref commande requise');

$allowed = ['new','confirmed','delivered','cancelled'];
if (!in_array($status, $allowed)) fail('Statut invalide');

$db   = getDB();
$stmt = $db->prepare('UPDATE orders SET status = ? WHERE order_ref = ?');
$stmt->execute([$status, $ref]);

if ($stmt->rowCount() === 0) fail('Commande introuvable', 404);

ok(['message' => 'Statut mis a jour']);
