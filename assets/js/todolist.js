"use strict";

    const TODO_API = {
      list: "api/todos/list.php",
      create: "api/todos/create.php",
      update: "api/todos/update.php",
      delete: "api/todos/delete.php",
      clear: "api/todos/clear.php",
      completeAll: "api/todos/complete_all.php"
    };
    const addTodoForm = document.getElementById("addTodoForm");
    const todoInput = document.getElementById("todoInput");
    const todoSearch = document.getElementById("todoSearch");
    const todoList = document.getElementById("todoList");
    const filterButtons = [...document.querySelectorAll(".filter-button")];
    const clearAllButton = document.getElementById("clearAllButton");
    const focusAddButton = document.getElementById("focusAddButton");
    const markAllButton = document.getElementById("markAllButton");
    const clearCompletedButton = document.getElementById("clearCompletedButton");
    const progressRing = document.getElementById("progressRing");
    const progressPercent = document.getElementById("progressPercent");
    const progressLabel = document.getElementById("progressLabel");
    const totalCount = document.getElementById("totalCount");
    const completedCount = document.getElementById("completedCount");
    const leftCount = document.getElementById("leftCount");
    const visibleCount = document.getElementById("visibleCount");

    let todos = [];
    let currentFilter = "all";

    function normalizeTodos(savedTodos) {
      if (!Array.isArray(savedTodos)) {
        return [];
      }
      return savedTodos.map(normalizeTodo).filter(Boolean);
    }
    function normalizeTodo(todo) {
      if (!todo) {
        return null;
      }
      return {
        id: String(todo.id),
        text: todo.text || "",
        completed: Boolean(todo.completed),
        createdAt: todo.createdAt || new Date().toISOString()
      };
    }
    function formatTime(timestamp) {
      return new Date(timestamp).toLocaleString("en-US", {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      });
    }
    async function requestTodo(endpoint, options = {}) {
      const fetchOptions = {
        credentials: "same-origin",
        ...options
      };
      if (fetchOptions.body && !fetchOptions.headers) {
        fetchOptions.headers = {
          "Content-Type": "application/json"
        };
      }
      const response = await fetch(endpoint, fetchOptions);
      const data = await response.json().catch(() => ({}));
      if (response.status === 401) {
        window.location.href = "login.php";
        return new Promise(() => {});
      }
      if (!response.ok || data.success === false) {
        throw new Error(data.message || "Todo request failed.");
      }
      return data;
    }

    function handleTodoError(error) {
      window.alert(error.message || "Todo request failed.");
    }

    function findTodo(todoId) {
      return todos.find((todo) => todo.id === String(todoId));
    }

    function replaceTodo(savedTodo) {
      const normalizedTodo = normalizeTodo(savedTodo);
      if (!normalizedTodo) return;

      todos = todos.map((todo) => (
        todo.id === normalizedTodo.id ? normalizedTodo : todo
      ));
    }

    async function loadTodos() {
      try {
        const data = await requestTodo(TODO_API.list);
        todos = normalizeTodos(data.todos || []);
        render();
      } catch (error) {
        handleTodoError(error);
        render();
      }
    }

    async function addTodo(text) {
      const cleanText = text.trim();
      if (!cleanText) return;

      try {
        const data = await requestTodo(TODO_API.create, {
          method: "POST",
          body: JSON.stringify({
            text: cleanText
          })
        });

        const createdTodo = normalizeTodo(data.todo);
        if (createdTodo) {
          todos.push(createdTodo);
        }

        todoInput.value = "";
        render();
      } catch (error) {
        handleTodoError(error);
      }
    }

    async function deleteTodo(todoId) {
      const previousTodos = todos;
      todos = todos.filter((todo) => todo.id !== String(todoId));
      render();

      try {
        await requestTodo(TODO_API.delete, {
          method: "DELETE",
          body: JSON.stringify({
            id: todoId
          })
        });
      } catch (error) {
        todos = previousTodos;
        render();
        handleTodoError(error);
      }
    }

    async function updateTodo(todoId, partial) {
      const todo = findTodo(todoId);
      if (!todo) return;

      const previousTodo = { ...todo };
      Object.assign(todo, partial);
      render();

      try {
        const data = await requestTodo(TODO_API.update, {
          method: "PATCH",
          body: JSON.stringify({
            id: todoId,
            ...partial
          })
        });

        replaceTodo(data.todo);
        render();
      } catch (error) {
        Object.assign(todo, previousTodo);
        render();
        handleTodoError(error);
      }
    }

    function getVisibleTodos() {
      const query = todoSearch.value.trim().toLowerCase();

      return todos.filter((todo) => {
        const matchesSearch = todo.text.toLowerCase().includes(query);
        const matchesFilter =
          currentFilter === "all" ||
          (currentFilter === "active" && !todo.completed) ||
          (currentFilter === "completed" && todo.completed);

        return matchesSearch && matchesFilter;
      });
    }

    function renderTodos() {
      const visibleTodos = getVisibleTodos();

      todoList.innerHTML = "";

      if (visibleTodos.length === 0) {
        const empty = document.createElement("li");
        empty.className = "empty-state";
        empty.innerHTML = `
          <div class="empty-icon">✓</div>
          No tasks here.<br>
          Add a task or change the filter.
        `;
        todoList.appendChild(empty);
        return;
      }

      visibleTodos.forEach((todo) => {
        const item = document.createElement("li");
        item.className = `todo-item ${todo.completed ? "completed" : ""}`;
        item.dataset.id = todo.id;

        const checkButton = document.createElement("button");
        checkButton.className = `todo-checkbox ${todo.completed ? "checked" : ""}`;
        checkButton.type = "button";
        checkButton.setAttribute(
          "aria-label",
          `${todo.completed ? "Mark active" : "Mark completed"}`
        );

        const main = document.createElement("div");
        main.className = "todo-main";

        const textInput = document.createElement("textarea");
        textInput.className = "todo-text-input";
        textInput.rows = 1;
        textInput.value = todo.text;
        textInput.dataset.id = todo.id;

        const meta = document.createElement("div");
        meta.className = "todo-meta";
        meta.textContent = todo.createdAt
          ? `Created ${formatTime(todo.createdAt)}`
          : "Saved task";

        const deleteButton = document.createElement("button");
        deleteButton.className = "todo-delete";
        deleteButton.type = "button";
        deleteButton.textContent = "×";
        deleteButton.setAttribute("aria-label", "Delete task");

        main.append(textInput, meta);
        item.append(checkButton, main, deleteButton);
        todoList.appendChild(item);

        autoResize(textInput);
      });
    }

    function renderStats() {
      const total = todos.length;
      const completed = todos.filter((todo) => todo.completed).length;
      const left = total - completed;
      const visible = getVisibleTodos().length;
      const percent = total === 0
        ? 0
        : Math.round((completed / total) * 100);

      totalCount.textContent = total;
      completedCount.textContent = completed;
      leftCount.textContent = left;
      visibleCount.textContent = visible;

      progressPercent.textContent = `${percent}%`;
      progressRing.style.setProperty("--value", `${percent * 3.6}deg`);

      progressLabel.textContent = total === 0
        ? "No tasks yet"
        : `${completed} completed · ${left} left`;
    }

    function renderFilterButtons() {
      filterButtons.forEach((button) => {
        button.classList.toggle("active", button.dataset.filter === currentFilter);
      });
    }

    function render() {
      renderFilterButtons();
      renderTodos();
      renderStats();
    }

    function autoResize(textarea) {
      textarea.style.height = "auto";
      textarea.style.height = `${textarea.scrollHeight}px`;
    }

    addTodoForm.addEventListener("submit", (event) => {
      event.preventDefault();
      addTodo(todoInput.value);
      todoInput.focus();
    });

    focusAddButton.addEventListener("click", () => {
      todoInput.focus();
    });

    filterButtons.forEach((button) => {
      button.addEventListener("click", () => {
        currentFilter = button.dataset.filter;
        render();
      });
    });

    todoSearch.addEventListener("input", render);

    clearAllButton.addEventListener("click", () => {
      if (todos.length === 0) return;

      const confirmed = window.confirm("Delete all tasks?");
      if (!confirmed) return;

      const previousTodos = todos;
      todos = [];
      render();

      requestTodo(TODO_API.clear, {
        method: "DELETE",
        body: JSON.stringify({
          completedOnly: false
        })
      }).catch((error) => {
        todos = previousTodos;
        render();
        handleTodoError(error);
      });
    });

    markAllButton.addEventListener("click", () => {
      if (todos.length === 0) return;

      const previousTodos = todos.map((todo) => ({ ...todo }));
      todos = todos.map((todo) => ({
        ...todo,
        completed: true
      }));
      render();

      requestTodo(TODO_API.completeAll, {
        method: "POST"
      }).catch((error) => {
        todos = previousTodos;
        render();
        handleTodoError(error);
      });
    });

    clearCompletedButton.addEventListener("click", () => {
      const previousTodos = todos;
      todos = todos.filter((todo) => !todo.completed);
      render();

      requestTodo(TODO_API.clear, {
        method: "DELETE",
        body: JSON.stringify({
          completedOnly: true
        })
      }).catch((error) => {
        todos = previousTodos;
        render();
        handleTodoError(error);
      });
    });

    todoList.addEventListener("click", (event) => {
      const item = event.target.closest(".todo-item");
      if (!item) return;

      const todoId = item.dataset.id;

      if (event.target.closest(".todo-checkbox")) {
        const todo = findTodo(todoId);
        if (!todo) return;

        updateTodo(todo.id, {
          completed: !todo.completed
        });
        return;
      }

      if (event.target.closest(".todo-delete")) {
        deleteTodo(todoId);
      }
    });

    todoList.addEventListener("input", (event) => {
      const textarea = event.target.closest(".todo-text-input");
      if (!textarea) return;

      autoResize(textarea);

      const todo = findTodo(textarea.dataset.id);
      if (!todo) return;

      todo.text = textarea.value;
      renderStats();
    });

    todoList.addEventListener("focusout", (event) => {
      const textarea = event.target.closest(".todo-text-input");
      if (!textarea) return;

      const todo = findTodo(textarea.dataset.id);
      if (!todo) return;

      const cleanText = textarea.value.trim();

      if (!cleanText) {
        deleteTodo(todo.id);
        return;
      }

      updateTodo(todo.id, {
        text: cleanText
      });
    });

    todoList.addEventListener("keydown", (event) => {
      const textarea = event.target.closest(".todo-text-input");
      if (!textarea) return;

      if (event.key === "Enter" && !event.shiftKey) {
        event.preventDefault();
        textarea.blur();
      }
    });

    render();
    loadTodos();
