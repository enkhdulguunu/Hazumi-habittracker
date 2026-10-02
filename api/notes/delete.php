<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["DELETE", "POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$noteId = (int) ($data["id"] ?? 0);

if ($noteId <= 0) {
    api_fail(400, "Note id is required.");
}

$stmt = $pdo->prepare("
    DELETE FROM notes
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    "id" => $noteId,
    "user_id" => $userId
]);

if ($stmt->rowCount() === 0) {
    api_fail(404, "Note not found.");
}

api_success();
