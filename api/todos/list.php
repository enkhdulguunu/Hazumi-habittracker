<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

todo_require_method(["GET"]);

$userId = todo_require_user();
todo_ensure_table($pdo);

$stmt = $pdo->prepare("
    SELECT id, todo_text, completed, created_at, updated_at
    FROM todos
    WHERE user_id = :user_id
    ORDER BY created_at ASC, id ASC
");

$stmt->execute([
    "user_id" => $userId
]);

$todos = array_map(
    "todo_from_row",
    $stmt->fetchAll()
);

todo_success([
    "todos" => $todos
]);
