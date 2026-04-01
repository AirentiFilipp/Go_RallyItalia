<?php
// ── Configurazione database ──────────────────────────────
// Legge le variabili d'ambiente impostate in docker-compose.yml

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'rally_italia');
define('DB_USER', getenv('DB_USER') ?: 'rally_user');
define('DB_PASS', getenv('DB_PASS') ?: 'rally_pass');

// Password admin (in produzione usa variabile d'ambiente!)
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');

// Connessione PDO singleton
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// Helper: risposta JSON
function jsonResponse(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Helper: verifica admin dalla sessione
function isAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Helper: richiede admin o restituisce 403
function requireAdmin(): void {
    if (!isAdmin()) {
        jsonResponse(['error' => 'Accesso non autorizzato.'], 403);
    }
}
