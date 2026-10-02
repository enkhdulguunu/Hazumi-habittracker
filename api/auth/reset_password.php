<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["POST"]);
api_ensure_password_reset_table($pdo);

$data = api_json_body();
$token = trim((string) ($data["token"] ?? ""));
$password = (string) ($data["password"] ?? "");
$confirmPassword = (string) ($data["confirm_password"] ?? "");

if ($token === "") {
    api_fail(400, "Reset token is required.");
}

if ($password === "" || $confirmPassword === "") {
    api_fail(400, "New password and confirmation are required.");
}

if (strlen($password) < 6) {
    api_fail(400, "Password must be at least 6 characters.");
}

if ($password !== $confirmPassword) {
    api_fail(400, "Passwords do not match.");
}

try {
    $tokenHash = hash("sha256", $token);

    $stmt = $pdo->prepare("
        SELECT id, user_id
        FROM password_resets
        WHERE token_hash = :token_hash
          AND expires_at > NOW()
        LIMIT 1
    ");

    $stmt->execute(["token_hash" => $tokenHash]);
    $reset = $stmt->fetch();

    if (!$reset) {
        api_fail(400, "Reset link is invalid or expired.");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $pdo->prepare("
        UPDATE users
        SET password_hash = :password_hash
        WHERE id = :user_id
    ")->execute([
        "password_hash" => $passwordHash,
        "user_id" => (int) $reset["user_id"]
    ]);

    $pdo->prepare("
        DELETE FROM password_resets
        WHERE user_id = :user_id
    ")->execute([
        "user_id" => (int) $reset["user_id"]
    ]);

    $pdo->commit();

    api_success([
        "message" => "Password updated successfully. You can now log in."
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    api_fail(500, "Password reset failed.");
}
