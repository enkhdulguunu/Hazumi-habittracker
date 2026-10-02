<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>dashboard</title>
  <link rel="stylesheet" href="mystyle.css?v=20260728-responsive-logout-1">
</head>
<body>
  <div class="dashboard-screen">
    <div class="dashboard">
    <div class="rectangle-5"></div>
    <div class="rectangle-4"></div>
    <button
      type="button"
      class="time-block"
      id="openCalendarButton"
      title="Open calendar"
      aria-label="Open calendar"
    >
      <span class="time-block-label">TIME BLOCK</span>
      <span class="time-block-hint" aria-hidden="true">Calendar</span>
    </button>
    <div class="hello">HELLO!</div>
    <a
      href="api/auth/logout.php"
      class="dashboard-logout-button"
      title="Logout"
      aria-label="Logout"
    >
      <span class="logout-power-icon"></span>
    </a>
    <!-- Date time -->
    <div class="glass-bg-shape glass-bg-shape-1"></div>
    <div class="glass-bg-shape glass-bg-shape-2"></div>
    <div class="glass-bg-shape glass-bg-shape-3"></div>
    <div class="glass-datetime-card">
      <div class="glass-time-wrap">
        <div class="glass-time" id="liveTime">00:00</div>
        <div class="glass-seconds" id="liveSeconds">00</div>
      </div>
      <div class="glass-divider"></div>
      <div class="glass-date-wrap">
        <div class="glass-date" id="liveDate">Monday, June 30</div>
        <div class="glass-year" id="liveYear">2026</div>
      </div>
    </div>
    <img class="hazumi-1" src="images/logo.png" alt="Hazumi logo">
    <div class="hazumi">HAZUMI</div>
    <div class="momentum-starts-today">Momentum starts today!</div>
    <div class="rectangle-6"></div>
    <div class="rectangle-7"></div>
    <div class="rectangle-8"></div>
    <div class="rectangle-9"></div>
    <div class="rectangle-10"></div>
    <div class="rectangle-11"></div>
    <div class="rectangle-12"></div>
    <!-- Time block -->
    <div class="timeblock-list" id="timeblockList"></div>
    <!-- Habit tracker -->
    <div
      class="habit-tracker"
      id="habitTitle"
      contenteditable="true"
      spellcheck="false"
    >Habit tracker</div>
    <button type="button" id="openHabitAddButton" class="habit-add-icon">+</button>
    <div class="habit-add-popover" id="habitAddPopover">
      <input
        type="text"
        id="habitInput"
        placeholder="Add habit..."
        autocomplete="off"
      >
      <button type="button" id="addHabitButton">Add</button>
    </div>
    <div class="habit-month-controls">
      <button type="button" id="prevMonthButton">‹</button>
      <div id="habitMonthTitle"></div>
      <button type="button" id="nextMonthButton">›</button>
    </div>
    <div class="habit-scroll">
      <div class="habit-grid" id="habitGrid"></div>
    </div>
    <!-- To-do list -->
    <div class="to-do-list">To-do list</div>
    <div class="todo-panel">
      <div class="todo-form">
        <input
          type="text"
          id="todoInput"
          placeholder="Add a new task..."
          autocomplete="off"
        >
        <button type="button" id="addTodoButton">+</button>
        <button type="button" id="clearAllButton" class="clear-all-button">
          Clear All
        </button>
      </div>
      <ul class="todo-items" id="todoItems"></ul>
    </div>
    <!-- Stats cards -->
    <div class="rectangle-19"></div>
    <div class="rectangle-20"></div>
    <div class="rectangle-21"></div>
    <div class="rectangle-22"></div>
    <div class="day-streak">Day Streak</div>
    <div class="tasks-completed">
      Tasks
      <br>
      Completed
    </div>
    <div class="overall-progress">
      Overall
      <br>
      Progress
    </div>
    <div class="task-left">
      Task
      <br>
      Left
    </div>
    <div class="_1" id="dayStreakCount">0</div>
    <div class="_12" id="tasksCompletedCount">0</div>
    <div class="_13" id="overallProgressCount">0%</div>
    <div class="_14" id="taskLeftCount">0</div>
    <img class="streak-1" src="images/icons/streak.png" alt="streak">
    <img class="habitcompleted-1" src="images/icons/habitcompleted.png" alt="habit completed">
    <img class="progress-1" src="images/icons/progress.png" alt="progress">
    <img class="task-1" src="images/icons/task.png" alt="task">
    <!-- Sidebar -->
    <aside class="hazumi-sidebar">
    <a href="dashboard.php" class="hazumi-brand">
    <img src="images/logo.png" alt="Hazumi logo">
    <div>
      <div class="hazumi-brand-title">HAZUMI</div>
      <div class="hazumi-brand-subtitle">Momentum starts today!</div>
    </div>
    </a>
    <nav class="hazumi-menu" aria-label="Main menu">
    <a href="dashboard.php" class="hazumi-menu-item active" aria-current="page">
      <span class="hazumi-menu-icon">
        <img src="images/icons/home.png" alt="">
      </span>
      <span class="hazumi-menu-text">Dashboard</span>
    </a>
    <a href="habittracker.html" class="hazumi-menu-item">
      <span class="hazumi-menu-icon">
        <img src="images/icons/habits.png" alt="">
      </span>
      <span class="hazumi-menu-text">Habits</span>
    </a>

    <a href="todolist.html" class="hazumi-menu-item">
      <span class="hazumi-menu-icon">
        <img src="images/icons/list.png" alt="">
      </span>
      <span class="hazumi-menu-text">To-do List</span>
    </a>
    <a href="calendar.html" class="hazumi-menu-item">
      <span class="hazumi-menu-icon">
        <img src="images/icons/calendar.png" alt="">
      </span>
      <span class="hazumi-menu-text">Calendar</span>
    </a>
    <a href="notes.html" class="hazumi-menu-item">
      <span class="hazumi-menu-icon">
        <img src="images/icons/notes.png" alt="">
      </span>
      <span class="hazumi-menu-text">Notes</span>
    </a>
    <a href="setting.php" class="hazumi-menu-item">
    <span class="hazumi-menu-icon">
    <img src="images/icons/settings.png" alt="">
    </span>
   <span class="hazumi-menu-text">Settings</span>
  </a>
  </nav>
  </aside>
    <div
      class="logout-modal"
      id="logoutModal"
      aria-hidden="true"
    >
      <div class="logout-modal-backdrop" data-logout-close></div>
      <section
        class="logout-modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logoutModalTitle"
      >
        <div class="logout-modal-icon">
          <span class="logout-power-icon"></span>
        </div>
        <h2 id="logoutModalTitle">Log out?</h2>
        <p>Are you sure you want to log out?</p>
        <div class="logout-modal-actions">
          <button
            type="button"
            class="logout-modal-button logout-modal-cancel"
            data-logout-close
          >
            Cancel
          </button>
          <button
            type="button"
            class="logout-modal-button logout-modal-confirm"
            id="confirmLogoutButton"
          >
            Log out
          </button>
        </div>
      </section>
    </div>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>
