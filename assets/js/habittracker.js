"use strict";

    const HABIT_API = {
      list: "api/habits/list.php",
      create: "api/habits/create.php",
      update: "api/habits/update.php",
      delete: "api/habits/delete.php",
      toggleLog: "api/habits/toggle_log.php",
      title: "api/habits/title.php"
    };

    const habitPageTitle = document.getElementById("habitPageTitle");
    const previousMonthButton = document.getElementById("previousMonthButton");
    const nextMonthButton = document.getElementById("nextMonthButton");
    const todayButton = document.getElementById("todayButton");
    const focusAddHabitButton = document.getElementById("focusAddHabitButton");
    const addHabitForm = document.getElementById("addHabitForm");
    const habitInput = document.getElementById("habitInput");
    const monthTitle = document.getElementById("monthTitle");
    const habitGrid = document.getElementById("habitGrid");
    const todayDoneCount = document.getElementById("todayDoneCount");
    const bestStreakCount = document.getElementById("bestStreakCount");
    const monthProgressCount = document.getElementById("monthProgressCount");
    const habitCount = document.getElementById("habitCount");
    const habitSummaryList = document.getElementById("habitSummaryList");

    let currentMonth = new Date();
    currentMonth.setDate(1);

    let habits = [];
    let habitLogs = {};

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

      return Array.from(
        { length: daysInMonth },
        (_, index) => new Date(year, month, index + 1)
      );
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
        `${HABIT_API.list}?from=${encodeURIComponent(range.from)}&to=${encodeURIComponent(range.to)}`
      );

      habitPageTitle.value = data.title || "Habit tracker";
      habits = (data.habits || []).map(normalizeHabit);
      habitLogs = data.logs || {};
      render();
    }

    async function addHabit(name) {
      const cleanName = name.trim();
      if (!cleanName) return;

      try {
        const data = await apiRequest(HABIT_API.create, {
          method: "POST",
          body: JSON.stringify({ name: cleanName })
        });

        habits.push(normalizeHabit(data.habit));
        render();
      } catch (error) {
        showApiError(error);
      }
    }

    function deleteHabit(habitId) {
      const habit = habits.find((item) => item.id === habitId);
      if (!habit) return;

      const confirmed = window.confirm(`Delete “${habit.name}”?`);
      if (!confirmed) return;

      const previousHabits = habits;
      const previousLogs = habitLogs;
      habits = habits.filter((item) => item.id !== habitId);
      delete habitLogs[String(habitId)];
      render();

      apiRequest(HABIT_API.delete, {
        method: "DELETE",
        body: JSON.stringify({ id: habitId })
      }).catch((error) => {
        habits = previousHabits;
        habitLogs = previousLogs;
        render();
        showApiError(error);
      });
    }

    function getCheckedCountForHabit(habitId) {
      return getMonthDates().reduce((count, date) => {
        return count + (isHabitChecked(habitId, formatDateKey(date)) ? 1 : 0);
      }, 0);
    }

    function getBestConsecutiveStreak(habitId) {
      let best = 0;
      let current = 0;

      getMonthDates().forEach((date) => {
        const dateKey = formatDateKey(date);

        if (isHabitChecked(habitId, dateKey)) {
          current += 1;
          best = Math.max(best, current);
        } else {
          current = 0;
        }
      });

      return best;
    }

    function renderGrid() {
      const dates = getMonthDates();
      const todayKey = formatDateKey(new Date());

      monthTitle.textContent = currentMonth.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric"
      });

      habitGrid.innerHTML = "";
      habitGrid.style.gridTemplateColumns =
        `240px repeat(${dates.length}, 58px)`;
      habitGrid.style.minWidth =
        `${240 + dates.length * 58}px`;

      const corner = document.createElement("div");
      corner.className = "habit-cell habit-day habit-sticky-left";
      corner.textContent = "Habit";
      habitGrid.appendChild(corner);

      dates.forEach((date) => {
        const dateKey = formatDateKey(date);
        const cell = document.createElement("div");

        cell.className = "habit-cell habit-day";
        if (dateKey === todayKey) {
          cell.classList.add("today");
        }

        const number = document.createElement("span");
        number.textContent = date.getDate();

        const weekday = document.createElement("small");
        weekday.textContent = date.toLocaleDateString("en-US", {
          weekday: "short"
        });

        cell.append(number, weekday);
        habitGrid.appendChild(cell);
      });

      if (habits.length === 0) {
        const empty = document.createElement("div");
        empty.className = "empty-state";
        empty.style.gridColumn = `1 / span ${dates.length + 1}`;
        empty.textContent = "No habits yet. Add your first habit above.";
        habitGrid.appendChild(empty);
        return;
      }

      habits.forEach((habit) => {
        const habitCell = document.createElement("div");
        habitCell.className = "habit-cell habit-name habit-sticky-left";

        const row = document.createElement("div");
        row.className = "habit-name-row";

        const input = document.createElement("input");
        input.className = "habit-name-input";
        input.type = "text";
        input.value = habit.name;
        input.dataset.id = habit.id;

        const deleteButton = document.createElement("button");
        deleteButton.className = "delete-habit";
        deleteButton.type = "button";
        deleteButton.textContent = "×";
        deleteButton.title = "Delete habit";
        deleteButton.dataset.id = habit.id;

        row.append(input, deleteButton);
        habitCell.appendChild(row);
        habitGrid.appendChild(habitCell);

        dates.forEach((date) => {
          const dateKey = formatDateKey(date);
          const checked = isHabitChecked(habit.id, dateKey);

          const cell = document.createElement("div");
          cell.className = "habit-cell";

          const checkButton = document.createElement("button");
          checkButton.className = `check-button ${checked ? "checked" : ""}`;
          checkButton.type = "button";
          checkButton.dataset.habitId = habit.id;
          checkButton.dataset.dateKey = dateKey;
          checkButton.setAttribute(
            "aria-label",
            `${checked ? "Uncheck" : "Check"} ${habit.name} on ${dateKey}`
          );

          cell.appendChild(checkButton);
          habitGrid.appendChild(cell);
        });
      });
    }

    function renderStats() {
      const dates = getMonthDates();
      const todayKey = formatDateKey(new Date());

      const todayDone = habits.reduce((count, habit) => {
        return count + (isHabitChecked(habit.id, todayKey) ? 1 : 0);
      }, 0);

      const totalChecks = habits.length * dates.length;
      const completedChecks = habits.reduce((total, habit) => {
        return total + getCheckedCountForHabit(habit.id);
      }, 0);

      const bestStreak = habits.reduce((best, habit) => {
        return Math.max(best, getBestConsecutiveStreak(habit.id));
      }, 0);

      const progress = totalChecks === 0
        ? 0
        : Math.round((completedChecks / totalChecks) * 100);

      todayDoneCount.textContent = todayDone;
      bestStreakCount.textContent = bestStreak;
      monthProgressCount.textContent = `${progress}%`;
      habitCount.textContent = habits.length;
    }

    function renderSummary() {
      const dates = getMonthDates();
      const totalDays = dates.length;

      habitSummaryList.innerHTML = "";

      if (habits.length === 0) {
        const empty = document.createElement("li");
        empty.className = "empty-state";
        empty.textContent = "Your habits will appear here.";
        habitSummaryList.appendChild(empty);
        return;
      }

      habits.forEach((habit) => {
        const checkedCount = getCheckedCountForHabit(habit.id);
        const progress = totalDays === 0
          ? 0
          : Math.round((checkedCount / totalDays) * 100);

        const item = document.createElement("li");
        item.className = "summary-item";

        const top = document.createElement("div");
        top.className = "summary-item-top";

        const name = document.createElement("span");
        name.textContent = habit.name;

        const count = document.createElement("span");
        count.className = "summary-count";
        count.textContent = `${checkedCount}/${totalDays}`;

        const track = document.createElement("div");
        track.className = "progress-track";

        const fill = document.createElement("div");
        fill.className = "progress-fill";
        fill.style.setProperty("--progress", `${progress}%`);

        top.append(name, count);
        track.appendChild(fill);
        item.append(top, track);
        habitSummaryList.appendChild(item);
      });
    }

    function render() {
      renderGrid();
      renderStats();
      renderSummary();
    }

    addHabitForm.addEventListener("submit", (event) => {
      event.preventDefault();
      addHabit(habitInput.value);
      habitInput.value = "";
      habitInput.focus();
    });

    focusAddHabitButton.addEventListener("click", () => {
      habitInput.focus();
    });

    previousMonthButton.addEventListener("click", () => {
      currentMonth.setMonth(currentMonth.getMonth() - 1);
      loadHabitsForMonth().catch(showApiError);
    });

    nextMonthButton.addEventListener("click", () => {
      currentMonth.setMonth(currentMonth.getMonth() + 1);
      loadHabitsForMonth().catch(showApiError);
    });

    todayButton.addEventListener("click", () => {
      currentMonth = new Date();
      currentMonth.setDate(1);
      loadHabitsForMonth().catch(showApiError);
    });

    function saveHabitTitleNow() {
      return apiRequest(HABIT_API.title, {
        method: "PATCH",
        body: JSON.stringify({
          title: habitPageTitle.value.trim() || "Habit tracker"
        })
      }).catch(showApiError);
    }

    const saveHabitTitle = debounce(saveHabitTitleNow);

    habitPageTitle.addEventListener("input", saveHabitTitle);
    habitPageTitle.addEventListener("blur", saveHabitTitleNow);

    habitGrid.addEventListener("click", (event) => {
      const deleteButton = event.target.closest(".delete-habit");
      if (deleteButton) {
        deleteHabit(deleteButton.dataset.id);
        return;
      }

      const checkButton = event.target.closest(".check-button");
      if (!checkButton) return;

      const nextState = !checkButton.classList.contains("checked");
      checkButton.classList.toggle("checked", nextState);
      setLocalHabitChecked(checkButton.dataset.habitId, checkButton.dataset.dateKey, nextState);

      renderStats();
      renderSummary();

      apiRequest(HABIT_API.toggleLog, {
        method: "POST",
        body: JSON.stringify({
          habitId: checkButton.dataset.habitId,
          date: checkButton.dataset.dateKey,
          completed: nextState
        })
      }).catch((error) => {
        checkButton.classList.toggle("checked", !nextState);
        setLocalHabitChecked(checkButton.dataset.habitId, checkButton.dataset.dateKey, !nextState);
        renderStats();
        renderSummary();
        showApiError(error);
      });
    });

    habitGrid.addEventListener("input", (event) => {
      const input = event.target.closest(".habit-name-input");
      if (!input) return;

      const habit = habits.find((item) => item.id === input.dataset.id);
      if (!habit) return;

      habit.name = input.value;
      renderSummary();
    });

    habitGrid.addEventListener("focusout", (event) => {
      const input = event.target.closest(".habit-name-input");
      if (!input) return;

      const habit = habits.find((item) => item.id === input.dataset.id);
      if (!habit) return;

      const previousName = habit.name;
      habit.name = input.value.trim() || "Untitled";
      input.value = habit.name;
      renderSummary();

      apiRequest(HABIT_API.update, {
        method: "PATCH",
        body: JSON.stringify({
          id: habit.id,
          name: habit.name
        })
      }).then((data) => {
        Object.assign(habit, normalizeHabit(data.habit));
        render();
      }).catch((error) => {
        habit.name = previousName;
        render();
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

    render();
    loadHabitsForMonth().catch(showApiError);
