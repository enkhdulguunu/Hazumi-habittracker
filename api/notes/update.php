<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["PATCH", "PUT", "POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$noteId = (int) ($data["id"] ?? 0);

if ($noteId <= 0) {
    api_fail(400, "Note id is required.");
}

$title = api_optional_text($data["title"] ?? "", 255);
$body = (string) ($data["body"] ?? "");

$stmt = $pdo->prepare("
    UPDATE notes
    SET title = :title,
        body = :body,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    "title" => $title,
    "body" => $body,
    "id" => $noteId,
    "user_id" => $userId
]);

if ($stmt->rowCount() === 0) {
    $check = $pdo->prepare("
        SELECT id
        FROM notes
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    ");

    $check->execute([
        "id" => $noteId,
        "user_id" => $userId
    ]);

    if (!$check->fetch()) {
        api_fail(404, "Note not found.");
    }
}

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
]);
