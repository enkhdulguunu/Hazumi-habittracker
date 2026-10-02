const signupForm = document.getElementById("signupForm");
    const submitBtn = document.getElementById("submitBtn");
    const messageBox = document.getElementById("messageBox");
    const loginButton = document.querySelector(".log-in");

    loginButton?.addEventListener("click", () => {
      window.location.href = "login.php";
    });

    signupForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      messageBox.textContent = "";
      messageBox.className = "message";
      const fullName = document.getElementById("fullName").value.trim();
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirmPassword").value;
      const terms = document.getElementById("terms").checked;

      if (!fullName || !email || !password || !confirmPassword) {
        showMessage("Please fill in all fields.", "error");
        return;
      }

      if (password.length < 6) {
        showMessage("Password must be at least 6 characters.", "error");
        return;
      }

      if (password !== confirmPassword) {
        showMessage("Passwords do not match.", "error");
        return;
      }

      if (!terms) {
        showMessage("Please agree to the terms.", "error");
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = "Creating...";

      try {
        const response = await fetch("api/auth/register.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            full_name: fullName,
            email: email,
            password: password
          })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          showMessage(result.message || "Sign up failed.", "error");
          submitBtn.disabled = false;
          submitBtn.textContent = "Create account";
          return;
        }

        showMessage("Account created successfully.", "success");

        setTimeout(() => {
          window.location.href = "dashboard.php";
        }, 700);
      } catch (error) {
        showMessage("Server error. Please try again.", "error");
        submitBtn.disabled = false;
        submitBtn.textContent = "Create account";
      }
    });

    function showMessage(text, type) {
      messageBox.textContent = text;
      messageBox.className = `message ${type}`;
    }
