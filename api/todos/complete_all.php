<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

todo_require_method(["POST"]);

$userId = todo_require_user();
todo_ensure_table($pdo);

$stmt = $pdo->prepare("
    UPDATE todos
    SET completed = 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = :user_id
");

$stmt->execute([
    "user_id" => $userId
]);

todo_success([
    "updated" => $stmt->rowCount()
]);
