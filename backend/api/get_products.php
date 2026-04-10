<?php
// api/get_products.php — Returns all products as JSON (public)
require_once __DIR__ . '/config.php';
setHeaders();

$db   = getDB();
$rows = $db->query('SELECT * FROM products ORDER BY id ASC')->fetchAll();

// Normalize for JS
$products = array_map(function($p) {
    return [
        'id'     => (int) $p['id'],
        'name'   => $p['name'],
        'cat'    => $p['cat'],
        'price'  => (float) $p['price'],
        'old'    => $p['old_price'] ? (float) $p['old_price'] : null,
        'img'    => $p['img'],
        'isNew'  => (bool) $p['is_new'],
    ];
}, $rows);

ok(['products' => $products]);
