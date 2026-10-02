<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/config/database.php";

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$userId = (int) $_SESSION["user_id"];
$successMessage = "";
$errorMessage = "";

$allowedGenders = ["female", "male", "other", "prefer_not_to_say"];

try {
    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, phone, date_of_birth, gender, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} catch (PDOException $error) {
    http_response_code(500);
    exit("Profile could not be loaded.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
    $gender = $_POST["gender"] ?? "prefer_not_to_say";

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($fullName === "" || $email === "") {
        $errorMessage = "Full name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } elseif (!in_array($gender, $allowedGenders, true)) {
        $errorMessage = "Invalid gender option.";
    } elseif ($dateOfBirth !== "" && !DateTime::createFromFormat("Y-m-d", $dateOfBirth)) {
        $errorMessage = "Invalid date of birth.";
    } elseif ($newPassword !== "" && strlen($newPassword) < 6) {
        $errorMessage = "New password must be at least 6 characters.";
    } elseif ($newPassword !== "" && $newPassword !== $confirmPassword) {
        $errorMessage = "New password and confirm password do not match.";
    } elseif ($newPassword !== "" && !password_verify($currentPassword, $user["password_hash"])) {
        $errorMessage = "Current password is incorrect.";
    } else {
        try {
            $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
            $emailCheck->execute([$email, $userId]);

            if ($emailCheck->fetch()) {
                $errorMessage = "This email is already used by another account.";
            } else {
                $phoneValue = $phone !== "" ? $phone : null;
                $dateValue = $dateOfBirth !== "" ? $dateOfBirth : null;

                if ($newPassword !== "") {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

                    $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, date_of_birth = ?, gender = ?, password_hash = ? WHERE id = ?");
                    $update->execute([$fullName, $email, $phoneValue, $dateValue, $gender, $newHash, $userId]);
                } else {
                    $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, date_of_birth = ?, gender = ? WHERE id = ?");
                    $update->execute([$fullName, $email, $phoneValue, $dateValue, $gender, $userId]);
                }

                $successMessage = "Your profile has been updated.";

                $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, phone, date_of_birth, gender, created_at FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $error) {
            $errorMessage = "Profile update failed. Please check your table columns.";
        }
    }
}

$initial = strtoupper(mb_substr((string) $user["full_name"], 0, 1));
$memberSince = !empty($user["created_at"]) ? date("M Y", strtotime((string) $user["created_at"])) : "HAZUMI user";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HAZUMI - Settings</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Modak&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    :root {
      --green: #78dc54;
      --green-dark: #2ea714;
      --bg: #f6f9ff;
      --text: #111111;
      --muted: #7c7c82;
      --line: rgba(218, 231, 214, 0.78);
      --shadow: 0 24px 70px rgba(66, 92, 57, 0.10);
    }
    html, body {
      margin: 0;
      width: 100%;
      min-height: 100%;
      color: var(--text);
      font-family: "Nunito", Arial, sans-serif;
      background:
        radial-gradient(circle at 84% 8%, rgba(120, 220, 84, 0.24), transparent 28%),
        radial-gradient(circle at 12% 88%, rgba(255, 241, 129, 0.17), transparent 30%),
        linear-gradient(180deg, #f6f9ff 0%, #f8fbf4 100%);
    }
    a { color: inherit; }
    .settings-app {
      width: 100%;
      min-height: 100vh;
      display: grid;
      grid-template-columns: 284px minmax(0, 1fr);
      gap: 24px;
      padding: 18px 28px 28px 12px;
    }
    .hazumi-sidebar {
      width: 260px;
      height: calc(100vh - 36px);
      min-height: 700px;
      position: sticky;
      top: 18px;
      padding: 22px 14px;
      background: rgba(255, 255, 255, 0.96);
      border: 1px solid rgba(255, 255, 255, 0.9);
      border-radius: 43px;
      box-shadow: 0 18px 45px rgba(66, 92, 57, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.85);
      overflow: hidden;
    }
    .hazumi-brand {
      height: 100px;
      display: flex;
      align-items: center;
      padding: 0 8px;
      text-decoration: none;
    }
    .hazumi-brand img {
      width: 75px;
      height: 75px;
      object-fit: contain;
      flex-shrink: 0;
    }
    .hazumi-brand-title,
    .hazumi-brand-subtitle,
    .hazumi-menu-text,
    .settings-title,
    .profile-name,
    .form-title,
    .save-button,
    .logout-link,
    .avatar {
      font-family: "Modak", system-ui;
      font-weight: 400;
    }
    .hazumi-brand-title {
      margin-left: -2px;
      font-size: 24px;
      line-height: 1;
    }
    .hazumi-brand-subtitle {
      margin-top: 7px;
      margin-left: -2px;
      font-size: 14px;
      line-height: 1;
      white-space: nowrap;
    }
    .hazumi-menu {
      margin-top: 26px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .hazumi-menu-item {
      position: relative;
      width: 232px;
      height: 68px;
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 0 14px;
      text-decoration: none;
      border-radius: 18px;
      overflow: hidden;
      transition: 0.22s ease;
    }
    .hazumi-menu-item::before {
      content: "";
      position: absolute;
      left: 0;
      top: 50%;
      width: 4px;
      height: 0;
      background: var(--green);
      border-radius: 999px;
      transform: translateY(-50%);
      transition: height 0.22s ease;
    }
    .hazumi-menu-icon {
      width: 48px;
      height: 48px;
      display: grid;
      place-items: center;
      flex-shrink: 0;
      border-radius: 16px;
    }
    .hazumi-menu-icon img {
      width: 38px;
      height: 38px;
      display: block;
      object-fit: contain;
    }
    .hazumi-menu-text {
      font-size: 23px;
      line-height: 1;
      white-space: nowrap;
    }
    .hazumi-menu-item:hover,
    .hazumi-menu-item.active {
      background: linear-gradient(90deg, rgba(237, 243, 235, 1) 0%, rgba(235, 253, 231, 1) 100%);
      box-shadow: 0 10px 24px rgba(120, 220, 84, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.75);
      transform: translateX(4px);
    }
    .hazumi-menu-item:hover::before,
    .hazumi-menu-item.active::before {
      height: 38px;
    }
    .hazumi-menu-item:hover .hazumi-menu-icon,
    .hazumi-menu-item.active .hazumi-menu-icon {
      background: rgba(120, 220, 84, 0.18);
    }
    .hazumi-menu-item:hover .hazumi-menu-text,
    .hazumi-menu-item.active .hazumi-menu-text {
      color: var(--green-dark);
    }
    .settings-main {
      min-width: 0;
      padding: 32px 0 0;
    }
    .settings-header {
      max-width: 1080px;
      margin: 0 auto 22px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 20px;
    }
    .eyebrow {
      margin: 0 0 6px;
      color: var(--green-dark);
      font-size: 14px;
      font-weight: 900;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .settings-title {
      margin: 0;
      font-size: clamp(42px, 5vw, 68px);
      line-height: 0.95;
    }
    .settings-subtitle {
      margin: 10px 0 0;
      max-width: 590px;
      color: var(--muted);
      font-size: 16px;
      font-weight: 700;
      line-height: 1.6;
    }
    .logout-link {
      min-width: 120px;
      height: 48px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      background: #111111;
      border-radius: 999px;
      text-decoration: none;
      font-size: 19px;
      line-height: 1;
    }
    .settings-grid {
      max-width: 1080px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 340px minmax(0, 1fr);
      gap: 22px;
      align-items: start;
    }
    .profile-card,
    .form-card {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(255, 255, 255, 0.76));
      border: 1px solid rgba(255, 255, 255, 0.86);
      border-radius: 36px;
      box-shadow: var(--shadow), inset 0 1px 0 rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }
    .profile-card {
      padding: 26px;
      position: sticky;
      top: 28px;
    }
    .avatar {
      width: 108px;
      height: 108px;
      display: grid;
      place-items: center;
      margin-bottom: 18px;
      color: #ffffff;
      background: linear-gradient(180deg, #8cf24b, #67d12d);
      border: 6px solid rgba(255, 255, 255, 0.85);
      border-radius: 32px;
      font-size: 58px;
      line-height: 1;
      box-shadow: 0 18px 40px rgba(120, 220, 84, 0.28);
    }
    .profile-name {
      margin: 0;
      font-size: 33px;
      line-height: 1;
    }
    .profile-email {
      margin: 8px 0 20px;
      color: var(--muted);
      font-size: 14px;
      font-weight: 800;
      overflow-wrap: anywhere;
    }
    .mini-stat {
      padding: 14px 16px;
      margin-top: 10px;
      background: rgba(247, 252, 245, 0.92);
      border: 1px solid var(--line);
      border-radius: 20px;
    }
    .mini-stat span {
      display: block;
      color: var(--muted);
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .mini-stat strong {
      display: block;
      margin-top: 5px;
      font-size: 16px;
      font-weight: 900;
    }
    .form-card { padding: 28px; }
    .form-title {
      margin: 0 0 18px;
      font-size: 32px;
      line-height: 1;
    }
    .message {
      padding: 14px 16px;
      margin-bottom: 18px;
      border-radius: 18px;
      font-size: 14px;
      font-weight: 900;
    }
    .message.success {
      color: #236b11;
      background: rgba(120, 220, 84, 0.16);
      border: 1px solid rgba(120, 220, 84, 0.30);
    }
    .message.error {
      color: #b62626;
      background: rgba(255, 100, 100, 0.12);
      border: 1px solid rgba(255, 100, 100, 0.25);
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    .field { display: flex; flex-direction: column; gap: 8px; }
    .field.full { grid-column: 1 / -1; }
    label {
      color: #2a2f2a;
      font-size: 14px;
      font-weight: 900;
    }
    input, select {
      width: 100%;
      height: 52px;
      padding: 0 16px;
      color: #111111;
      background: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(208, 224, 203, 0.85);
      border-radius: 17px;
      outline: none;
      font-family: "Nunito", Arial, sans-serif;
      font-size: 15px;
      font-weight: 800;
    }
    input:focus, select:focus {
      border-color: rgba(120, 220, 84, 0.75);
      box-shadow: 0 0 0 4px rgba(120, 220, 84, 0.15);
      background: #ffffff;
    }

    .password-box {
      margin-top: 24px;
      padding: 20px;
      background: rgba(248, 252, 247, 0.86);
      border: 1px solid var(--line);
      border-radius: 28px;
    }
    .password-box p {
      margin: -6px 0 16px;
      color: var(--muted);
      font-size: 14px;
      font-weight: 700;
      line-height: 1.5;
    }
    .actions {
      margin-top: 22px;
      display: flex;
      justify-content: flex-end;
    }
    .save-button {
      min-width: 170px;
      height: 56px;
      padding: 0 24px;
      border: none;
      border-radius: 999px;
      color: #071006;
      background: linear-gradient(180deg, #8cf24b, #67d12d);
      font-size: 23px;
      line-height: 1;
      cursor: pointer;
      box-shadow: 0 18px 34px rgba(120, 220, 84, 0.26);
    }
    @media (max-width: 1024px) {
      .settings-app {
        display: block;
        padding: 16px 16px 112px;
      }
      .hazumi-sidebar {
        position: fixed;
        left: 16px;
        right: 16px;
        bottom: 16px;
        top: auto;
        z-index: 1000;
        width: auto;
        height: 76px;
        min-height: 0;
        padding: 8px;
        border-radius: 28px;
        display: flex;
        align-items: center;
      }
      .hazumi-brand { display: none; }
      .hazumi-menu {
        width: 100%;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 6px;
      }
      .hazumi-menu-item {
        width: auto;
        height: 60px;
        justify-content: center;
        padding: 0;
        gap: 0;
        border-radius: 22px;
      }

      .hazumi-menu-item::before { display: none; }
      .hazumi-menu-icon { width: 36px; height: 36px; }
      .hazumi-menu-icon img { width: 28px; height: 28px; }
      .hazumi-menu-text { display: none; }
      .settings-main { padding-top: 18px; }
      .settings-grid { grid-template-columns: 1fr; }
      .profile-card { position: relative; top: 0; }
    }

    @media (max-width: 640px) {
      .settings-header { display: block; }
      .logout-link { margin-top: 16px; }
      .settings-title { font-size: 46px; }
      .form-card { padding: 22px; border-radius: 30px; }
      .form-grid { grid-template-columns: 1fr; }
      .actions { justify-content: stretch; }
      .save-button { width: 100%; }
      .hazumi-menu { grid-template-columns: repeat(5, 1fr); }
      .hazumi-menu-item:nth-child(6) { display: none; }
    }
  </style>
</head>
<body>
  <div class="settings-app">
    <aside class="hazumi-sidebar">
      <a href="dashboard.php" class="hazumi-brand">
        <img src="images/logo.png" alt="Hazumi logo">
        <div>
          <div class="hazumi-brand-title">HAZUMI</div>
          <div class="hazumi-brand-subtitle">Momentum starts today!</div>
        </div>
      </a>
      <nav class="hazumi-menu" aria-label="Main menu">
        <a href="dashboard.php" class="hazumi-menu-item">
          <span class="hazumi-menu-icon"><img src="images/icons/home.png" alt=""></span>
          <span class="hazumi-menu-text">Dashboard</span>
        </a>
        <a href="habittracker.html" class="hazumi-menu-item">
          <span class="hazumi-menu-icon"><img src="images/icons/habits.png" alt=""></span>
          <span class="hazumi-menu-text">Habits</span>
        </a>
        <a href="todolist.html" class="hazumi-menu-item">
          <span class="hazumi-menu-icon"><img src="images/icons/list.png" alt=""></span>
          <span class="hazumi-menu-text">To-do List</span>
        </a>
        <a href="calendar.html" class="hazumi-menu-item">
          <span class="hazumi-menu-icon"><img src="images/icons/calendar.png" alt=""></span>
          <span class="hazumi-menu-text">Calendar</span>
        </a>
        <a href="notes.html" class="hazumi-menu-item">
          <span class="hazumi-menu-icon"><img src="images/icons/notes.png" alt=""></span>
          <span class="hazumi-menu-text">Notes</span>
        </a>
        <a href="setting.php" class="hazumi-menu-item active" aria-current="page">
          <span class="hazumi-menu-icon"><img src="images/icons/settings.png" alt=""></span>
          <span class="hazumi-menu-text">Settings</span>
        </a>
      </nav>
    </aside>
    <main class="settings-main">
      <header class="settings-header">
        <div>
          <p class="eyebrow">Account settings</p>
          <h1 class="settings-title">Settings</h1>
          <p class="settings-subtitle">Update your personal information and change your password whenever you need.</p>
        </div>
        <a href="api/auth/logout.php" class="logout-link">Logout</a>
      </header>
      <section class="settings-grid">
        <aside class="profile-card">
          <div class="avatar"><?php echo e($initial); ?></div>
          <h2 class="profile-name"><?php echo e($user["full_name"]); ?></h2>
          <p class="profile-email"><?php echo e($user["email"]); ?></p>
          <div class="mini-stat"><span>Phone</span><strong><?php echo e($user["phone"] ?: "Not added"); ?></strong></div>
          <div class="mini-stat"><span>Birthday</span><strong><?php echo e($user["date_of_birth"] ?: "Not added"); ?></strong></div>
          <div class="mini-stat"><span>Member since</span><strong><?php echo e($memberSince); ?></strong></div>
        </aside>
        <section class="form-card">
          <h2 class="form-title">Personal information</h2>
          <?php if ($successMessage !== ""): ?>
            <div class="message success"><?php echo e($successMessage); ?></div>
          <?php endif; ?>
          <?php if ($errorMessage !== ""): ?>
            <div class="message error"><?php echo e($errorMessage); ?></div>
          <?php endif; ?>
          <form method="POST" action="setting.php">
            <div class="form-grid">
              <div class="field">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo e($user["full_name"]); ?>" autocomplete="name" required>
              </div>
              <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($user["email"]); ?>" autocomplete="email" required>
              </div>
              <div class="field">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo e($user["phone"]); ?>" placeholder="+81 90 0000 0000" autocomplete="tel">
              </div>
              <div class="field">
                <label for="date_of_birth">Date of birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo e($user["date_of_birth"]); ?>">
              </div>
              <div class="field full">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                  <option value="prefer_not_to_say" <?php echo $user["gender"] === "prefer_not_to_say" ? "selected" : ""; ?>>Prefer not to say</option>
                  <option value="female" <?php echo $user["gender"] === "female" ? "selected" : ""; ?>>Female</option>
                  <option value="male" <?php echo $user["gender"] === "male" ? "selected" : ""; ?>>Male</option>
                  <option value="other" <?php echo $user["gender"] === "other" ? "selected" : ""; ?>>Other</option>
                </select>
              </div>
            </div>
            <div class="password-box">
              <h2 class="form-title">Change password</h2>
              <p>Leave these fields empty if you do not want to change your password.</p>
              <div class="form-grid">
                <div class="field full">
                  <label for="current_password">Current password</label>
                  <input type="password" id="current_password" name="current_password" autocomplete="current-password" placeholder="Enter current password">
                </div>
                <div class="field">
                  <label for="new_password">New password</label>
                  <input type="password" id="new_password" name="new_password" autocomplete="new-password" placeholder="New password">
                </div>

                <div class="field">
                  <label for="confirm_password">Confirm password</label>
                  <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" placeholder="Repeat new password">
                </div>
              </div>
            </div>
            <div class="actions">
              <button type="submit" class="save-button">Save changes</button>
            </div>
          </form>
        </section>
      </section>
    </main>
  </div>
</body>
</html>