<?php
define('DB_HOST', 'mysql.railway.internal');
define('DB_NAME', 'railway');
define('DB_USER', 'root');
define('DB_PASS', 'TYhyOZjdgwvCmYcuJYOFcBdzcDBMDmTz');
define('DB_CHARSET', 'utf8mb4');

define('ADMIN_EMAIL', 'azizklif2004@gmail.com');
define('STORE_NAME',  'JouJou Accessoires');

define('UPLOAD_DIR',      __DIR__ . '/../uploads/');
define('UPLOAD_URL_BASE', '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

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

function setHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Admin-Token');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
}

function requireAdmin(): void {
    $token = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    $valid = ($token === hash('sha256', 'JouJou_Secret_2025_' . date('Y-m-d')));
    if (!$valid) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Non autorise']));
    }
}

function ok(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}