const API_URL = 'api/tasks.php';
let tasks = [];
let editingTaskId = null;
let notifiedTasks = new Set(); // Keep track of rendered notifications locally
let suggestions = ['Buy groceries', 'Read 10 pages', 'Exercise for 30 mins', 'Call a friend'];

document.addEventListener('DOMContentLoaded', async () => {
    const authStatus = await checkAuthStatus();
    if (!authStatus.authenticated) {
        window.location.href = 'login.html';
        return;
    }

    document.getElementById('welcomeUser').textContent = `Welcome, ${authStatus.username}`;
    fetchTasks();
    renderSuggestions();

    document.getElementById('taskForm').addEventListener('submit', handleAddTask);
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);

    // Check for reminders every 30 secs
    setInterval(checkReminders, 30000);
});

async function checkAuthStatus() {
    try {
        const response = await fetch('api/auth.php?action=session');
        return await response.json();
    } catch (err) {
        return { authenticated: false };
    }
}

async function handleLogout() {
    await fetch('api/auth.php?action=logout');
    window.location.href = 'login.html';
}

async function fetchTasks() {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error('Network error');
        tasks = await response.json();
        renderTasks();
        checkReminders(); // check immediately after fetching
    } catch (error) {
        console.error('Failed to fetch tasks:', error);
    }
}

async function handleAddTask(e) {
    e.preventDefault();
    const titleInput = document.getElementById('taskTitle');
    const descInput = document.getElementById('taskDesc');
    const reminderInput = document.getElementById('taskReminder');
    const submitBtn = document.querySelector('#taskForm button[type="submit"]');

    const taskPayload = {
        title: titleInput.value.trim(),
        description: descInput.value.trim() || null,
        reminder_time: reminderInput.value || null
    };

    try {
        if (editingTaskId) {
            // Update existing task
            const response = await fetch(`${API_URL}?id=${editingTaskId}`, {
                method: 'PUT',
                body: JSON.stringify(taskPayload),
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.ok) {
                const taskIndex = tasks.findIndex(t => t.id == editingTaskId);
                if (taskIndex !== -1) {
                    tasks[taskIndex].title = taskPayload.title;
                    tasks[taskIndex].description = taskPayload.description;
                    tasks[taskIndex].reminder_time = taskPayload.reminder_time;
                }
                
                // Allow alarms to fire again if time was changed
                notifiedTasks.delete(editingTaskId);
                editingTaskId = null;
                submitBtn.innerHTML = 'Add Task';
                document.querySelector('.task-input-section h2').textContent = 'Add a Task';
            }
        } else {
            // Add new task
            const response = await fetch(API_URL, {
                method: 'POST',
                body: JSON.stringify(taskPayload),
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.ok) {
                const addedTask = await response.json();
                tasks.unshift(addedTask);
            }
        }

        renderTasks();
        titleInput.value = '';
        descInput.value = '';
        reminderInput.value = '';
        checkReminders(); 
    } catch (error) {
        console.error('Failed to save task:', error);
    }
}

async function toggleTask(id, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    try {
        await fetch(`${API_URL}?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify({ completed: newStatus }),
            headers: { 'Content-Type': 'application/json' }
        });

        // Update local state
        const taskIndex = tasks.findIndex(t => t.id == id);
        if (taskIndex !== -1) {
            tasks[taskIndex].completed = newStatus;
            
            // If task is restarted and time has passed, silently add to notified to prevent an instant stale alarm
            if (newStatus === 0 && tasks[taskIndex].reminder_time) {
                const reminderTime = new Date(tasks[taskIndex].reminder_time);
                if (reminderTime <= new Date()) {
                    notifiedTasks.add(id);
                    
                    // User requested UX: Prompt them if they want to update the stale date
                    if (confirm("Welcome back! The reminder time for this task has already passed. Would you like to set a new time?")) {
                        editTask(id);
                    }
                }
            }
            
            renderTasks();
        }
    } catch (error) {
        console.error('Failed to update task:', error);
    }
}

async function editTask(id) {
    const task = tasks.find(t => t.id == id);
    if (!task) return;

    editingTaskId = id;
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskDesc').value = task.description || '';
    
    if (task.reminder_time) {
        // Form needs exactly YYYY-MM-DDTHH:MM
        // e.g., '2023-10-05 14:30:00' -> '2023-10-05T14:30'
        document.getElementById('taskReminder').value = task.reminder_time.replace(' ', 'T').substring(0, 16);
    } else {
        document.getElementById('taskReminder').value = '';
    }

    const submitBtn = document.querySelector('#taskForm button[type="submit"]');
    submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Update Task';
    document.querySelector('.task-input-section h2').textContent = 'Edit Task';

    document.getElementById('taskForm').scrollIntoView({ behavior: 'smooth' });
    document.getElementById('taskTitle').focus();
}

async function deleteTask(id) {
    try {
        await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE'
        });

        // Update local state
        tasks = tasks.filter(t => t.id != id);
        renderTasks();
    } catch (error) {
        console.error('Failed to delete task:', error);
    }
}

function renderSuggestions() {
    const list = document.getElementById('suggestionsList');
    if (!list) return;
    list.innerHTML = '';

    suggestions.forEach((sugg, index) => {
        const chip = document.createElement('div');
        chip.className = 'suggestion-chip';

        const textSpan = document.createElement('span');
        textSpan.textContent = sugg;
        textSpan.onclick = () => fillFormWithSuggestion(sugg);

        const actions = document.createElement('div');
        actions.className = 'suggestion-actions';

        const editBtn = document.createElement('button');
        editBtn.className = 'suggestion-btn edit-sugg';
        editBtn.innerHTML = '<i class="fa-solid fa-pen"></i>';
        editBtn.title = 'Edit Suggestion';
        editBtn.onclick = (e) => {
            e.stopPropagation();
            editSuggestion(index);
        };

        actions.appendChild(editBtn);
        chip.appendChild(textSpan);
        chip.appendChild(actions);
        list.appendChild(chip);
    });
}

function fillFormWithSuggestion(text) {
    document.getElementById('taskTitle').value = text;
    document.getElementById('taskTitle').focus();
}

function editSuggestion(index) {
    const newText = prompt("Edit suggestion:", suggestions[index]);
    if (newText !== null && newText.trim() !== '') {
        suggestions[index] = newText.trim();
        renderSuggestions();
    }
}

function renderTasks() {
    const list = document.getElementById('tasksList');
    list.innerHTML = '';

    if (tasks.length === 0) {
        list.innerHTML = '<p style="text-align:center; color: var(--text-sec); padding: 2rem;">No tasks found. Add a new one to get started!</p>';
        return;
    }

    tasks.forEach(task => {
        const isCompleted = parseInt(task.completed) === 1;

        // Format reminder text if present
        let reminderHtml = '';
        if (task.reminder_time) {
            const rDate = new Date(task.reminder_time);
            const dateStr = rDate.toLocaleDateString();
            const timeStr = rDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            reminderHtml = `
                <div class="task-meta">
                    <i class="fa-regular fa-bell"></i>
                    ${dateStr} @ ${timeStr}
                </div>
            `;
        }

        const card = document.createElement('div');
        card.className = `task-card ${isCompleted ? 'completed' : ''}`;
        card.innerHTML = `
            <div class="task-info">
                <h3 class="task-title">${escapeHTML(task.title)}</h3>
                ${task.description ? `<p class="task-desc">${escapeHTML(task.description)}</p>` : ''}
                ${reminderHtml}
            </div>
            <div class="task-actions">
                <button class="btn-icon complete" onclick="toggleTask(${task.id}, ${isCompleted})" title="Mark Complete">
                    <i class="fa-solid ${isCompleted ? 'fa-rotate-left' : 'fa-check'}"></i>
                </button>
                <button class="btn-icon" onclick="editTask(${task.id})" title="Edit Task">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn-icon delete" onclick="deleteTask(${task.id})" title="Delete Task">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
        `;
        list.appendChild(card);
    });
}

function checkReminders() {
    const now = new Date();
    tasks.forEach(task => {
        if (!parseInt(task.completed) && task.reminder_time) {
            const reminderTime = new Date(task.reminder_time);
            // If the reminder time is in the past or exactly now, and we haven't notified yet
            if (reminderTime <= now && !notifiedTasks.has(task.id)) {
                showNotification(task);
                notifiedTasks.add(task.id);
            }
        }
    });
}

function showNotification(task) {
    const area = document.getElementById('notificationArea');
    const notification = document.createElement('div');
    notification.className = 'notification';

    // Play a gentle sound (Optional, based on browser policies, might need user interaction first)
    // const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
    // audio.play().catch(e => console.log('Audio autoplay blocked'));

    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="notification-content">
            <h4>Reminder: ${escapeHTML(task.title)}</h4>
            <p>${task.description ? escapeHTML(task.description) : 'It is time to work on this task.'}</p>
        </div>
        <button class="notification-close" onclick="closeNotification(this)"><i class="fa-solid fa-xmark"></i></button>
    `;

    area.appendChild(notification);

    // Auto close after 10 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            closeNotification(notification.querySelector('.notification-close'));
        }
    }, 10000);
}

function closeNotification(btn) {
    const notification = btn.closest('.notification');
    notification.classList.add('closing');

    // Remove element after animation finishes
    notification.addEventListener('animationend', () => {
        notification.remove();
    });
}

// Utility to prevent XSS
function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, tag => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[tag] || tag));
}
