<?php
declare(strict_types=1);

session_start();

header("Content-Type: application/json");

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Not logged in."
    ]);

    exit;
}

$userId = (int) $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT id, full_name, email, phone, date_of_birth, gender, created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "user" => $user
]);