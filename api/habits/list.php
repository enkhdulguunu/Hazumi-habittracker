<?php
declare(strict_types=1);

require_once __DIR__ . "/common.php";

api_require_method(["GET"]);

$userId = api_require_user();
api_ensure_productivity_tables($pdo);

$title = habit_ensure_user_defaults($pdo, $userId);

$fromDate = isset($_GET["from"]) && $_GET["from"] !== ""
    ? api_valid_date($_GET["from"])
    : null;
$toDate = isset($_GET["to"]) && $_GET["to"] !== ""
    ? api_valid_date($_GET["to"])
    : null;

$stmt = $pdo->prepare("
    SELECT id, habit_name
    FROM habits
    WHERE user_id = :user_id
    ORDER BY sort_order ASC, id ASC
");

$stmt->execute(["user_id" => $userId]);

$habits = array_map("habit_from_row", $stmt->fetchAll());
$logs = habit_log_map($pdo, $userId, $fromDate, $toDate);

api_success([
    "title" => $title,
    "habits" => $habits,
    "logs" => $logs
]);
