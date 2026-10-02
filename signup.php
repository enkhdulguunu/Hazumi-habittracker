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
  <title>HAZUMI - Sign up</title>
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
      --white: rgba(255, 255, 255, 0.92);
      --glass: rgba(255, 255, 255, 0.09);
      --glass-2: rgba(255, 255, 255, 0.055);
      --border: rgba(255, 255, 255, 0.15);
      --text: #ffffff;
      --muted: rgba(255, 255, 255, 0.72);
      --danger: #ff7373;
      --success: #9af873;
      --shadow: 0 25px 65px rgba(0, 0, 0, 0.38);
    }
    html,
    body {
      width: 100%;
      min-height: 100%;
    }
    body {
      min-height: 100vh;
      color: var(--text);
      font-family: "Nunito", Arial, sans-serif;
      overflow-x: hidden;
      overflow-y: auto;
      position: relative;
      background:
        radial-gradient(circle at 16% 88%, rgba(123, 225, 59, 0.58) 0%, rgba(123, 225, 59, 0.20) 22%, transparent 44%),
        radial-gradient(circle at 36% 42%, rgba(123, 225, 59, 0.44) 0%, rgba(123, 225, 59, 0.14) 18%, transparent 35%),
        radial-gradient(circle at 80% 66%, rgba(123, 225, 59, 0.42) 0%, rgba(123, 225, 59, 0.12) 17%, transparent 30%),
        radial-gradient(circle at 95% 94%, rgba(123, 225, 59, 0.30) 0%, transparent 22%),
        #030507;
    }
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      pointer-events: none;
      opacity: 0.13;
      background-image:
        radial-gradient(rgba(255,255,255,0.24) 0.8px, transparent 0.8px);
      background-size: 7px 7px;
      mix-blend-mode: soft-light;
    }
    body::after {
      content: "";
      position: fixed;
      inset: 0;
      pointer-events: none;
      background:
        linear-gradient(to bottom right, rgba(255,255,255,0.025), rgba(255,255,255,0));
      backdrop-filter: blur(1px);
      -webkit-backdrop-filter: blur(1px);
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
      padding: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
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
    .auth-card {
      width: 100%;
      max-width: 470px;
      padding: 34px 30px 28px;
      position: relative;
      overflow: hidden;
      background: linear-gradient(180deg, var(--glass), var(--glass-2));
      border: 1px solid var(--border);
      border-radius: 32px;
      box-shadow:
        var(--shadow),
        inset 0 1px 0 rgba(255, 255, 255, 0.13);
      backdrop-filter: blur(26px);
      -webkit-backdrop-filter: blur(26px);
    }
    .auth-card::before {
      content: "";
      position: absolute;
      width: 190px;
      height: 190px;
      right: -70px;
      top: -70px;
      border-radius: 50%;
      background: rgba(123, 225, 59, 0.16);
      filter: blur(32px);
      pointer-events: none;
    }
    .auth-card::after {
      content: "";
      position: absolute;
      width: 150px;
      height: 150px;
      left: -76px;
      bottom: -74px;
      border-radius: 50%;
      background: rgba(123, 225, 59, 0.13);
      filter: blur(34px);
      pointer-events: none;
    }
    .brand {
      margin-bottom: 26px;
      position: relative;
      z-index: 1;
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
      max-width: 360px;
      color: var(--muted);
      font-size: 14px;
      font-weight: 700;
      line-height: 1.55;
    }
    .top-buttons {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 24px;
      position: relative;
      z-index: 1;
    }
    .log-in,
    .sign-up {
      height: 50px;
      border: none;
      border-radius: 16px;
      font-family: "Modak", system-ui;
      font-size: 22px;
      font-weight: 400;
      line-height: 1;
      cursor: pointer;
      transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
    }
    .sign-up {
      color: #081006;
      background: linear-gradient(180deg, #8CF24B, #67D12D);
      box-shadow: 0 14px 28px rgba(123, 225, 59, 0.28);
    }
    .sign-up:hover {
      transform: translateY(-2px);
      box-shadow: 0 18px 34px rgba(123, 225, 59, 0.35);
    }
    .log-in {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.14);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }
    .log-in:hover {
      background: rgba(255, 255, 255, 0.13);
      transform: translateY(-2px);
    }
    form {
      position: relative;
      z-index: 1;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-label {
      display: block;
      margin-bottom: 8px;
      color: rgba(255, 255, 255, 0.88);
      font-size: 14px;
      font-weight: 800;
    }
    .form-input {
      width: 100%;
      height: 52px;
      padding: 0 16px;
      color: #ffffff;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.13);
      border-radius: 16px;
      outline: none;
      font-size: 15px;
      font-weight: 700;
      transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
    }
    .form-input::placeholder {
      color: rgba(255, 255, 255, 0.44);
    }
    .form-input:focus {
      border-color: rgba(123, 225, 59, 0.62);
      box-shadow: 0 0 0 4px rgba(123, 225, 59, 0.12);
      background: rgba(255, 255, 255, 0.09);
    }
    .terms {
      margin: 4px 0 18px;
      display: flex;
      align-items: flex-start;
      gap: 9px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 700;
      line-height: 1.45;
    }
    .terms input {
      margin-top: 2px;
      accent-color: var(--green);
      flex-shrink: 0;
    }
    .terms a {
      color: #a5f36f;
      text-decoration: none;
      font-weight: 900;
    }
    .terms a:hover {
      text-decoration: underline;
    }
    .submit-btn {
      width: 100%;
      height: 54px;
      border: none;
      border-radius: 18px;
      color: #081006;
      background: linear-gradient(180deg, #8CF24B, #67D12D);
      font-family: "Modak", system-ui;
      font-size: 23px;
      font-weight: 400;
      cursor: pointer;
      transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        opacity 0.25s ease;
      box-shadow: 0 18px 35px rgba(123, 225, 59, 0.28);
    }
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 23px 42px rgba(123, 225, 59, 0.34);
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
      color: var(--success);
    }
    .bottom-text {
      margin-top: 18px;
      text-align: center;
      color: var(--muted);
      font-size: 14px;
      font-weight: 700;
      position: relative;
      z-index: 1;
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
      .page {
        padding: 18px;
      }
      .auth-card {
        padding: 28px 20px 22px;
        border-radius: 26px;
      }
      .brand h1 {
        font-size: 36px;
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
    }
  </style>
</head>
<body>
  <main class="page">
    <section class="auth-card">
      <div class="brand">
        <a href="index.html" class="brand-home-link" aria-label="Hazumi home">
          <h1>HAZUMI</h1>
        </a>
        <p>Create your account and start tracking your habits, tasks, notes and calendar.</p>
      </div>
      <div class="top-buttons">
        <button type="button" class="log-in">Log in</button>
        <button type="button" class="sign-up">Sign up</button>
      </div>
      <form id="signupForm">
        <div class="form-group">
          <label for="fullName" class="form-label">Full name</label>
          <input
            type="text"
            id="fullName"
            name="full_name"
            class="form-input"
            placeholder="Enter your name"
            autocomplete="name"
            required
          >
        </div>
        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-input"
            placeholder="Enter your email"
            autocomplete="email"
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
            placeholder="Create a password"
            autocomplete="new-password"
            required
          >
        </div>
        <div class="form-group">
          <label for="confirmPassword" class="form-label">Confirm password</label>
          <input
            type="password"
            id="confirmPassword"
            name="confirm_password"
            class="form-input"
            placeholder="Repeat your password"
            autocomplete="new-password"
            required
          >
        </div>
        <label class="terms">
          <input type="checkbox" id="terms" required>
          <span>
            I agree to the <a href="terms.html">Terms</a> and <a href="privacy.html">Privacy Policy</a>.
          </span>
        </label>
        <button type="submit" class="submit-btn" id="submitBtn">Create account</button>
        <div class="message" id="messageBox"></div>
      </form>
      <div class="bottom-text">
        Already have an account?
        <a href="login.php">Log in</a>
      </div>
    </section>
  </main>
  <script src="assets/js/signup.js"></script>
</body>
</html>
