document.addEventListener("DOMContentLoaded", () => {
    const nav = document.querySelector(".frame-1");
    const navLinks = document.querySelectorAll(".frame-1 a");
    const loginButtons = document.querySelectorAll(".log-in, .log-in2");
    const signupButtons = document.querySelectorAll(".sign-up, .sign-up2");
    const sectionMap = Array.from(navLinks)
      .map((link) => {
        const section = document.querySelector(link.getAttribute("href"));
        return { link, section };
      })
      .filter((item) => item.section);

    loginButtons.forEach((button) => {
      button.addEventListener("click", () => {
        window.location.href = "login.php";
      });
    });

    signupButtons.forEach((button) => {
      button.addEventListener("click", () => {
        window.location.href = "signup.php";
      });
    });

    function moveNavLine(activeLink) {
      navLinks.forEach((link) => link.classList.remove("active"));
      activeLink.classList.add("active");

      nav.style.setProperty("--nav-left", activeLink.offsetLeft + "px");
      nav.style.setProperty("--nav-width", activeLink.offsetWidth + "px");
    }

    function updateActiveNav() {
      const scrollPosition = window.scrollY;
      const windowHeight = window.innerHeight;
      const pageBottom = scrollPosition + windowHeight;
      const documentHeight = document.documentElement.scrollHeight;

      document.body.classList.toggle("is-index-scrolled", scrollPosition > 24);

      let currentItem = sectionMap[0];

      sectionMap.forEach((item) => {
        const sectionTop = item.section.offsetTop;

        if (scrollPosition + windowHeight * 0.55 >= sectionTop) {
          currentItem = item;
        }

        if (
          item.section.id === "join" &&
          pageBottom >= documentHeight - 30
        ) {
          currentItem = item;
        }
      });

      moveNavLine(currentItem.link);
    }

    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        moveNavLine(link);

        setTimeout(() => {
          updateActiveNav();
        }, 350);
      });
    });

    let ticking = false;

    window.addEventListener(
      "scroll",
      () => {
        if (!ticking) {
          window.requestAnimationFrame(() => {
            updateActiveNav();
            ticking = false;
          });

          ticking = true;
        }
      },
      { passive: true }
    );

    window.addEventListener("resize", updateActiveNav);

    updateActiveNav();
  });
