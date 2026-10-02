<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$dateKey = api_valid_date($data["date"] ?? "");
$time = api_valid_time($data["time"] ?? "09:00");
$description = api_clean_text($data["desc"] ?? "", 500, "Task description is required.");

$stmt = $pdo->prepare("
    INSERT INTO calendar_events (user_id, event_date, event_time, description)
    VALUES (:user_id, :event_date, :event_time, :description)
");

$stmt->execute([
    "user_id" => $userId,
    "event_date" => $dateKey,
    "event_time" => $time . ":00",
    "description" => $description
]);

api_success([
    "event" => [
        "id" => (int) $pdo->lastInsertId(),
        "date" => $dateKey,
        "desc" => $description,
        "time" => $time
    ]
], 201);
