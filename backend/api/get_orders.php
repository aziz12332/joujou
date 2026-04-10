<?php
// api/get_orders.php — Returns all orders (admin only)
require_once __DIR__ . '/config.php';
setHeaders();
requireAdmin();

$db   = getDB();
$rows = $db->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();

$orders = array_map(function($o) {
    $items = json_decode($o['items'], true) ?? [];
    return [
        'id'          => $o['order_ref'],
        'firstName'   => $o['first_name'],
        'lastName'    => $o['last_name'],
        'phone'       => $o['phone'],
        'address'     => $o['address'],
        'city'        => $o['city'],
        'zip'         => $o['zip'],
        'note'        => $o['note'],
        'items'       => $items,
        'subtotal'    => (float) $o['subtotal'],
        'total'       => (float) $o['total'],
        'status'      => $o['status'],
        'dateDisplay' => date('d/m/Y H:i', strtotime($o['created_at'])),
        'date'        => $o['created_at'],
    ];
}, $rows);

ok(['orders' => $orders]);
