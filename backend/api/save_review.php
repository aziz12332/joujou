<?php
// api/save_review.php — Submit a product review (public)
require_once __DIR__ . '/config.php';
setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$firstName = trim($body['first_name'] ?? '');
$city      = trim($body['city']       ?? '');
$rating    = isset($body['rating']) ? (int) $body['rating'] : 0;
$comment   = trim($body['comment']    ?? '');

if (empty($firstName))            fail('Prenom requis');
if ($rating < 1 || $rating > 5)   fail('Note invalide (1-5)');
if (empty($comment))              fail('Avis requis');
if (mb_strlen($comment) > 1000)   fail('Avis trop long (max 1000 caracteres)');

// Auto-approve reviews (set approved = 0 if you want manual moderation)
$approved = 1;

$db   = getDB();
$stmt = $db->prepare('INSERT INTO reviews (first_name, city, rating, comment, approved) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$firstName, $city ?: null, $rating, $comment, $approved]);

ok(['message' => 'Avis enregistre', 'id' => (int) $db->lastInsertId()]);
