<?php
declare(strict_types=1);

$requestUri = $_SERVER["REQUEST_URI"] ?? "";

$dbHost = getenv("HAZUMI_DB_HOST") ?: "localhost";
$dbName = getenv("HAZUMI_DB_NAME") ?: "hazumi_db";
$dbUser = getenv("HAZUMI_DB_USER") ?: "root";
$dbPass = getenv("HAZUMI_DB_PASS") ?: "";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);

    if (strpos($requestUri, "/api/") !== false) {
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "success" => false,
            "message" => "Database connection failed."
        ]);

        exit;
    }

    exit("Database connection failed.");
}
