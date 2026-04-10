<?php
// api/get_reviews.php — Returns all approved reviews (public)
require_once __DIR__ . '/config.php';
setHeaders();

$db   = getDB();
$rows = $db->query('SELECT * FROM reviews WHERE approved = 1 ORDER BY created_at DESC')->fetchAll();

$reviews = array_map(function($r) {
    return [
        'id'           => (int) $r['id'],
        'first_name'   => $r['first_name'],
        'city'         => $r['city'] ?? '',
        'rating'       => (int) $r['rating'],
        'comment'      => $r['comment'],
        'date_display' => date('d/m/Y', strtotime($r['created_at'])),
    ];
}, $rows);

ok(['reviews' => $reviews]);
