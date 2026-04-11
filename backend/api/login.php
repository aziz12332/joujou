<?php
require_once __DIR__ . '/config.php';
setHeaders();

$action = $_GET['action'] ?? 'login';

if ($action === 'check') {
    $token = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    $valid = ($token === getAdminToken());
    ok(['logged_in' => $valid]);
}

if ($action === 'logout') {
    ok(['message' => 'Deconnecte']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$password = trim($body['password'] ?? '');

if (empty($password)) fail('Mot de passe requis');

$db   = getDB();
$stmt = $db->prepare('SELECT password_hash FROM admin WHERE username = ?');
$stmt->execute(['admin']);
$row  = $stmt->fetch();

if ($row && password_verify($password, $row['password_hash'])) {
    ok(['message' => 'Connecte', 'token' => getAdminToken()]);
} else {
    fail('Mot de passe incorrect.');
}

function getAdminToken() {
    return hash('sha256', 'JouJou_Secret_2025_' . date('Y-m-d'));
}