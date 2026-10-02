<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

api_require_method(["PATCH", "PUT", "POST"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);
habit_ensure_user_defaults($pdo, $userId);

$data = api_json_body();
$title = api_optional_text($data["title"] ?? "Habit tracker", 150);

if ($title === "") {
    $title = "Habit tracker";
}

$stmt = $pdo->prepare("
    UPDATE habit_settings
    SET title = :title,
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = :user_id
");

$stmt->execute([
    "title" => $title,
    "user_id" => $userId
]);

api_success([
    "title" => $title
]);
