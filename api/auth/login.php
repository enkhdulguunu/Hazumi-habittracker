<?php
declare(strict_types=1);

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "POST request required."
    ]);

    exit;
}

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request data."
    ]);

    exit;
}

$email = trim((string) ($data["email"] ?? ""));
$password = (string) ($data["password"] ?? "");

if ($email === "" || $password === "") {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);

    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);

    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, password_hash
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        "email" => $email
    ]);

    $user = $stmt->fetch();

    if (
        !$user ||
        !password_verify($password, $user["password_hash"])
    ) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Incorrect email or password."
        ]);

        exit;
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["email"] = $user["email"];

    echo json_encode([
        "success" => true,
        "message" => "Logged in successfully.",
        "user" => [
            "id" => (int) $user["id"],
            "full_name" => $user["full_name"],
            "email" => $user["email"]
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error occurred."
    ]);
}