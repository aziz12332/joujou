<?php
// api/newsletter.php — Save newsletter subscriber (public)
require_once __DIR__ . '/config.php';
setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$email = strtolower(trim($body['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Email invalide');
}

$db = getDB();
try {
    $stmt = $db->prepare('INSERT INTO newsletter (email) VALUES (?)');
    $stmt->execute([$email]);
    ok(['message' => 'Inscription reussie']);
} catch (PDOException $e) {
    // Duplicate entry = already subscribed
    if ($e->getCode() === '23000') {
        ok(['message' => 'Deja inscrit(e)']);
    }
    fail('Erreur serveur', 500);
}
