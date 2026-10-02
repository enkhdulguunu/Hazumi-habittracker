<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["POST", "PATCH", "PUT"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$dateKey = api_valid_date($data["date"] ?? "");
$time = api_valid_time($data["time"] ?? "");
$description = api_optional_text($data["desc"] ?? "", 500);

try {
    if ($description === "") {
        $stmt = $pdo->prepare("
            DELETE FROM calendar_events
            WHERE user_id = :user_id
              AND event_date = :event_date
              AND event_time = :event_time
        ");

        $stmt->execute([
            "user_id" => $userId,
            "event_date" => $dateKey,
            "event_time" => $time . ":00"
        ]);

        api_success([
            "cleared" => true,
            "date" => $dateKey,
            "time" => $time
        ]);
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM calendar_events
        WHERE user_id = :user_id
          AND event_date = :event_date
          AND event_time = :event_time
        ORDER BY id ASC
        LIMIT 1
    ");

    $stmt->execute([
        "user_id" => $userId,
        "event_date" => $dateKey,
        "event_time" => $time . ":00"
    ]);

    $eventId = (int) ($stmt->fetchColumn() ?: 0);

    if ($eventId > 0) {
        $stmt = $pdo->prepare("
            UPDATE calendar_events
            SET description = :description
            WHERE id = :id
              AND user_id = :user_id
        ");

        $stmt->execute([
            "description" => $description,
            "id" => $eventId,
            "user_id" => $userId
        ]);
    } else {
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

        $eventId = (int) $pdo->lastInsertId();
    }

    api_success([
        "event" => [
            "id" => $eventId,
            "date" => $dateKey,
            "desc" => $description,
            "time" => $time
        ]
    ]);
} catch (Throwable $e) {
    api_fail(500, "Calendar time block update failed.");
}
