<?php
declare(strict_types=1);

session_start();

header("Content-Type: application/json");

require_once "../../config/database.php";

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$fullName = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($fullName === "" || $email === "" || $password === "") {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Full name, email and password are required."
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

if (strlen($password) < 6) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters."
    ]);

    exit;
}

try {
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);

    if ($checkStmt->fetch()) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "Email already exists."
        ]);

        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password_hash)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $fullName,
        $email,
        $passwordHash
    ]);

    $userId = (int) $pdo->lastInsertId();
    session_regenerate_id(true);
    $_SESSION["user_id"] = $userId;

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully.",
        "user" => [
            "id" => $userId,
            "full_name" => $fullName,
            "email" => $email
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Registration failed."
    ]);
}