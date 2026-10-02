<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

api_require_method(["DELETE", "POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$data = api_json_body();
$eventId = (int) ($data["id"] ?? 0);

if ($eventId <= 0) {
    api_fail(400, "Calendar event id is required.");
}

$stmt = $pdo->prepare("
    DELETE FROM calendar_events
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    "id" => $eventId,
    "user_id" => $userId
]);

if ($stmt->rowCount() === 0) {
    api_fail(404, "Calendar event not found.");
}

api_success();
