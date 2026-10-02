"use strict";
    const API = {
      todos: {
        list: "api/todos/list.php",
        create: "api/todos/create.php",
        update: "api/todos/update.php",
        delete: "api/todos/delete.php",
        clear: "api/todos/clear.php"
      },
      calendar: {
        list: "api/calendar/list.php",
        updateSlot: "api/calendar/update_slot.php"
      },
      habits: {
        list: "api/habits/list.php",
        create: "api/habits/create.php",
        update: "api/habits/update.php",
        delete: "api/habits/delete.php",
        toggleLog: "api/habits/toggle_log.php",
        title: "api/habits/title.php"
      }
    };

    async function apiRequest(endpoint, options = {}) {
      const requestOptions = {
        credentials: "same-origin",
        ...options
      };

      if (requestOptions.body && !requestOptions.headers) {
        requestOptions.headers = {
          "Content-Type": "application/json"
        };
      }

      const response = await fetch(endpoint, requestOptions);
      const data = await response.json().catch(() => ({}));

      if (response.status === 401) {
        window.location.href = "login.php";
        return new Promise(() => {});
      }

      if (!response.ok || data.success === false) {
        throw new Error(data.message || "Request failed.");
      }

      return data;
    }

    function showApiError(error) {
      window.alert(error.message || "Could not save your changes.");
    }

    function debounce(callback, delay = 350) {
      let timer = null;

      return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => callback(...args), delay);
      };
    }

    // Time block
    const timeblockList = document.getElementById("timeblockList");
    const timeblockInputs = new Map();
    const timeblockSaveTimers = new Map();
    let dashboardDateKey = formatDateKey(new Date());

    const times = [
      "5:00", "6:00", "7:00", "8:00", "9:00", "10:00",
      "11:00", "12:00", "13:00", "14:00", "15:00", "16:00",
      "17:00", "18:00", "19:00", "20:00", "21:00", "22:00",
      "23:00", "24:00", "1:00", "2:00", "3:00", "4:00"
    ];

    function timeLabelToCalendarTime(label) {
      const [hourText, minuteText] = label.split(":");
      const hour = Number(hourText) === 24 ? 0 : Number(hourText);
      const minute = Number(minuteText || 0);

      return `${String(hour).padStart(2, "0")}:${String(minute).padStart(2, "0")}`;
    }

    function saveTimeblock(index, text) {
      window.clearTimeout(timeblockSaveTimers.get(index));

      const timer = window.setTimeout(() => {
        apiRequest(API.calendar.updateSlot, {
          method: "POST",
          body: JSON.stringify({
            date: dashboardDateKey,
            time: timeLabelToCalendarTime(times[index]),
            desc: text
          })
        })
          .catch(showApiError)
          .finally(() => {
            timeblockSaveTimers.delete(index);
          });
      }, 350);

      timeblockSaveTimers.set(index, timer);
    }

    function updateTimeblockRowState(input) {
      input.closest(".timeblock-row")?.classList.toggle(
        "has-task",
        input.value.trim() !== ""
      );
    }

    function renderTimeblockRows() {
      timeblockList.innerHTML = "";
      timeblockInputs.clear();

      times.forEach((time, index) => {
        const row = document.createElement("div");
        row.className = "timeblock-row";

        const label = document.createElement("label");
        label.textContent = time;

        const input = document.createElement("input");
        input.type = "text";
        input.placeholder = "Write a task...";

        input.addEventListener("input", () => {
          updateTimeblockRowState(input);
          saveTimeblock(index, input.value);
        });

        timeblockInputs.set(index, input);
        row.append(label, input);
        timeblockList.appendChild(row);
      });
    }

    async function loadTimeblocks() {
      const data = await apiRequest(API.calendar.list);
      const calendarEvents = data.events || {};
      const dayEvents = calendarEvents[dashboardDateKey] || [];

      timeblockInputs.forEach((input) => {
        input.value = "";
        updateTimeblockRowState(input);
      });

      dayEvents.forEach((event) => {
        const index = times.findIndex((time) => {
          return timeLabelToCalendarTime(time) === event.time;
        });
        const input = timeblockInputs.get(index);

        if (input) {
          input.value = event.desc || "";
          updateTimeblockRowState(input);
        }
      });
    }

    function refreshTimeblocksWhenDateChanges() {
      const currentDateKey = formatDateKey(new Date());

      if (currentDateKey !== dashboardDateKey) {
        dashboardDateKey = currentDateKey;
        loadTimeblocks().catch(showApiError);
      }
    }

    // To-do list
    const todoInput = document.getElementById("todoInput");
    const addTodoButton = document.getElementById("addTodoButton");
    const clearAllButton = document.getElementById("clearAllButton");
    const todoItems = document.getElementById("todoItems");

    let todos = [];

    function normalizeTodo(todo) {
      return {
        id: String(todo.id),
        text: todo.text || "",
        completed: Boolean(todo.completed)
      };
    }

    function renderTodos() {
      todoItems.innerHTML = "";

      todos.forEach((todo) => {
        const item = document.createElement("li");
        item.className = `todo-item ${todo.completed ? "completed" : ""}`;
        item.dataset.id = todo.id;

        item.innerHTML = `
          <input
            type="checkbox"
            class="todo-checkbox"
            ${todo.completed ? "checked" : ""}
          >
          <span class="todo-text"></span>
          <button type="button" class="todo-delete">×</button>
        `;

        item.querySelector(".todo-text").textContent = todo.text;

        item.querySelector(".todo-checkbox").addEventListener("change", (event) => {
          updateTodo(todo.id, { completed: event.target.checked });
        });

        item.querySelector(".todo-delete").addEventListener("click", () => {
          deleteTodo(todo.id);
        });

        todoItems.appendChild(item);
      });
    }

    async function loadTodos() {
      const data = await apiRequest(API.todos.list);
      todos = (data.todos || []).map(normalizeTodo);
      renderTodos();
      updateStats();
    }

    async function addTodo() {
      const text = todoInput.value.trim();
      if (text === "") return;

      try {
        const data = await apiRequest(API.todos.create, {
          method: "POST",
          body: JSON.stringify({ text })
        });

        todos.push(normalizeTodo(data.todo));
        todoInput.value = "";
        renderTodos();
        updateStats();
      } catch (error) {
        showApiError(error);
      }
    }

    async function updateTodo(todoId, partial) {
      const todo = todos.find((item) => item.id === String(todoId));
      if (!todo) return;

      const previousTodo = { ...todo };
      Object.assign(todo, partial);
      renderTodos();
      updateStats();

      try {
        const data = await apiRequest(API.todos.update, {
          method: "PATCH",
          body: JSON.stringify({
            id: todoId,
            ...partial
          })
        });

        Object.assign(todo, normalizeTodo(data.todo));
        renderTodos();
        updateStats();
      } catch (error) {
        Object.assign(todo, previousTodo);
        renderTodos();
        updateStats();
        showApiError(error);
      }
    }

    async function deleteTodo(todoId) {
      const previousTodos = todos;
      todos = todos.filter((todo) => todo.id !== String(todoId));
      renderTodos();
      updateStats();

      try {
        await apiRequest(API.todos.delete, {
          method: "DELETE",
          body: JSON.stringify({ id: todoId })
        });
      } catch (error) {
        todos = previousTodos;
        renderTodos();
        updateStats();
        showApiError(error);
      }
    }

    addTodoButton.addEventListener("click", addTodo);

    todoInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        addTodo();
      }
    });

    clearAllButton.addEventListener("click", () => {
      const previousTodos = todos;
      todos = [];
      renderTodos();
      updateStats();

      apiRequest(API.todos.clear, {
        method: "DELETE",
        body: JSON.stringify({ completedOnly: false })
      }).catch((error) => {
        todos = previousTodos;
        renderTodos();
        updateStats();
        showApiError(error);
      });
    });

    // Habit tracker
    const habitTitle = document.getElementById("habitTitle");
    const openHabitAddButton = document.getElementById("openHabitAddButton");
    const habitAddPopover = document.getElementById("habitAddPopover");
    const habitInput = document.getElementById("habitInput");
    const addHabitButton = document.getElementById("addHabitButton");
    const habitGrid = document.getElementById("habitGrid");
    const habitMonthTitle = document.getElementById("habitMonthTitle");
    const prevMonthButton = document.getElementById("prevMonthButton");
    const nextMonthButton = document.getElementById("nextMonthButton");

    let habits = [];
    let habitLogs = {};
    let currentMonth = new Date();
    currentMonth.setDate(1);

    function normalizeHabit(habit) {
      return {
        id: String(habit.id),
        name: habit.name || "Untitled"
      };
    }

    function formatDateKey(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");

      return `${year}-${month}-${day}`;
    }

    function getDaysInMonth(year, month) {
      return new Date(year, month + 1, 0).getDate();
    }

    function getMonthDates() {
      const year = currentMonth.getFullYear();
      const month = currentMonth.getMonth();
      const daysInMonth = getDaysInMonth(year, month);
      const dates = [];

      for (let day = 1; day <= daysInMonth; day++) {
        dates.push(new Date(year, month, day));
      }

      return dates;
    }

    function monthRange() {
      const dates = getMonthDates();

      return {
        from: formatDateKey(dates[0]),
        to: formatDateKey(dates[dates.length - 1])
      };
    }

    function isHabitChecked(habitId, dateKey) {
      return Boolean(habitLogs[String(habitId)]?.[dateKey]);
    }

    function setLocalHabitChecked(habitId, dateKey, checked) {
      const key = String(habitId);

      if (!habitLogs[key]) {
        habitLogs[key] = {};
      }

      if (checked) {
        habitLogs[key][dateKey] = true;
      } else {
        delete habitLogs[key][dateKey];
      }
    }

    async function loadHabitsForMonth() {
      const range = monthRange();
      const data = await apiRequest(
        `${API.habits.list}?from=${encodeURIComponent(range.from)}&to=${encodeURIComponent(range.to)}`
      );

      habitTitle.textContent = data.title || "Habit tracker";
      habits = (data.habits || []).map(normalizeHabit);
      habitLogs = data.logs || {};
      renderHabitTracker();
      updateStats();
    }

    function renderHabitTracker() {
      const monthDates = getMonthDates();
      const daysInMonth = monthDates.length;

      habitMonthTitle.textContent = currentMonth.toLocaleString("en-US", {
        month: "long",
        year: "numeric"
      });

      habitGrid.innerHTML = "";
      habitGrid.style.gridTemplateColumns = `230px repeat(${daysInMonth}, 70px)`;
      habitGrid.style.minWidth = `${230 + daysInMonth * 70}px`;

      const emptyCell = document.createElement("div");
      emptyCell.className = "habit-cell habit-day habit-sticky-left";
      habitGrid.appendChild(emptyCell);

      monthDates.forEach((date) => {
        const dayCell = document.createElement("div");
        dayCell.className = "habit-cell habit-day";

        const dayNumber = document.createElement("span");
        dayNumber.textContent = date.getDate();

        const weekDay = document.createElement("small");
        weekDay.textContent = date.toLocaleString("en-US", {
          weekday: "short"
        });

        dayCell.append(dayNumber, weekDay);
        habitGrid.appendChild(dayCell);
      });

      habits.forEach((habit) => {
        const habitNameCell = document.createElement("div");
        habitNameCell.className = "habit-cell habit-name habit-sticky-left";

        const nameInput = document.createElement("input");
        nameInput.type = "text";
        nameInput.className = "habit-name-input";
        nameInput.value = habit.name;
        nameInput.dataset.id = habit.id;

        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className = "habit-delete-button";
        deleteButton.textContent = "×";
        deleteButton.dataset.id = habit.id;

        habitNameCell.append(nameInput, deleteButton);
        habitGrid.appendChild(habitNameCell);

        monthDates.forEach((date) => {
          const dateKey = formatDateKey(date);
          const checked = isHabitChecked(habit.id, dateKey);

          const checkCell = document.createElement("div");
          checkCell.className = "habit-cell";

          const checkButton = document.createElement("button");
          checkButton.type = "button";
          checkButton.className = `habit-check-button ${checked ? "checked" : ""}`;
          checkButton.dataset.habitId = habit.id;
          checkButton.dataset.dateKey = dateKey;

          checkCell.appendChild(checkButton);
          habitGrid.appendChild(checkCell);
        });
      });
    }

    async function addHabit() {
      const newHabit = habitInput.value.trim();
      if (newHabit === "") return;

      try {
        const data = await apiRequest(API.habits.create, {
          method: "POST",
          body: JSON.stringify({ name: newHabit })
        });

        habits.push(normalizeHabit(data.habit));
        habitInput.value = "";
        renderHabitTracker();
        updateStats();
        habitAddPopover.classList.remove("open");
      } catch (error) {
        showApiError(error);
      }
    }

    function saveHabitTitleNow() {
      return apiRequest(API.habits.title, {
        method: "PATCH",
        body: JSON.stringify({
          title: habitTitle.textContent.trim() || "Habit tracker"
        })
      }).catch(showApiError);
    }

    const saveHabitTitle = debounce(saveHabitTitleNow);

    habitTitle.addEventListener("input", saveHabitTitle);

    habitTitle.addEventListener("blur", () => {
      if (habitTitle.textContent.trim() === "") {
        habitTitle.textContent = "Habit tracker";
      }

      saveHabitTitleNow();
    });

    openHabitAddButton.addEventListener("click", (event) => {
      event.stopPropagation();
      habitAddPopover.classList.toggle("open");
      habitInput.focus();
    });

    habitAddPopover.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    document.addEventListener("click", () => {
      habitAddPopover.classList.remove("open");
    });

    addHabitButton.addEventListener("click", addHabit);

    habitInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        addHabit();
      }

      if (event.key === "Escape") {
        habitAddPopover.classList.remove("open");
      }
    });

    habitGrid.addEventListener("input", (event) => {
      const input = event.target.closest(".habit-name-input");
      if (!input) return;

      const habit = habits.find((item) => item.id === input.dataset.id);
      if (!habit) return;

      habit.name = input.value;
    });

    habitGrid.addEventListener("focusout", (event) => {
      const input = event.target.closest(".habit-name-input");
      if (!input) return;

      const habit = habits.find((item) => item.id === input.dataset.id);
      if (!habit) return;

      const previousName = habit.name;
      habit.name = input.value.trim() || "Untitled";
      input.value = habit.name;

      apiRequest(API.habits.update, {
        method: "PATCH",
        body: JSON.stringify({
          id: habit.id,
          name: habit.name
        })
      }).then((data) => {
        Object.assign(habit, normalizeHabit(data.habit));
        renderHabitTracker();
      }).catch((error) => {
        habit.name = previousName;
        renderHabitTracker();
        showApiError(error);
      });
    });

    habitGrid.addEventListener("keydown", (event) => {
      const input = event.target.closest(".habit-name-input");
      if (!input) return;

      if (event.key === "Enter") {
        event.preventDefault();
        input.blur();
      }
    });

    habitGrid.addEventListener("click", (event) => {
      const deleteButton = event.target.closest(".habit-delete-button");

      if (deleteButton) {
        const habitId = deleteButton.dataset.id;
        const previousHabits = habits;
        const previousLogs = habitLogs;

        habits = habits.filter((habit) => habit.id !== habitId);
        delete habitLogs[String(habitId)];
        renderHabitTracker();
        updateStats();

        apiRequest(API.habits.delete, {
          method: "DELETE",
          body: JSON.stringify({ id: habitId })
        }).catch((error) => {
          habits = previousHabits;
          habitLogs = previousLogs;
          renderHabitTracker();
          updateStats();
          showApiError(error);
        });

        return;
      }

      const checkButton = event.target.closest(".habit-check-button");
      if (!checkButton) return;

      const habitId = checkButton.dataset.habitId;
      const dateKey = checkButton.dataset.dateKey;
      const nextState = !checkButton.classList.contains("checked");

      checkButton.classList.toggle("checked", nextState);
      setLocalHabitChecked(habitId, dateKey, nextState);
      updateStats();

      apiRequest(API.habits.toggleLog, {
        method: "POST",
        body: JSON.stringify({
          habitId,
          date: dateKey,
          completed: nextState
        })
      }).catch((error) => {
        checkButton.classList.toggle("checked", !nextState);
        setLocalHabitChecked(habitId, dateKey, !nextState);
        updateStats();
        showApiError(error);
      });
    });

    prevMonthButton.addEventListener("click", () => {
      currentMonth.setMonth(currentMonth.getMonth() - 1);
      loadHabitsForMonth().catch(showApiError);
    });

    nextMonthButton.addEventListener("click", () => {
      currentMonth.setMonth(currentMonth.getMonth() + 1);
      loadHabitsForMonth().catch(showApiError);
    });

    // Stats calculation
    const dayStreakCount = document.getElementById("dayStreakCount");
    const tasksCompletedCount = document.getElementById("tasksCompletedCount");
    const overallProgressCount = document.getElementById("overallProgressCount");
    const taskLeftCount = document.getElementById("taskLeftCount");

    function getHabitCheckedCountForMonth(habitId) {
      return getMonthDates().reduce((count, date) => {
        return count + (isHabitChecked(habitId, formatDateKey(date)) ? 1 : 0);
      }, 0);
    }

    function getBestHabitStreak() {
      if (habits.length === 0) return 0;

      return habits.reduce((bestStreak, habit) => {
        return Math.max(bestStreak, getHabitCheckedCountForMonth(habit.id));
      }, 0);
    }

    function updateStats() {
      const completedTodos = todos.filter((todo) => todo.completed).length;
      const leftTodos = todos.filter((todo) => !todo.completed).length;
      const bestHabitStreak = getBestHabitStreak();

      const completedHabitChecks = habits.reduce((total, habit) => {
        return total + getHabitCheckedCountForMonth(habit.id);
      }, 0);

      const totalHabitChecks = habits.length * getMonthDates().length;
      const totalTodos = todos.length;
      const completedProgressItems = completedHabitChecks + completedTodos;
      const totalProgressItems = totalHabitChecks + totalTodos;
      const progress = totalProgressItems === 0
        ? 0
        : Math.round((completedProgressItems / totalProgressItems) * 100);

      dayStreakCount.textContent = bestHabitStreak;
      tasksCompletedCount.textContent = completedTodos;
      taskLeftCount.textContent = leftTodos;
      overallProgressCount.textContent = `${progress}%`;
    }

    // Live date and time
    const liveTime = document.getElementById("liveTime");
    const liveSeconds = document.getElementById("liveSeconds");
    const liveDate = document.getElementById("liveDate");
    const liveYear = document.getElementById("liveYear");

    function updateLiveDateTime() {
      const now = new Date();

      const hours = String(now.getHours()).padStart(2, "0");
      const minutes = String(now.getMinutes()).padStart(2, "0");
      const seconds = String(now.getSeconds()).padStart(2, "0");

      liveTime.textContent = `${hours}:${minutes}`;
      liveSeconds.textContent = seconds;

      liveDate.textContent = now.toLocaleDateString("en-US", {
        weekday: "long",
        month: "long",
        day: "numeric"
      });

      liveYear.textContent = now.getFullYear();
    }

    // Fit dashboard to screen
    const dashboardScreen = document.querySelector(".dashboard-screen");
    const dashboard = document.querySelector(".dashboard");

    function resizeDashboard() {
      const originalWidth = 1440;
      const originalHeight = 1024;
      const scale = window.innerWidth / originalWidth;

      document.documentElement.style.setProperty(
        "--dashboard-scale",
        scale
      );

      dashboardScreen.style.height =
        `${originalHeight * scale}px`;
    }

    // Page transitions and logout 
    const openCalendarButton = document.getElementById("openCalendarButton");
    const logoutButton = document.querySelector(".dashboard-logout-button");
    const logoutModal = document.getElementById("logoutModal");
    const confirmLogoutButton = document.getElementById("confirmLogoutButton");
    const closeLogoutButtons = document.querySelectorAll("[data-logout-close]");

    function startPageTransition(url) {
      document.body.classList.add("page-leaving");

      window.setTimeout(() => {
        window.location.href = url;
      }, 240);
    }

    function startLogoutTransition(logoutUrl) {
      const homeUrl = new URL("index.html", window.location.href).href;

      document.body.classList.add("logout-leaving");
      confirmLogoutButton.disabled = true;

      window.setTimeout(() => {
        fetch(logoutUrl, {
          method: "GET",
          credentials: "same-origin",
          cache: "no-store"
        })
          .then(() => {
            window.setTimeout(() => {
              window.location.href = homeUrl;
            }, 120);
          })
          .catch(() => {
            window.location.href = logoutUrl;
          });
      }, 260);
    }

    function openLogoutModal() {
      logoutModal.classList.add("open");
      logoutModal.setAttribute("aria-hidden", "false");
      document.body.classList.add("logout-modal-open");
      confirmLogoutButton.focus();
    }

    function closeLogoutModal() {
      logoutModal.classList.remove("open");
      logoutModal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("logout-modal-open");
      logoutButton.focus();
    }

    logoutButton.addEventListener("click", (event) => {
      event.preventDefault();
      openLogoutModal();
    });

    openCalendarButton.addEventListener("click", () => {
      startPageTransition("calendar.html");
    });

    openCalendarButton.addEventListener("mouseenter", () => {
      dashboard.classList.add("timeblock-hovering");
    });

    openCalendarButton.addEventListener("mouseleave", () => {
      dashboard.classList.remove("timeblock-hovering");
    });

    openCalendarButton.addEventListener("focus", () => {
      dashboard.classList.add("timeblock-hovering");
    });

    openCalendarButton.addEventListener("blur", () => {
      dashboard.classList.remove("timeblock-hovering");
    });

    closeLogoutButtons.forEach((button) => {
      button.addEventListener("click", closeLogoutModal);
    });

    confirmLogoutButton.addEventListener("click", () => {
      closeLogoutModal();
      startLogoutTransition(logoutButton.href);
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && logoutModal.classList.contains("open")) {
        closeLogoutModal();
      }
    });

    document.addEventListener("click", (event) => {
      const link = event.target.closest("a[href]");

      if (
        !link ||
        link === logoutButton ||
        link.target === "_blank" ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey
      ) {
        return;
      }

      const nextUrl = new URL(link.href, window.location.href);

      if (nextUrl.origin !== window.location.origin) {
        return;
      }

      event.preventDefault();
      startPageTransition(nextUrl.href);
    });

    renderTimeblockRows();
    renderTodos();
    renderHabitTracker();
    updateStats();
    updateLiveDateTime();
    resizeDashboard();

    loadTimeblocks().catch(showApiError);
    loadTodos().catch(showApiError);
    loadHabitsForMonth().catch(showApiError);

    setInterval(updateLiveDateTime, 1000);
    setInterval(refreshTimeblocksWhenDateChanges, 60000);
    window.addEventListener("resize", resizeDashboard);

