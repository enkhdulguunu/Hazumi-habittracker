<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

todo_require_method(["DELETE", "POST"]);

$userId = todo_require_user();
todo_ensure_table($pdo);

$data = todo_json_body();
$todoId = (int) ($data["id"] ?? 0);

if ($todoId <= 0) {
    todo_fail(400, "Todo id is required.");
}

$stmt = $pdo->prepare("
    DELETE FROM todos
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    "id" => $todoId,
    "user_id" => $userId
]);

if ($stmt->rowCount() === 0) {
    todo_fail(404, "Todo not found.");
}

todo_success();
