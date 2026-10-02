<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

api_require_method(["PATCH", "PUT", "POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$habitId = (int) ($data["id"] ?? 0);

if ($habitId <= 0) {
    api_fail(400, "Habit id is required.");
}

habit_fetch($pdo, $userId, $habitId);
$name = api_clean_text($data["name"] ?? "", 150, "Habit name is required.");

$stmt = $pdo->prepare("
    UPDATE habits
    SET habit_name = :habit_name,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    "habit_name" => $name,
    "id" => $habitId,
    "user_id" => $userId
]);

$habit = habit_from_row(habit_fetch($pdo, $userId, $habitId));

api_success([
    "habit" => $habit
]);
