<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["GET"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$stmt = $pdo->prepare("
    SELECT id, title, body, created_at, updated_at
    FROM notes
    WHERE user_id = :user_id
    ORDER BY updated_at DESC, id DESC
");

$stmt->execute(["user_id" => $userId]);

$notes = array_map(
    static fn (array $row): array => [
        "id" => (int) $row["id"],
        "title" => (string) $row["title"],
        "body" => (string) $row["body"],
        "createdAt" => api_date_to_ms($row["created_at"] ?? null),
        "updatedAt" => api_date_to_ms($row["updated_at"] ?? null)
    ],
    $stmt->fetchAll()
);

api_success([
    "notes" => $notes
]);
