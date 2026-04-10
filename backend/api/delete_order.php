<?php
// api/delete_order.php — Delete an order (admin only)
require_once __DIR__ . '/config.php';
setHeaders();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$ref  = trim($body['order_ref'] ?? '');

if (empty($ref)) fail('Ref commande requise');

$db   = getDB();
$stmt = $db->prepare('DELETE FROM orders WHERE order_ref = ?');
$stmt->execute([$ref]);

if ($stmt->rowCount() === 0) fail('Commande introuvable', 404);

ok(['message' => 'Commande supprimee']);
