<?php
// ═══════════════════════════════════════════════
//  JouJou — Database Configuration
//  Edit this file with your hosting credentials
// ═══════════════════════════════════════════════

define('DB_HOST', 'localhost');
define('DB_NAME', 'joujou2_db');
define('DB_USER', 'root');        // ← Change to your DB username
define('DB_PASS', '');            // ← Change to your DB password
define('DB_CHARSET', 'utf8mb4');

// Admin email for order notifications
define('ADMIN_EMAIL', 'azizklif2004@gmail.com');  // ← Change to your email
define('STORE_NAME',  'JouJou Accessoires');

// Upload settings
define('UPLOAD_DIR',      __DIR__ . '/../uploads/');
define('UPLOAD_URL_BASE', '/uploads/');   // URL path to uploads folder
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB

// ─── PDO connection (used by all API files) ───
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// ─── CORS & JSON headers ───
function setHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Admin-Token');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
}

// ─── Session-based admin auth ───
function requireAdmin(): void {
    session_start();
    if (empty($_SESSION['jj_admin'])) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Non autorise']));
    }
}

// ─── JSON response helpers ───
function ok(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}
