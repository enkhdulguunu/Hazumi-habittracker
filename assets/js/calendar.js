// Calendar
        let currentDate = new Date();
        let selectedDate = new Date();
        let currentView = 'month';
        const CALENDAR_API = {
            list: 'api/calendar/list.php',
            create: 'api/calendar/create.php',
            delete: 'api/calendar/delete.php'
        };

        let events = {};
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const dayNames = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
        document.getElementById('prev-btn').addEventListener('click', () => navigateCalendar(-1));
        document.getElementById('next-btn').addEventListener('click', () => navigateCalendar(1));
        document.querySelectorAll('[data-calendar-view]').forEach((button) => {
            button.addEventListener('click', () => switchView(button.dataset.calendarView));
        });
        document.getElementById('add-task-button').addEventListener('click', addTask);
        async function apiRequest(endpoint, options = {}) {
            const requestOptions = {
                credentials: 'same-origin',
                ...options
            };

            if (requestOptions.body && !requestOptions.headers) {
                requestOptions.headers = {
                    'Content-Type': 'application/json'
                };
            }

            const response = await fetch(endpoint, requestOptions);
            const data = await response.json().catch(() => ({}));

            if (response.status === 401) {
                window.location.href = 'login.php';
                return new Promise(() => {});
            }

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Calendar request failed.');
            }

            return data;
        }

        function showApiError(error) {
            window.alert(error.message || 'Could not save calendar changes.');
        }

        async function loadEvents() {
            const data = await apiRequest(CALENDAR_API.list);
            events = data.events || {};
            renderCalendar();
        }

        function switchView(view) {
            currentView = view;
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.classList.remove('active');
                if(btn.innerText.toLowerCase() === view) btn.classList.add('active');
            });
            renderCalendar();
        }

        function navigateCalendar(direction) {
            if (currentView === 'month') {
                currentDate.setDate(1);
                currentDate.setMonth(currentDate.getMonth() + direction);
                selectedDate = new Date(currentDate);
            } else if (currentView === 'week') {
                currentDate.setDate(currentDate.getDate() + (direction * 7));
                selectedDate = new Date(currentDate);
            } else if (currentView === 'day') {
                currentDate.setDate(currentDate.getDate() + direction);
                selectedDate = new Date(currentDate);
            } else if (currentView === 'year') {
                currentDate.setFullYear(currentDate.getFullYear() + direction);
                selectedDate = new Date(currentDate.getFullYear(), 0, 1);
            }

            renderCalendar();
        }

        function formatDateKey(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        // Rendering engine
        function renderCalendar() {
            const grid = document.getElementById('calendar-grid');
            const gridHeader = document.getElementById('grid-header');
            const monthDisplay = document.getElementById('month-display');

            grid.innerHTML = '';
            gridHeader.style.display = 'grid'; 

            if (currentView === 'month') {
                monthDisplay.innerText = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
                gridHeader.innerHTML = '<div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>';
                grid.style.gridTemplateColumns = 'repeat(7, 1fr)';

                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();

                let firstDayIndex = new Date(year, month, 1).getDay(); 
                firstDayIndex = firstDayIndex === 0 ? 6 : firstDayIndex - 1; // Make Monday the first day of the week

                const prevLastDay = new Date(year, month, 0).getDate();
                const lastDay = new Date(year, month + 1, 0).getDate();

                // Prev month fill
                for (let x = firstDayIndex; x > 0; x--) {
                    const prevDate = new Date(year, month - 1, prevLastDay - x + 1);
                    createDayCell(prevDate, true);
                }

                // Current month
                for (let i = 1; i <= lastDay; i++) {
                    const currDate = new Date(year, month, i);
                    createDayCell(currDate, false);
                }

                // Next month fill
                const totalCells = firstDayIndex + lastDay;
                const nextDays = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
                for (let j = 1; j <= nextDays; j++) {
                    const nextDate = new Date(year, month + 1, j);
                    createDayCell(nextDate, true);
                }

            } else if (currentView === 'week') {
                monthDisplay.innerText = `Week of ${currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
                gridHeader.innerHTML = '<div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>';
                grid.style.gridTemplateColumns = 'repeat(7, 1fr)';

                const startOfWeek = new Date(currentDate);
                const day = startOfWeek.getDay();
                const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1); 
                startOfWeek.setDate(diff);

                for (let i = 0; i < 7; i++) {
                    const weekDay = new Date(startOfWeek);
                    weekDay.setDate(startOfWeek.getDate() + i);
                    createDayCell(weekDay, false);
                }

            } else if (currentView === 'day') {
                monthDisplay.innerText = currentDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                gridHeader.style.display = 'none'; 
                grid.style.gridTemplateColumns = '1fr';
                
                createDayCell(currentDate, false, true);

            } else if (currentView === 'year') {
                monthDisplay.innerText = currentDate.getFullYear();
                gridHeader.style.display = 'none';
                grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
                
                for (let m = 0; m < 12; m++) {
                    const monthCard = document.createElement('div');
                    monthCard.className = 'day-cell';
                    monthCard.style.justifyContent = 'center';
                    monthCard.style.alignItems = 'center';
                    monthCard.innerHTML = `<span style="font-weight: 800; font-size: 18px;">${monthNames[m]}</span>`;
                    monthCard.onclick = () => {
                        currentDate.setMonth(m);
                        switchView('month');
                    };
                    grid.appendChild(monthCard);
                }
            }

            renderTaskList();
        }

        function createDayCell(date, isOtherMonth, isDayView = false) {
            const grid = document.getElementById('calendar-grid');
            const cell = document.createElement('div');
            cell.className = 'day-cell';
            
            const dateKey = formatDateKey(date);
            const todayKey = formatDateKey(new Date());

            if (isOtherMonth) cell.classList.add('other-month');
            if (dateKey === todayKey) cell.classList.add('today');

            if (formatDateKey(selectedDate) === dateKey) {
                cell.style.borderColor = 'var(--primary-color)';
                cell.style.boxShadow = '0 16px 36px rgba(108, 210, 89, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.92)';
            }

            let content = `<span class="day-number">${date.getDate()}</span>`;
            
            if (events[dateKey] && events[dateKey].length > 0) {
                const taskCount = events[dateKey].length;

                if (isDayView) {
                    content += `<div style="font-size: 14px; margin-top: 10px; font-weight: 700; color: var(--primary-color);">${taskCount} Tasks scheduled.</div>`;
                } else {
                    content += `
                        <div class="task-count-wrap">
                            <div class="task-count-badge" aria-label="${taskCount} tasks scheduled">
                                <span class="task-count-dot"></span>
                                <span class="task-count-number">${taskCount}</span>
                            </div>
                        </div>
                    `;
                }
            }

            cell.innerHTML = content;

            cell.addEventListener('click', () => {
                selectedDate = new Date(date);
                renderCalendar(); 
            });

            grid.appendChild(cell);
        }

        // Task operations
        function renderTaskList() {
            const listContainer = document.getElementById('task-list-container');
            listContainer.innerHTML = '';
            
            const dateKey = formatDateKey(selectedDate);
            const formattedDisplayDate = selectedDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            document.getElementById('selected-date-title').innerText = `Schedule for ${formattedDisplayDate}`;

            const dayEvents = events[dateKey] || [];

            if (dayEvents.length === 0) {
                listContainer.innerHTML = `<p style="text-align: center; color: var(--text-muted); margin-top: 20px; font-size: 14px;">No tasks scheduled for this day.</p>`;
                return;
            }

            dayEvents.sort((a, b) => a.time.localeCompare(b.time));

            dayEvents.forEach(ev => {
                const item = document.createElement('div');
                item.className = 'task-item';
                item.innerHTML = `
                    <div class="task-info">
                        <h4>${escapeHtml(ev.desc)}</h4>
                        <span>Time: ${escapeHtml(ev.time)}</span>
                    </div>
                    <button
                        class="delete-btn"
                        type="button"
                        aria-label="Delete task"
                    >&times;</button>
                `;

                item
                    .querySelector('.delete-btn')
                    .addEventListener('click', () => deleteTask(ev.id));
                listContainer.appendChild(item);
            });
        }

        async function addTask() {
            const descInput = document.getElementById('task-desc');
            const timeInput = document.getElementById('task-time');

            if (!descInput.value.trim()) {
                alert('Please enter a task description.');
                return;
            }

            const dateKey = formatDateKey(selectedDate);
            
            if (!events[dateKey]) {
                events[dateKey] = [];
            }

            try {
                const data = await apiRequest(CALENDAR_API.create, {
                    method: 'POST',
                    body: JSON.stringify({
                        date: dateKey,
                        desc: descInput.value.trim(),
                        time: timeInput.value || '09:00'
                    })
                });

                events[dateKey].push({
                    id: data.event.id,
                    desc: data.event.desc,
                    time: data.event.time
                });

                descInput.value = '';
                renderCalendar();
            } catch (error) {
                showApiError(error);
            }
        }

        function deleteTask(id) {
            const dateKey = formatDateKey(selectedDate);
            if (events[dateKey]) {
                const previousEvents = JSON.parse(JSON.stringify(events));
                events[dateKey] = events[dateKey].filter(ev => String(ev.id) !== String(id));
                if (events[dateKey].length === 0) {
                    delete events[dateKey];
                }
                renderCalendar();

                apiRequest(CALENDAR_API.delete, {
                    method: 'DELETE',
                    body: JSON.stringify({ id })
                }).catch((error) => {
                    events = previousEvents;
                    renderCalendar();
                    showApiError(error);
                });
            }
        }

        document
            .getElementById('task-desc')
            .addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTask();
                }
            });

        renderCalendar();
        loadEvents().catch(showApiError);

