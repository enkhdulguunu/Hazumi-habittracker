<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

api_require_method(["POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);
habit_ensure_user_defaults($pdo, $userId);

$data = api_json_body();
$name = api_clean_text($data["name"] ?? "", 150, "Habit name is required.");

$stmt = $pdo->prepare("
    SELECT COALESCE(MAX(sort_order), -1) + 1 AS next_order
    FROM habits
    WHERE user_id = :user_id
");

$stmt->execute(["user_id" => $userId]);
$nextOrder = (int) ($stmt->fetch()["next_order"] ?? 0);

$stmt = $pdo->prepare("
    INSERT INTO habits (user_id, habit_name, sort_order)
    VALUES (:user_id, :habit_name, :sort_order)
");

$stmt->execute([
    "user_id" => $userId,
    "habit_name" => $name,
    "sort_order" => $nextOrder
]);

$habit = habit_from_row(habit_fetch($pdo, $userId, (int) $pdo->lastInsertId()));

api_success([
    "habit" => $habit
], 201);
