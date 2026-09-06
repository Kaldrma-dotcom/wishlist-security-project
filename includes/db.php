<?php
$host = "localhost";
$dbname = "security_project";
$dbuser = "root";
$dbpass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die("Database connection failed.");
}

try {
    $col = $pdo->query("SHOW COLUMNS FROM watchlist_items LIKE 'poster_url'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE watchlist_items ADD COLUMN poster_url varchar(500) DEFAULT NULL");
        $pdo->exec("ALTER TABLE watchlist_items ADD COLUMN external_id varchar(64) DEFAULT NULL");
    }
} catch (PDOException $e) {
    error_log('Schema check failed: ' . $e->getMessage());
}
