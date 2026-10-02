<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["GET"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$stmt = $pdo->prepare("
    SELECT block_index, task_text
    FROM timeblocks
    WHERE user_id = :user_id
    ORDER BY block_index ASC
");

$stmt->execute(["user_id" => $userId]);

$blocks = array_map(
    static fn (array $row): array => [
        "index" => (int) $row["block_index"],
        "text" => (string) $row["task_text"]
    ],
    $stmt->fetchAll()
);

api_success([
    "blocks" => $blocks
]);
