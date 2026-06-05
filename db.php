<?php
// =============================================
// includes/db.php — Connexion MySQL
// =============================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'stages_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:30px;background:#fee2e2;color:#b91c1c;border-radius:8px;margin:20px">
        <strong>Erreur de connexion à la base de données</strong><br>
        ' . htmlspecialchars($e->getMessage()) . '<br><br>
        Vérifiez vos identifiants dans <code>includes/db.php</code>
    </div>');
}
