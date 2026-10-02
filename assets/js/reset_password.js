const resetPasswordForm = document.getElementById("resetPasswordForm");
    const submitBtn = document.getElementById("submitBtn");
    const messageBox = document.getElementById("messageBox");
    const params = new URLSearchParams(window.location.search);
    const token = params.get("token") || "";

    function showMessage(message, type) {
      messageBox.textContent = message;
      messageBox.className = `message ${type}`;
    }

    if (!token) {
      showMessage("Reset link is missing or invalid.", "error");
      submitBtn.disabled = true;
    }

    resetPasswordForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirmPassword").value;

      if (password.length < 6) {
        showMessage("Password must be at least 6 characters.", "error");
        return;
      }

      if (password !== confirmPassword) {
        showMessage("Passwords do not match.", "error");
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = "Resetting...";

      try {
        const response = await fetch("api/auth/reset_password.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            token,
            password,
            confirm_password: confirmPassword
          })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          showMessage(result.message || "Password reset failed.", "error");
          return;
        }

        showMessage(result.message, "success");

        window.setTimeout(() => {
          window.location.href = "login.php";
        }, 1200);
      } catch (error) {
        showMessage("Server error. Please try again.", "error");
      } finally {
        if (!messageBox.classList.contains("success")) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Reset password";
        }
      }
    });
