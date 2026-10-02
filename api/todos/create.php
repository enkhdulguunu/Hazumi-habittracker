<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

todo_require_method(["POST"]);

$userId = todo_require_user();
todo_ensure_table($pdo);

$data = todo_json_body();
$text = todo_clean_text($data["text"] ?? "");

$stmt = $pdo->prepare("
    INSERT INTO todos (user_id, todo_text)
    VALUES (:user_id, :todo_text)
");

$stmt->execute([
    "user_id" => $userId,
    "todo_text" => $text
]);

$todoId = (int) $pdo->lastInsertId();
$todo = todo_from_row(todo_fetch($pdo, $userId, $todoId));

todo_success([
    "todo" => $todo
], 201);
