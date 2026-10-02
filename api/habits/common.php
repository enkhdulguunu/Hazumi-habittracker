<?php
declare(strict_types=1);

require_once __DIR__ . "/../common.php";

const HAZUMI_DEFAULT_HABITS = [
    "Drink Water",
    "Exercise",
    "Read Book",
    "Meditate",
    "Sleep Early",
    "Study",
    "Walking"
];

function habit_from_row(array $row): array
{
    return [
        "id" => (int) $row["id"],
        "name" => (string) $row["habit_name"]
    ];
}

function habit_ensure_user_defaults(PDO $pdo, int $userId): string
{
    $stmt = $pdo->prepare("
        SELECT title, defaults_seeded
        FROM habit_settings
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute(["user_id" => $userId]);
    $settings = $stmt->fetch();

    if (!$settings) {
        $stmt = $pdo->prepare("
            INSERT INTO habit_settings (user_id, title, defaults_seeded)
            VALUES (:user_id, 'Habit tracker', 1)
        ");

        $stmt->execute(["user_id" => $userId]);

        $insertHabit = $pdo->prepare("
            INSERT INTO habits (user_id, habit_name, sort_order)
            VALUES (:user_id, :habit_name, :sort_order)
        ");

        foreach (HAZUMI_DEFAULT_HABITS as $index => $habitName) {
            $insertHabit->execute([
                "user_id" => $userId,
                "habit_name" => $habitName,
                "sort_order" => $index
            ]);
        }

        return "Habit tracker";
    }

    return (string) $settings["title"];
}

function habit_fetch(PDO $pdo, int $userId, int $habitId): array
{
    $stmt = $pdo->prepare("
        SELECT id, habit_name
        FROM habits
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        "id" => $habitId,
        "user_id" => $userId
    ]);

    $habit = $stmt->fetch();

    if (!$habit) {
        api_fail(404, "Habit not found.");
    }

    return $habit;
}

function habit_log_map(PDO $pdo, int $userId, ?string $fromDate, ?string $toDate): array
{
    $where = ["hl.user_id = :user_id", "hl.completed = 1"];
    $params = ["user_id" => $userId];

    if ($fromDate !== null) {
        $where[] = "hl.log_date >= :from_date";
        $params["from_date"] = $fromDate;
    }

    if ($toDate !== null) {
        $where[] = "hl.log_date <= :to_date";
        $params["to_date"] = $toDate;
    }

    $stmt = $pdo->prepare("
        SELECT hl.habit_id, hl.log_date
        FROM habit_logs hl
        INNER JOIN habits h
            ON h.id = hl.habit_id
           AND h.user_id = hl.user_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY hl.log_date ASC
    ");

    $stmt->execute($params);

    $logs = [];

    foreach ($stmt->fetchAll() as $row) {
        $habitId = (string) $row["habit_id"];
        $dateKey = (string) $row["log_date"];

        if (!isset($logs[$habitId])) {
            $logs[$habitId] = [];
        }

        $logs[$habitId][$dateKey] = true;
    }

    return $logs;
}
