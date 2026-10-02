<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["POST"]);
api_ensure_password_reset_table($pdo);

$data = api_json_body();
$email = trim((string) ($data["email"] ?? ""));

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_fail(400, "Valid email address is required.");
}

function hazumi_is_local_request(): bool
{
    $host = $_SERVER["HTTP_HOST"] ?? "";

    return PHP_SAPI === "cli" ||
        $host === "localhost" ||
        $host === "127.0.0.1" ||
        strpos($host, "localhost:") === 0 ||
        strpos($host, "127.0.0.1:") === 0;
}

function hazumi_app_base_url(): string
{
    $https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
        (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");
    $scheme = $https ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $appRoot = preg_replace("#/api/auth/[^/]+$#", "", $scriptName);

    if (!is_string($appRoot) || $appRoot === $scriptName) {
        $appRoot = "";
    }

    return $scheme . "://" . $host . rtrim($appRoot, "/");
}

$message = "If that email is registered, a password reset link has been sent.";
$resetLink = null;

try {
    $stmt = $pdo->prepare("
        SELECT id, email
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash("sha256", $token);

        $pdo->prepare("
            DELETE FROM password_resets
            WHERE user_id = :user_id OR expires_at < NOW()
        ")->execute([
            "user_id" => (int) $user["id"]
        ]);

        $pdo->prepare("
            INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))
        ")->execute([
            "user_id" => (int) $user["id"],
            "token_hash" => $tokenHash
        ]);

        $resetLink = hazumi_app_base_url() . "/reset_password.php?token=" . urlencode($token);

        if (!hazumi_is_local_request()) {
            $hostName = preg_replace('/:\d+$/', "", $_SERVER["HTTP_HOST"] ?? "hazumi");
            $subject = "Reset your HAZUMI password";
            $body = "Use this link to reset your HAZUMI password:\n\n" .
                $resetLink .
                "\n\nThis link expires in 1 hour.";
            $headers = "From: HAZUMI <no-reply@" . $hostName . ">\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail((string) $user["email"], $subject, $body, $headers);
        }
    }

    api_success([
        "message" => $message,
        "reset_link" => hazumi_is_local_request() ? $resetLink : null
    ]);
} catch (Throwable $e) {
    api_fail(500, "Password reset request failed.");
}
