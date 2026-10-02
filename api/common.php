<?php
declare(strict_types=1);

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/database.php";

function api_send(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function api_success(array $payload = [], int $statusCode = 200): void
{
    api_send(
        array_merge(["success" => true], $payload),
        $statusCode
    );
}

function api_fail(int $statusCode, string $message): void
{
    api_send(
        [
            "success" => false,
            "message" => $message
        ],
        $statusCode
    );
}

function api_require_method(array $allowedMethods): void
{
    if (!in_array($_SERVER["REQUEST_METHOD"], $allowedMethods, true)) {
        http_response_code(405);
        header("Allow: " . implode(", ", $allowedMethods));

        api_fail(405, implode(" or ", $allowedMethods) . " request required.");
    }
}

function api_require_user(): int
{
    if (!isset($_SESSION["user_id"])) {
        api_fail(401, "Not logged in.");
    }

    return (int) $_SESSION["user_id"];
}

function api_ensure_password_reset_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            UNIQUE KEY uniq_password_resets_token (token_hash),
            INDEX idx_password_resets_user (user_id),
            INDEX idx_password_resets_expires (expires_at),

            CONSTRAINT fk_password_resets_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");
}

function api_json_body(): array
{
    $rawData = file_get_contents("php://input");

    if ($rawData === false || trim($rawData) === "") {
        return [];
    }

    $data = json_decode($rawData, true);

    if (!is_array($data)) {
        api_fail(400, "Invalid request data.");
    }

    return $data;
}

function api_clean_text(mixed $value, int $maxLength, string $message): string
{
    $text = trim((string) $value);

    if ($text === "") {
        api_fail(400, $message);
    }

    if (function_exists("mb_substr")) {
        return mb_substr($text, 0, $maxLength);
    }

    return substr($text, 0, $maxLength);
}

function api_optional_text(mixed $value, int $maxLength): string
{
    $text = trim((string) $value);

    if (function_exists("mb_substr")) {
        return mb_substr($text, 0, $maxLength);
    }

    return substr($text, 0, $maxLength);
}

function api_valid_date(mixed $value, string $message = "Valid date is required."): string
{
    $date = trim((string) $value);

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        api_fail(400, $message);
    }

    [$year, $month, $day] = array_map("intval", explode("-", $date));

    if (!checkdate($month, $day, $year)) {
        api_fail(400, $message);
    }

    return $date;
}

function api_valid_time(mixed $value): string
{
    $time = trim((string) $value);

    if ($time === "") {
        return "09:00";
    }

    if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        api_fail(400, "Valid time is required.");
    }

    [$hour, $minute] = array_map("intval", explode(":", $time));

    if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
        api_fail(400, "Valid time is required.");
    }

    return $time;
}

function api_date_to_ms(?string $dateValue): int
{
    if ($dateValue === null || $dateValue === "") {
        return time() * 1000;
    }

    $timestamp = strtotime($dateValue);

    if ($timestamp === false) {
        return time() * 1000;
    }

    return $timestamp * 1000;
}

function api_ensure_productivity_tables(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS todos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            todo_text VARCHAR(500) NOT NULL,
            completed TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_todos_user_created (user_id, created_at),

            CONSTRAINT fk_todos_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS timeblocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            block_index TINYINT UNSIGNED NOT NULL,
            task_text VARCHAR(500) NOT NULL DEFAULT '',
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY uniq_timeblocks_user_block (user_id, block_index),
            INDEX idx_timeblocks_user (user_id),

            CONSTRAINT fk_timeblocks_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS habit_settings (
            user_id INT PRIMARY KEY,
            title VARCHAR(150) NOT NULL DEFAULT 'Habit tracker',
            defaults_seeded TINYINT(1) NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_habit_settings_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS habits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            habit_name VARCHAR(150) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_habits_user_order (user_id, sort_order, id),

            CONSTRAINT fk_habits_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS habit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            habit_id INT NOT NULL,
            user_id INT NOT NULL,
            log_date DATE NOT NULL,
            completed TINYINT(1) NOT NULL DEFAULT 1,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY uniq_habit_logs_habit_date (habit_id, log_date),
            INDEX idx_habit_logs_user_date (user_id, log_date),

            CONSTRAINT fk_habit_logs_habit
                FOREIGN KEY (habit_id)
                REFERENCES habits(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_habit_logs_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS calendar_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_date DATE NOT NULL,
            event_time TIME NOT NULL DEFAULT '09:00:00',
            description VARCHAR(500) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_calendar_user_date (user_id, event_date, event_time),

            CONSTRAINT fk_calendar_events_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL DEFAULT '',
            body MEDIUMTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_notes_user_updated (user_id, updated_at),

            CONSTRAINT fk_notes_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");
}
