<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$title = api_optional_text($data["title"] ?? "", 255);
$body = (string) ($data["body"] ?? "");

$stmt = $pdo->prepare("
    INSERT INTO notes (user_id, title, body)
    VALUES (:user_id, :title, :body)
");

$stmt->execute([
    "user_id" => $userId,
    "title" => $title,
    "body" => $body
]);

$noteId = (int) $pdo->lastInsertId();

$stmt = $pdo->prepare("
    SELECT id, title, body, created_at, updated_at
    FROM notes
    WHERE id = :id
      AND user_id = :user_id
    LIMIT 1
");

$stmt->execute([
    "id" => $noteId,
    "user_id" => $userId
]);

$note = $stmt->fetch();

api_success([
    "note" => [
        "id" => (int) $note["id"],
        "title" => (string) $note["title"],
        "body" => (string) $note["body"],
        "createdAt" => api_date_to_ms($note["created_at"] ?? null),
        "updatedAt" => api_date_to_ms($note["updated_at"] ?? null)
    ]
], 201);
