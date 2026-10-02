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
  <title>HAZUMI - Forgot password</title>
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
    .page {
      width: 100%;
      min-height: 100vh;
      padding: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 2;
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
    .brand,
    form,
    .bottom-text,
    .reset-link-box {
      position: relative;
      z-index: 1;
    }
    .brand {
      margin-bottom: 26px;
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
      max-width: 380px;
      color: var(--muted);
      font-size: 14px;
      font-weight: 700;
      line-height: 1.55;
    }
    .form-group {
      margin-bottom: 16px;
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
    .reset-link-box {
      display: none;
      margin-top: 14px;
      padding: 14px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.13);
      color: var(--muted);
      font-size: 13px;
      font-weight: 700;
      line-height: 1.45;
    }
    .reset-link-box a {
      display: inline-block;
      margin-top: 8px;
      color: #a2f36f;
      font-weight: 900;
      text-decoration: none;
      overflow-wrap: anywhere;
    }
    .reset-link-box a:hover,
    .bottom-text a:hover {
      text-decoration: underline;
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
        <p>Enter your email and we will send you a secure link to reset your password.</p>
      </div>
      <form id="forgotPasswordForm">
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
        <button type="submit" class="submit-btn" id="submitBtn">Send reset link</button>
        <div class="message" id="messageBox"></div>
      </form>
      <div class="reset-link-box" id="resetLinkBox">
        Localhost test link:
        <a href="#" id="resetLink"></a>
      </div>
      <div class="bottom-text">
        Remember your password?
        <a href="login.php">Log in</a>
      </div>
    </section>
  </main>
  <script src="assets/js/forgot_password.js"></script>
</body>
</html>
