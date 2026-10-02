<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

api_require_method(["POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$habitId = (int) ($data["habitId"] ?? 0);
$dateKey = api_valid_date($data["date"] ?? "");
$completed = !empty($data["completed"]);

if ($habitId <= 0) {
    api_fail(400, "Habit id is required.");
}

habit_fetch($pdo, $userId, $habitId);

if ($completed) {
    $stmt = $pdo->prepare("
        INSERT INTO habit_logs (habit_id, user_id, log_date, completed)
        VALUES (:habit_id, :user_id, :log_date, 1)
        ON DUPLICATE KEY UPDATE
            completed = 1,
            updated_at = CURRENT_TIMESTAMP
    ");

    $stmt->execute([
        "habit_id" => $habitId,
        "user_id" => $userId,
        "log_date" => $dateKey
    ]);
} else {
    $stmt = $pdo->prepare("
        DELETE FROM habit_logs
        WHERE habit_id = :habit_id
          AND user_id = :user_id
          AND log_date = :log_date
    ");

    $stmt->execute([
        "habit_id" => $habitId,
        "user_id" => $userId,
        "log_date" => $dateKey
    ]);
}

api_success([
    "habitId" => $habitId,
    "date" => $dateKey,
    "completed" => $completed
]);
