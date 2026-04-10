<?php
// api/submit_order.php — Customer places an order (public)
require_once __DIR__ . '/config.php';
setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$firstName = trim($body['firstName'] ?? '');
$lastName  = trim($body['lastName']  ?? '');
$phone     = trim($body['phone']     ?? '');
$address   = trim($body['address']   ?? '');
$city      = trim($body['city']      ?? '');
$zip       = trim($body['zip']       ?? '');
$note      = trim($body['note']      ?? '');
$items     = $body['items']          ?? [];
$subtotal  = (float) ($body['subtotal'] ?? 0);
$total     = (float) ($body['total']    ?? 0);

// ── Validation ──
if (empty($firstName)) fail('Prenom requis');
if (empty($phone))     fail('Telephone requis');
if (empty($address))   fail('Adresse requise');
if (empty($items) || !is_array($items)) fail('Panier vide');
if ($total <= 0)       fail('Total invalide');

// Sanitize items
$cleanItems = array_map(function($i) {
    return [
        'id'    => (int)   ($i['id']    ?? 0),
        'name'  => (string)($i['name']  ?? ''),
        'price' => (float) ($i['price'] ?? 0),
        'qty'   => (int)   ($i['qty']   ?? 1),
        'img'   => (string)($i['img']   ?? ''),
    ];
}, $items);

// ── Generate order ref ──
$orderRef = 'JJ-' . strtoupper(substr(uniqid(), -5));

// ── Save to DB ──
$db   = getDB();
$stmt = $db->prepare('
    INSERT INTO orders
        (order_ref, first_name, last_name, phone, address, city, zip, note, items, subtotal, total, status)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "new")
');
$stmt->execute([
    $orderRef,
    $firstName, $lastName,
    $phone, $address, $city, $zip,
    $note ?: null,
    json_encode($cleanItems),
    $subtotal, $total
]);

// ── Send email notification to admin ──
$itemLines = implode("\n", array_map(fn($i) =>
    "  - {$i['name']} x{$i['qty']} = " . ($i['price'] * $i['qty']) . " TND",
$cleanItems));

$emailBody = "Nouvelle commande #{$orderRef}\n\n"
    . "Client : {$firstName} {$lastName}\n"
    . "Telephone : {$phone}\n"
    . "Adresse : {$address}, {$city} {$zip}\n"
    . ($note ? "Note : {$note}\n" : "")
    . "\nArticles :\n{$itemLines}\n"
    . "\nSous-total : {$subtotal} TND"
    . "\nLivraison : 7 TND"
    . "\nTotal : {$total} TND\n"
    . "\n---\nConnectez-vous au panneau admin pour traiter cette commande.";

$emailHeaders  = "From: " . STORE_NAME . " <noreply@" . ($_SERVER['HTTP_HOST'] ?? 'joujou.tn') . ">\r\n";
$emailHeaders .= "Content-Type: text/plain; charset=utf-8\r\n";

@mail(
    ADMIN_EMAIL,
    "[" . STORE_NAME . "] Nouvelle commande #{$orderRef}",
    $emailBody,
    $emailHeaders
);

ok([
    'order_ref' => $orderRef,
    'message'   => 'Commande enregistree avec succes',
]);
