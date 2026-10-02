<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

todo_require_method(["DELETE", "POST"]);

$userId = todo_require_user();
todo_ensure_table($pdo);

$data = todo_json_body();
$completedOnly = !empty($data["completedOnly"]);

if ($completedOnly) {
    $stmt = $pdo->prepare("
        DELETE FROM todos
        WHERE user_id = :user_id
          AND completed = 1
    ");
} else {
    $stmt = $pdo->prepare("
        DELETE FROM todos
        WHERE user_id = :user_id
    ");
}

$stmt->execute([
    "user_id" => $userId
]);

todo_success([
    "deleted" => $stmt->rowCount()
]);
