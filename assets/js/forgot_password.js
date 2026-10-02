const forgotPasswordForm = document.getElementById("forgotPasswordForm");
    const submitBtn = document.getElementById("submitBtn");
    const messageBox = document.getElementById("messageBox");
    const resetLinkBox = document.getElementById("resetLinkBox");
    const resetLink = document.getElementById("resetLink");
    function showMessage(message, type) {
      messageBox.textContent = message;
      messageBox.className = `message ${type}`;
    }

    forgotPasswordForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const email = document.getElementById("email").value.trim();

      resetLinkBox.style.display = "none";
      resetLink.removeAttribute("href");
      resetLink.textContent = "";
      submitBtn.disabled = true;
      submitBtn.textContent = "Sending...";

      try {
        const response = await fetch("api/auth/request_password_reset.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ email })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          showMessage(result.message || "Password reset request failed.", "error");
          return;
        }

        showMessage(result.message, "success");

        if (result.reset_link) {
          resetLink.href = result.reset_link;
          resetLink.textContent = result.reset_link;
          resetLinkBox.style.display = "block";
        }
      } catch (error) {
        showMessage("Server error. Please try again.", "error");
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = "Send reset link";
      }
    });
