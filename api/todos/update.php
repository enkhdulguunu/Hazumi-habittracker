<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

todo_require_method(["PATCH", "PUT", "POST"]);

$userId = todo_require_user();
todo_ensure_table($pdo);

$data = todo_json_body();
$todoId = (int) ($data["id"] ?? 0);

if ($todoId <= 0) {
    todo_fail(400, "Todo id is required.");
}

todo_fetch($pdo, $userId, $todoId);

$setParts = [];
$params = [
    "id" => $todoId,
    "user_id" => $userId
];

if (array_key_exists("text", $data)) {
    $setParts[] = "todo_text = :todo_text";
    $params["todo_text"] = todo_clean_text($data["text"]);
}

if (array_key_exists("completed", $data)) {
    $setParts[] = "completed = :completed";
    $params["completed"] = !empty($data["completed"]) ? 1 : 0;
}

if ($setParts === []) {
    todo_fail(400, "No todo fields were provided.");
}

$setParts[] = "updated_at = CURRENT_TIMESTAMP";

$stmt = $pdo->prepare("
    UPDATE todos
    SET " . implode(", ", $setParts) . "
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute($params);

$todo = todo_from_row(todo_fetch($pdo, $userId, $todoId));

todo_success([
    "todo" => $todo
]);
