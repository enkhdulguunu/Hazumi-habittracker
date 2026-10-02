"use strict";
    const NOTES_API = {
      list: "api/notes/list.php",
      create: "api/notes/create.php",
      update: "api/notes/update.php",
      delete: "api/notes/delete.php"
    };

    const noteList = document.getElementById("noteList");
    const noteSearch = document.getElementById("noteSearch");
    const newNoteButton = document.getElementById("newNoteButton");
    const noteTitle = document.getElementById("noteTitle");
    const noteEditor = document.getElementById("noteEditor");
    const noteMeta = document.getElementById("noteMeta");
    const saveStatus = document.getElementById("saveStatus");
    const toolbar = document.getElementById("toolbar");
    const blockStyle = document.getElementById("blockStyle");
    const fontName = document.getElementById("fontName");
    const highlightColor = document.getElementById("highlightColor");
    const removeFormatButton = document.getElementById("removeFormatButton");

    let notes = [];
    let activeNoteId = null;
    let savedRange = null;
    let saveTimer = null;

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
        throw new Error(data.message || "Notes request failed.");
      }

      return data;
    }

    function showApiError(error) {
      saveStatus.innerHTML = '<span class="save-dot"></span><span>Could not save</span>';
      window.alert(error.message || "Could not save notes.");
    }

    function normalizeNote(note) {
      return {
        id: String(note.id),
        title: note.title || "",
        body: note.body || "",
        createdAt: Number(note.createdAt) || Date.now(),
        updatedAt: Number(note.updatedAt) || Date.now()
      };
    }

    async function loadNotesFromApi() {
      try {
        const data = await apiRequest(NOTES_API.list);
        notes = (data.notes || []).map(normalizeNote);

        if (notes.length === 0) {
          const created = await apiRequest(NOTES_API.create, {
            method: "POST",
            body: JSON.stringify({
              title: "",
              body: ""
            })
          });

          notes.push(normalizeNote(created.note));
        }

        activeNoteId = notes[0].id;
        renderNoteList();
        renderEditor();
        showSaved();
      } catch (error) {
        showApiError(error);
      }
    }

    function getActiveNote() {
      return notes.find((note) => note.id === activeNoteId) || null;
    }

    function stripHtml(html) {
      const temp = document.createElement("div");
      temp.innerHTML = html;
      return (temp.textContent || temp.innerText || "")
        .replace(/\s+/g, " ")
        .trim();
    }

    function formatListDate(timestamp) {
      const date = new Date(timestamp);
      const today = new Date();
      const sameDay = date.toDateString() === today.toDateString();

      if (sameDay) {
        return date.toLocaleTimeString("en-US", {
          hour: "2-digit",
          minute: "2-digit"
        });
      }

      return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: date.getFullYear() === today.getFullYear() ? undefined : "numeric"
      });
    }

    function formatEditorDate(timestamp) {
      return `Edited ${new Date(timestamp).toLocaleString("en-US", {
        weekday: "short",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      })}`;
    }

    function renderNoteList() {
      const query = noteSearch.value.trim().toLowerCase();

      const visibleNotes = [...notes]
        .sort((a, b) => b.updatedAt - a.updatedAt)
        .filter((note) => {
          const searchableText = `${note.title} ${stripHtml(note.body)}`.toLowerCase();
          return searchableText.includes(query);
        });

      noteList.innerHTML = "";

      if (visibleNotes.length === 0) {
        const empty = document.createElement("li");
        empty.className = "empty-list";
        empty.textContent = query
          ? "No notes match your search."
          : "Your saved notes will appear here.";
        noteList.appendChild(empty);
        return;
      }

      visibleNotes.forEach((note) => {
        const item = document.createElement("li");
        item.className = `note-card ${note.id === activeNoteId ? "active" : ""}`;
        item.dataset.id = note.id;

        const title = document.createElement("div");
        title.className = "note-card-title";
        title.textContent = note.title.trim() || "Untitled Note";

        const preview = document.createElement("div");
        preview.className = "note-card-preview";
        preview.textContent = stripHtml(note.body) || "No additional text";

        const date = document.createElement("div");
        date.className = "note-card-date";
        date.textContent = formatListDate(note.updatedAt);

        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className = "note-card-delete";
        deleteButton.textContent = "×";
        deleteButton.title = "Delete note";
        deleteButton.setAttribute("aria-label", "Delete note");

        deleteButton.addEventListener("click", (event) => {
          event.stopPropagation();
          deleteNote(note.id);
        });

        item.addEventListener("click", () => {
          selectNote(note.id);
        });

        item.append(title, preview, date, deleteButton);
        noteList.appendChild(item);
      });
    }

    function renderEditor() {
      const note = getActiveNote();

      if (!note) return;

      noteTitle.value = note.title;
      noteEditor.innerHTML = note.body;
      noteMeta.textContent = formatEditorDate(note.updatedAt);
      updateToolbarState();
    }

    function selectNote(noteId) {
      flushSave();
      activeNoteId = String(noteId);
      renderNoteList();
      renderEditor();
      noteTitle.focus();
    }

    async function createNote() {
      await flushSave();

      try {
        const data = await apiRequest(NOTES_API.create, {
          method: "POST",
          body: JSON.stringify({
            title: "",
            body: ""
          })
        });

        const note = normalizeNote(data.note);
        notes.push(note);
        activeNoteId = note.id;
        noteSearch.value = "";
        renderNoteList();
        renderEditor();
        noteTitle.focus();
        showSaved();
      } catch (error) {
        showApiError(error);
      }
    }

    async function deleteNote(noteId) {
      const note = notes.find((item) => item.id === String(noteId));
      if (!note) return;

      const displayTitle = note.title.trim() || "Untitled Note";
      const confirmed = window.confirm(`Delete “${displayTitle}”?`);
      if (!confirmed) return;

      const previousNotes = notes;
      const previousActiveNoteId = activeNoteId;
      notes = notes.filter((item) => item.id !== String(noteId));

      if (notes.length === 0) {
        activeNoteId = null;
      } else if (activeNoteId === noteId) {
        activeNoteId = [...notes].sort((a, b) => b.updatedAt - a.updatedAt)[0].id;
      }

      renderNoteList();
      renderEditor();

      try {
        await apiRequest(NOTES_API.delete, {
          method: "DELETE",
          body: JSON.stringify({ id: noteId })
        });

        if (notes.length === 0) {
          await createNote();
        }
      } catch (error) {
        notes = previousNotes;
        activeNoteId = previousActiveNoteId;
        renderNoteList();
        renderEditor();
        showApiError(error);
      }
    }

    function showSaving() {
      saveStatus.innerHTML = '<span class="save-dot"></span><span>Saving…</span>';
    }

    function showSaved() {
      saveStatus.innerHTML = '<span class="save-dot"></span><span>Saved</span>';
    }

    function scheduleSave() {
      showSaving();
      window.clearTimeout(saveTimer);
      saveTimer = window.setTimeout(flushSave, 350);
    }

    async function flushSave() {
      window.clearTimeout(saveTimer);

      const note = getActiveNote();
      if (!note) return;

      note.title = noteTitle.value;
      note.body = noteEditor.innerHTML;
      note.updatedAt = Date.now();

      noteMeta.textContent = formatEditorDate(note.updatedAt);
      renderNoteList();
      showSaved();

      try {
        const data = await apiRequest(NOTES_API.update, {
          method: "PATCH",
          body: JSON.stringify({
            id: note.id,
            title: note.title,
            body: note.body
          })
        });

        Object.assign(note, normalizeNote(data.note));
        noteMeta.textContent = formatEditorDate(note.updatedAt);
        renderNoteList();
        showSaved();
      } catch (error) {
        showApiError(error);
      }
    }

    function saveCurrentRange() {
      const selection = window.getSelection();

      if (!selection || selection.rangeCount === 0) return;

      const range = selection.getRangeAt(0);
      if (noteEditor.contains(range.commonAncestorContainer)) {
        savedRange = range.cloneRange();
      }
    }

    function restoreRange() {
      if (!savedRange) {
        noteEditor.focus();
        return;
      }

      const selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange(savedRange);
    }

    function runCommand(command, value = null) {
      restoreRange();
      noteEditor.focus();

      try {
        document.execCommand("styleWithCSS", false, true);
        document.execCommand(command, false, value);
      } catch (error) {
        console.warn(`Formatting command failed: ${command}`, error);
      }

      saveCurrentRange();
      scheduleSave();
      updateToolbarState();
    }

    function updateToolbarState() {
      toolbar.querySelectorAll("[data-command]").forEach((button) => {
        const command = button.dataset.command;
        let active = false;

        try {
          active = document.queryCommandState(command);
        } catch {
          active = false;
        }

        button.classList.toggle("active", active);
      });
    }

    noteTitle.addEventListener("input", scheduleSave);
    noteEditor.addEventListener("input", scheduleSave);
    noteEditor.addEventListener("mouseup", saveCurrentRange);
    noteEditor.addEventListener("keyup", () => {
      saveCurrentRange();
      updateToolbarState();
    });
    noteEditor.addEventListener("focus", saveCurrentRange);

    toolbar.addEventListener("mousedown", (event) => {
      if (event.target.closest("button")) {
        event.preventDefault();
      }
    });

    toolbar.addEventListener("click", (event) => {
      const button = event.target.closest("[data-command]");
      if (!button) return;
      runCommand(button.dataset.command);
    });

    blockStyle.addEventListener("change", () => {
      runCommand("formatBlock", blockStyle.value);
      blockStyle.value = "p";
    });

    fontName.addEventListener("change", () => {
      runCommand("fontName", fontName.value);
    });

    highlightColor.addEventListener("input", () => {
      restoreRange();
      noteEditor.focus();

      const applied = document.execCommand("hiliteColor", false, highlightColor.value);
      if (!applied) {
        document.execCommand("backColor", false, highlightColor.value);
      }

      saveCurrentRange();
      scheduleSave();
    });

    removeFormatButton.addEventListener("mousedown", (event) => {
      event.preventDefault();
    });

    removeFormatButton.addEventListener("click", () => {
      runCommand("removeFormat");
    });

    newNoteButton.addEventListener("click", createNote);
    noteSearch.addEventListener("input", renderNoteList);

    window.addEventListener("beforeunload", flushSave);

    renderNoteList();
    loadNotesFromApi();
