<?php
declare(strict_types=1);

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../config/database.php";

function todo_send(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function todo_success(array $payload = [], int $statusCode = 200): void
{
    todo_send(
        array_merge(
            ["success" => true],
            $payload
        ),
        $statusCode
    );
}

function todo_fail(int $statusCode, string $message): void
{
    todo_send(
        [
            "success" => false,
            "message" => $message
        ],
        $statusCode
    );
}

function todo_require_method(array $allowedMethods): void
{
    if (!in_array($_SERVER["REQUEST_METHOD"], $allowedMethods, true)) {
        http_response_code(405);
        header("Allow: " . implode(", ", $allowedMethods));

        todo_fail(
            405,
            implode(" or ", $allowedMethods) . " request required."
        );
    }
}

function todo_require_user(): int
{
    if (!isset($_SESSION["user_id"])) {
        todo_fail(401, "Not logged in.");
    }

    return (int) $_SESSION["user_id"];
}

function todo_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS todos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            todo_text VARCHAR(500) NOT NULL,
            completed TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_todos_user_created (user_id, created_at),

            CONSTRAINT fk_todos_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");
}

function todo_json_body(): array
{
    $rawData = file_get_contents("php://input");

    if ($rawData === false || trim($rawData) === "") {
        return [];
    }

    $data = json_decode($rawData, true);

    if (!is_array($data)) {
        todo_fail(400, "Invalid request data.");
    }

    return $data;
}

function todo_clean_text(mixed $value): string
{
    $text = trim((string) $value);

    if ($text === "") {
        todo_fail(400, "Todo text is required.");
    }

    if (function_exists("mb_substr")) {
        return mb_substr($text, 0, 500);
    }

    return substr($text, 0, 500);
}

function todo_date_to_atom(?string $dateValue): ?string
{
    if ($dateValue === null || $dateValue === "") {
        return null;
    }

    $timestamp = strtotime($dateValue);

    if ($timestamp === false) {
        return null;
    }

    return date(DATE_ATOM, $timestamp);
}

function todo_from_row(array $row): array
{
    return [
        "id" => (int) $row["id"],
        "text" => (string) $row["todo_text"],
        "completed" => (bool) $row["completed"],
        "createdAt" => todo_date_to_atom($row["created_at"] ?? null),
        "updatedAt" => todo_date_to_atom($row["updated_at"] ?? null)
    ];
}

function todo_fetch(PDO $pdo, int $userId, int $todoId): array
{
    $stmt = $pdo->prepare("
        SELECT id, todo_text, completed, created_at, updated_at
        FROM todos
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        "id" => $todoId,
        "user_id" => $userId
    ]);

    $todo = $stmt->fetch();

    if (!$todo) {
        todo_fail(404, "Todo not found.");
    }

    return $todo;
}
