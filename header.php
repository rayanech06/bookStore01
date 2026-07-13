<?php
// ── Configuration base de données ──────────────────────────────
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_NAME', 'if0_42352470_bookstore');
define('DB_USER', 'if0_42352470');
define('DB_PASS', 'booKStore102');
define('DB_CHARSET', 'utf8mb4');

// ── Connexion PDO ───────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:40px;color:#C0392B;">
                ❌ Erreur de connexion à la base de données :<br><br>
                <code>' . htmlspecialchars($e->getMessage()) . '</code><br><br>
                Vérifiez vos paramètres dans <strong>includes/config.php</strong>
                </div>');
        }
    }
    return $pdo;
}

// ── Helpers ─────────────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

session_start();
