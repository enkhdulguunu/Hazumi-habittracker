<?php
declare(strict_types=1);
session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HAZUMI - Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Modak&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    :root {
      --green: #7BE13B;
      --green-dark: #4FAE19;
      --green-soft: rgba(123, 225, 59, 0.22);
      --white: rgba(255, 255, 255, 0.9);
      --glass: rgba(255, 255, 255, 0.09);
      --glass-2: rgba(255, 255, 255, 0.06);
      --border: rgba(255, 255, 255, 0.15);
      --text: #ffffff;
      --muted: rgba(255, 255, 255, 0.72);
      --danger: #ff6b6b;
      --shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    }
    body {
      min-height: 100vh;
      font-family: "Nunito", Arial, sans-serif;
      color: var(--text);
      overflow: hidden;
      position: relative;
      background:
        radial-gradient(circle at 20% 88%, rgba(123, 225, 59, 0.55) 0%, rgba(123, 225, 59, 0.18) 20%, transparent 42%),
        radial-gradient(circle at 38% 44%, rgba(123, 225, 59, 0.45) 0%, rgba(123, 225, 59, 0.14) 18%, transparent 34%),
        radial-gradient(circle at 78% 68%, rgba(123, 225, 59, 0.40) 0%, rgba(123, 225, 59, 0.11) 16%, transparent 28%),
        radial-gradient(circle at 92% 95%, rgba(123, 225, 59, 0.28) 0%, transparent 20%),
        #030507;
    }
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      pointer-events: none;
      opacity: 0.12;
      background-image:
        radial-gradient(rgba(255,255,255,0.22) 0.8px, transparent 0.8px);
      background-size: 7px 7px;
      mix-blend-mode: soft-light;
    }
    body::after {
      content: "";
      position: absolute;
      inset: 0;
      pointer-events: none;
      background:
      linear-gradient(to bottom right, rgba(255,255,255,0.02), rgba(255,255,255,0));
      backdrop-filter: blur(1px);
    }
    @keyframes authGlowDrift {
      0% {
        opacity: 0.22;
        transform: translate3d(-12%, 9%, 0) scale(0.92);
      }
      50% {
        opacity: 0.82;
        transform: translate3d(10%, -8%, 0) scale(1.14);
      }
      100% {
        opacity: 0.34;
        transform: translate3d(15%, 10%, 0) scale(1.02);
      }
    }
    .page {
      width: 100%;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      z-index: 2;
      isolation: isolate;
    }
    .page::before,
    .page::after {
      content: "";
      position: fixed;
      inset: -18%;
      z-index: -1;
      pointer-events: none;
      filter: blur(78px);
      will-change: transform;
    }
    .page::before {
      opacity: 0.68;
      background:
        radial-gradient(circle at 22% 72%, rgba(123, 225, 59, 0.42) 0%, rgba(123, 225, 59, 0.20) 24%, transparent 46%),
        radial-gradient(circle at 74% 58%, rgba(123, 225, 59, 0.30) 0%, rgba(123, 225, 59, 0.13) 20%, transparent 42%);
      animation: authGlowDrift 7s ease-in-out infinite;
    }
    .page::after {
      opacity: 0.42;
      background:
        radial-gradient(circle at 42% 38%, rgba(145, 255, 74, 0.34) 0%, rgba(145, 255, 74, 0.13) 18%, transparent 38%),
        radial-gradient(circle at 86% 88%, rgba(91, 210, 48, 0.24) 0%, transparent 34%);
      animation: authGlowDrift 10s ease-in-out infinite reverse;
    }
    @media (prefers-reduced-motion: reduce) {
      .page::before,
      .page::after {
        animation: none;
      }
    }
    .login-card {
      width: 100%;
      max-width: 450px;
      padding: 34px 30px 28px;
      border-radius: 30px;
      background: linear-gradient(180deg, var(--glass), var(--glass-2));
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      position: relative;
      overflow: hidden;
    }
    .login-card::before {
      content: "";
      position: absolute;
      width: 180px;
      height: 180px;
      right: -50px;
      top: -60px;
      border-radius: 50%;
      background: rgba(123, 225, 59, 0.14);
      filter: blur(30px);
      pointer-events: none;
    }
    .brand {
      margin-bottom: 28px;
    }
    .brand h1 {
      font-family: "Modak", system-ui;
      font-size: 44px;
      font-weight: 400;
      letter-spacing: 1px;
      line-height: 1;
      margin-bottom: 8px;
    }
    .brand-home-link {
      color: inherit;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
    }
    .brand p {
      color: var(--muted);
      font-size: 14px;
      font-weight: 700;
      line-height: 1.5;
    }
    .top-buttons {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 24px;
    }
    .log-in,
    .sign-up {
      height: 48px;
      border: none;
      border-radius: 16px;
      font-family: "Modak", system-ui;
      font-size: 22px;
      font-weight: 400;
      line-height: 1;
      cursor: pointer;
      transition: 0.25s ease;
    }
    .log-in {
      color: #081006;
      background: linear-gradient(180deg, #8CF24B, #67D12D);
      box-shadow: 0 12px 26px rgba(123, 225, 59, 0.28);
    }
    .log-in:hover {
      transform: translateY(-2px);
      box-shadow: 0 16px 30px rgba(123, 225, 59, 0.35);
    }
    .sign-up {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.14);
      backdrop-filter: blur(10px);
    }
    .sign-up:hover {
      background: rgba(255, 255, 255, 0.13);
      transform: translateY(-2px);
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 800;
      color: rgba(255, 255, 255, 0.88);
    }
    .form-input {
      width: 100%;
      height: 52px;
      border: 1px solid rgba(255, 255, 255, 0.13);
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.07);
      padding: 0 16px;
      font-size: 15px;
      font-weight: 700;
      color: white;
      outline: none;
      transition: 0.25s ease;
    }
    .form-input::placeholder {
      color: rgba(255, 255, 255, 0.45);
    }
    .form-input:focus {
      border-color: rgba(123, 225, 59, 0.6);
      box-shadow: 0 0 0 4px rgba(123, 225, 59, 0.12);
      background: rgba(255, 255, 255, 0.09);
    }
    .options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 18px;
      font-size: 13px;
      font-weight: 700;
      color: var(--muted);
    }
    .remember-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .remember-wrap input {
      accent-color: var(--green);
    }
    .forgot-link {
      color: #9df16c;
      text-decoration: none;
      font-weight: 700;
    }
    .forgot-link:hover {
      text-decoration: underline;
    }
    .submit-btn {
      width: 100%;
      height: 54px;
      border: none;
      border-radius: 18px;
      background: linear-gradient(180deg, #8CF24B, #67D12D);
      color: #081006;
      font-family: "Modak", system-ui;
      font-size: 23px;
      font-weight: 400;
      cursor: pointer;
      transition: 0.25s ease;
      box-shadow: 0 18px 35px rgba(123, 225, 59, 0.28);
    }
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 22px 40px rgba(123, 225, 59, 0.34);
    }
    .submit-btn:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }
    .message {
      margin-top: 16px;
      min-height: 22px;
      font-size: 14px;
      font-weight: 800;
    }
    .message.error {
      color: var(--danger);
    }
    .message.success {
      color: #99f87e;
    }
    .bottom-text {
      margin-top: 18px;
      text-align: center;
      color: var(--muted);
      font-size: 14px;
      font-weight: 700;
    }
    .bottom-text a {
      color: #a2f36f;
      text-decoration: none;
      font-weight: 900;
    }
    .bottom-text a:hover {
      text-decoration: underline;
    }
    @media (max-width: 768px) {
      body {
        overflow-y: auto;
      }
      .page {
        padding: 18px;
      }
      .login-card {
        padding: 28px 20px 22px;
        border-radius: 24px;
      }
      .brand h1 {
        font-size: 36px;
      }
      .top-buttons {
        gap: 10px;
      }
      .log-in,
      .sign-up,
      .submit-btn,
      .form-input {
        height: 50px;
      }
    }
    @media (max-width: 420px) {
      .brand h1 {
        font-size: 32px;
      }
      .brand p {
        font-size: 13px;
      }
      .top-buttons {
        grid-template-columns: 1fr;
      }
      .options {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="login-card">
      <div class="brand">
        <a href="index.html" class="brand-home-link" aria-label="Hazumi home">
          <h1>HAZUMI</h1>
        </a>
        <p>Welcome back. Log in to continue your habits, notes, tasks and calendar.</p>
      </div>
      <div class="top-buttons">
        <button type="button" class="log-in">Log in</button>
        <button type="button" class="sign-up">Sign up</button>
      </div>
      <form id="loginForm">
        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-input"
            placeholder="Enter your email"
            required
          >
        </div>
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-input"
            placeholder="Enter your password"
            required
          >
        </div>
        <div class="options">
          <label class="remember-wrap">
            <input type="checkbox" name="remember">
            <span>Remember me</span>
          </label>
          <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
        </div>
        <button type="submit" class="submit-btn" id="submitBtn">Log in</button>
        <div class="message" id="messageBox"></div>
      </form>
      <div class="bottom-text">
        Don’t have an account?
        <a href="signup.php">Create one</a>
      </div>
    </div>
  </div>
  <script src="assets/js/login.js"></script>
</body>
</html>
