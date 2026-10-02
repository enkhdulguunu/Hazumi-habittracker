<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["GET"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$stmt = $pdo->prepare("
    SELECT id, event_date, TIME_FORMAT(event_time, '%H:%i') AS event_time, description
    FROM calendar_events
    WHERE user_id = :user_id
    ORDER BY event_date ASC, event_time ASC, id ASC
");

$stmt->execute(["user_id" => $userId]);

$events = [];

foreach ($stmt->fetchAll() as $row) {
    $dateKey = (string) $row["event_date"];

    if (!isset($events[$dateKey])) {
        $events[$dateKey] = [];
    }

    $events[$dateKey][] = [
        "id" => (int) $row["id"],
        "desc" => (string) $row["description"],
        "time" => (string) $row["event_time"]
    ];
}

api_success([
    "events" => $events
]);
