const loginForm = document.getElementById("loginForm");
  const submitBtn = document.getElementById("submitBtn");
  const messageBox = document.getElementById("messageBox");
  const signupButton = document.querySelector(".sign-up");

  signupButton?.addEventListener("click", () => {
    window.location.href = "signup.php";
  });

  loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    submitBtn.disabled = true;
    submitBtn.textContent = "Logging in...";
    try {
      const response = await fetch("api/auth/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          email: email,
          password: password
        })
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        messageBox.textContent = result.message || "Login failed.";
        messageBox.className = "message error";
        submitBtn.disabled = false;
        submitBtn.textContent = "Log in";
        return;
      }
      messageBox.textContent = "Logged in successfully.";
      messageBox.className = "message success";
      window.location.href = "dashboard.php";
    } catch (error) {
      messageBox.textContent = "Server error. Please try again.";
      messageBox.className = "message error";
      submitBtn.disabled = false;
      submitBtn.textContent = "Log in";
    }
  });
