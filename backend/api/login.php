<?php
// api/login.php — Admin login / logout / check
require_once __DIR__ . '/config.php';
setHeaders();
session_start();

$action = $_GET['action'] ?? 'login';

// ── CHECK SESSION ──────────────────────────────
if ($action === 'check') {
    ok(['logged_in' => !empty($_SESSION['jj_admin'])]);
}

// ── LOGOUT ────────────────────────────────────
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    ok(['message' => 'Deconnecte']);
}

// ── LOGIN ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Methode non autorisee', 405);

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$password = trim($body['password'] ?? '');

if (empty($password)) fail('Mot de passe requis');

// Brute-force protection via session counter
if (empty($_SESSION['jj_attempts']))  $_SESSION['jj_attempts']  = 0;
if (empty($_SESSION['jj_lock_until'])) $_SESSION['jj_lock_until'] = 0;

$now = time();
if ($_SESSION['jj_lock_until'] > $now) {
    $wait = ceil(($_SESSION['jj_lock_until'] - $now) / 60);
    fail("Trop de tentatives. Reessayez dans {$wait} minute(s).", 429);
}

$db   = getDB();
$stmt = $db->prepare('SELECT password_hash FROM admin WHERE username = ?');
$stmt->execute(['admin']);
$row  = $stmt->fetch();

if ($row && password_verify($password, $row['password_hash'])) {
    $_SESSION['jj_admin']    = true;
    $_SESSION['jj_attempts'] = 0;
    session_regenerate_id(true);
    ok(['message' => 'Connecte']);
} else {
    $_SESSION['jj_attempts']++;
    if ($_SESSION['jj_attempts'] >= 5) {
        $_SESSION['jj_lock_until'] = $now + 15 * 60; // 15 min lock
        fail('Trop de tentatives. Compte bloque 15 minutes.', 429);
    }
    $remaining = 5 - $_SESSION['jj_attempts'];
    fail("Mot de passe incorrect. {$remaining} tentative(s) restante(s).");
}
