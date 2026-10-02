<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["POST", "PATCH", "PUT"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$blockIndex = (int) ($data["index"] ?? -1);
$text = api_optional_text($data["text"] ?? "", 500);

if ($blockIndex < 0 || $blockIndex > 23) {
    api_fail(400, "Valid time block index is required.");
}

$stmt = $pdo->prepare("
    INSERT INTO timeblocks (user_id, block_index, task_text)
    VALUES (:user_id, :block_index, :task_text)
    ON DUPLICATE KEY UPDATE
        task_text = VALUES(task_text),
        updated_at = CURRENT_TIMESTAMP
");

$stmt->execute([
    "user_id" => $userId,
    "block_index" => $blockIndex,
    "task_text" => $text
]);

api_success([
    "block" => [
        "index" => $blockIndex,
        "text" => $text
    ]
]);
