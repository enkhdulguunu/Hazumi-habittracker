[English](README.md) | [日本語](README.ja.md)

# Hazumi

Hazumi is a web application for managing habits, to-do tasks, calendar events, time blocks, and notes. The application has already been deployed to a server and can be used directly from a browser.

## Site

Hazumi is available here:

[https://gict.xsrv.jp/dulguun_1224/Hazumi/index.html]

## Purpose

Hazumi is designed to help users organize daily productivity data in one place. Instead of managing habits, tasks, schedules, and notes in separate tools, users can manage them together through an account-based web application.

The current implementation mainly supports:

- tracking recurring habits and completed habit days;
- managing personal to-do tasks;
- managing dated calendar events and dashboard time blocks;
- saving personal notes.

### Completed Features

- Public landing page with Home, About, Feature, and Join sections.
- User registration and login.
- Authentication.
- Password handling with password_hash() and password_verify().
- Logout process that destroys the PHP session and returns to the landing page.
- Logged-in dashboard showing habits, to-dos, progress, and time blocks.
- Settings page for profile editing and optional password changes.
- MySQL-backed To-do List:
  - create tasks;
  - edit task text;
  - mark tasks complete or incomplete;
  - delete individual tasks;
  - delete all tasks or completed tasks for the current user;
  - share the same saved data between the dashboard and To-do List page.
- MySQL-backed Habit Tracker:
  - default habits for new users;
  - add, rename, and delete habits;
  - check habit completion by date;
  - edit the habit tracker title;
  - share the same saved data between the dashboard and Habit Tracker page.
- MySQL-backed Calendar:
  - day, week, month, and year view modes;
  - add and delete dated tasks;
  - update calendar slot data from the dashboard time-block section.
- MySQL-backed Notes:
  - create, edit, search, and delete notes;
  - near-autosave editing behavior on the Notes page.
- Terms and Privacy Policy pages.
- Page-specific JavaScript files placed under assets/js/.

## Technologies and Dependencies

- PHP, PHP sessions, PDO
- MySQL / MariaDB
- HTML, CSS, Vanilla JavaScript
- Fetch API for browser-to-API communication
- Google Fonts:
  - Modak
  - Nunito
  - Fredoka One

## Directory Structure

Hazumi/
├── api/
│   ├── auth/          # Login, registration, logout, password reset, user API
│   ├── calendar/      # Calendar event APIs and dashboard slot update
│   ├── habits/        # Habit and habit log APIs
│   ├── notes/         # Notes CRUD API
│   ├── timeblocks/    # Time-block API
│   ├── todos/         # To-do CRUD and bulk action API
│   └── common.php     # Shared JSON/API helpers and table creation helpers
├── assets/js/         # Page-specific JavaScript files
├── config/
│   └── database.php   # PDO database connection configuration
├── images/            # Landing page images, logo, and sidebar icons
├── sql/
│   └── schema.sql     # Main database schema
├── index.html         # Public landing page
├── login.php          # Login page
├── signup.php         # Registration page
├── dashboard.php      # Logged-in dashboard
├── habittracker.html  # Habit tracker page
├── todolist.html      # To-do list page
├── calendar.html      # Calendar page
├── notes.html         # Notes page
├── setting.php        # Account settings page
├── terms.html         # Terms page
└── privacy.html       # Privacy policy page

## How to Use the Live Site

1. Open [Hazumi](https://gict.xsrv.jp/dulguun_1224/Hazumi/) in a browser.
2. Create an account from **Sign up**.
3. Log in with the registered email address and password.
4. Use the dashboard to check habit progress, to-dos, and time blocks.
5. Open Habit Tracker, To-do List, Calendar, Notes, and Settings from the sidebar.
6. Update profile information or change the password from Settings.
7. Use Logout to end the session.