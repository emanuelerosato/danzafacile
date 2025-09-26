/**
 * CalendarManager - Handles calendar view and schedule visualization
 *
 * Provides calendar functionality for staff schedules:
 * - Week/month view switching
 * - Schedule rendering and display
 * - Time slot management
 * - Drag and drop support
 * - Schedule conflict visualization
 */

export class CalendarManager {
    constructor(options = {}) {
        this.options = {
            manager: null,
            defaultView: 'week',
            timeFormat: '24h',
            startTime: '08:00',
            endTime: '22:00',
            slotDuration: 30, // minutes
            firstDay: 1, // Monday = 1
            ...options
        };

        // Reference to main manager
        this.manager = this.options.manager;

        // Calendar state
        this.state = {
            currentView: this.options.defaultView,
            currentDate: new Date(),
            selectedDate: null,
            selectedSchedule: null,
            schedules: new Map(),
            timeSlots: [],
            conflicts: []
        };

        // Calendar elements
        this.elements = {
            container: null,
            header: null,
            navigation: null,
            grid: null,
            timeColumn: null
        };

        // Event listeners
        this.eventListeners = new Map();

        // Initialize
        this.init();
    }

    /**
     * Initialize the calendar manager
     */
    async init() {
        try {
            this.log('Initializing CalendarManager...');

            // Setup calendar container
            this.setupCalendarContainer();

            // Generate time slots
            this.generateTimeSlots();

            // Setup event listeners
            this.setupEventListeners();

            // Render initial calendar
            await this.render();

            this.log('CalendarManager initialized successfully');

        } catch (error) {
            console.error('Failed to initialize CalendarManager:', error);
            this.manager.modules.notification?.error('Errore nell\'inizializzazione del calendario');
        }
    }

    /**
     * Setup calendar container and basic structure
     */
    setupCalendarContainer() {
        // Look for existing calendar container
        this.elements.container = document.getElementById('calendar-container');

        if (!this.elements.container) {
            // Create calendar container if it doesn't exist
            this.createCalendarContainer();
        }

        // Setup calendar sections
        this.setupCalendarSections();

        this.log('Calendar container setup completed');
    }

    /**
     * Create calendar container
     */
    createCalendarContainer() {
        const calendarHtml = `
            <div id="calendar-container" class="bg-white rounded-lg shadow p-6 hidden">
                <div id="calendar-header" class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <h3 id="calendar-title" class="text-lg font-semibold text-gray-900"></h3>
                        <div class="flex items-center space-x-2">
                            <button id="prev-period" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button id="next-period" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <button id="today-btn" class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Oggi
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button id="week-view-btn" class="px-3 py-1 text-sm rounded-md">Settimana</button>
                            <button id="month-view-btn" class="px-3 py-1 text-sm rounded-md">Mese</button>
                        </div>
                    </div>
                </div>
                <div id="calendar-grid" class="overflow-x-auto">
                    <!-- Calendar grid will be rendered here -->
                </div>
            </div>
        `;

        // Insert calendar after the main content or at a specific location
        const targetContainer = document.querySelector('.space-y-6') || document.querySelector('.max-w-7xl');
        if (targetContainer) {
            targetContainer.insertAdjacentHTML('beforeend', calendarHtml);
            this.elements.container = document.getElementById('calendar-container');
        }
    }

    /**
     * Setup calendar sections references
     */
    setupCalendarSections() {
        this.elements.header = this.elements.container.querySelector('#calendar-header');
        this.elements.navigation = {
            title: this.elements.container.querySelector('#calendar-title'),
            prevBtn: this.elements.container.querySelector('#prev-period'),
            nextBtn: this.elements.container.querySelector('#next-period'),
            todayBtn: this.elements.container.querySelector('#today-btn'),
            weekBtn: this.elements.container.querySelector('#week-view-btn'),
            monthBtn: this.elements.container.querySelector('#month-view-btn')
        };
        this.elements.grid = this.elements.container.querySelector('#calendar-grid');
    }

    /**
     * Generate time slots for the calendar
     */
    generateTimeSlots() {
        const startTime = this.parseTime(this.options.startTime);
        const endTime = this.parseTime(this.options.endTime);
        const slotMinutes = this.options.slotDuration;

        this.state.timeSlots = [];

        for (let time = startTime; time < endTime; time += slotMinutes * 60000) {
            const date = new Date(time);
            this.state.timeSlots.push({
                time: this.formatTime(date),
                timestamp: time,
                display: this.formatTimeDisplay(date)
            });
        }

        this.log('Generated time slots:', this.state.timeSlots.length);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Navigation buttons
        if (this.elements.navigation.prevBtn) {
            this.elements.navigation.prevBtn.addEventListener('click', () => {
                this.navigatePrevious();
            });
        }

        if (this.elements.navigation.nextBtn) {
            this.elements.navigation.nextBtn.addEventListener('click', () => {
                this.navigateNext();
            });
        }

        if (this.elements.navigation.todayBtn) {
            this.elements.navigation.todayBtn.addEventListener('click', () => {
                this.navigateToday();
            });
        }

        // View switching
        if (this.elements.navigation.weekBtn) {
            this.elements.navigation.weekBtn.addEventListener('click', () => {
                this.switchView('week');
            });
        }

        if (this.elements.navigation.monthBtn) {
            this.elements.navigation.monthBtn.addEventListener('click', () => {
                this.switchView('month');
            });
        }

        // Listen to manager events
        this.manager.on('schedules:updated', (schedules) => {
            this.updateSchedules(schedules);
        });

        this.log('Event listeners setup completed');
    }

    /**
     * Render the calendar
     */
    async render() {
        // Update navigation title
        this.updateNavigationTitle();

        // Update view buttons
        this.updateViewButtons();

        // Render calendar grid
        await this.renderCalendarGrid();

        // Render schedules
        this.renderSchedules();

        this.log('Calendar rendered');
    }

    /**
     * Update navigation title
     */
    updateNavigationTitle() {
        if (!this.elements.navigation.title) return;

        const title = this.state.currentView === 'week'
            ? this.getWeekTitle()
            : this.getMonthTitle();

        this.elements.navigation.title.textContent = title;
    }

    /**
     * Get week title
     */
    getWeekTitle() {
        const weekStart = this.getWeekStart(this.state.currentDate);
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekEnd.getDate() + 6);

        const formatter = new Intl.DateTimeFormat('it-IT', {
            day: 'numeric',
            month: 'short'
        });

        return `${formatter.format(weekStart)} - ${formatter.format(weekEnd)}`;
    }

    /**
     * Get month title
     */
    getMonthTitle() {
        const formatter = new Intl.DateTimeFormat('it-IT', {
            month: 'long',
            year: 'numeric'
        });

        return formatter.format(this.state.currentDate);
    }

    /**
     * Update view buttons state
     */
    updateViewButtons() {
        const { weekBtn, monthBtn } = this.elements.navigation;

        if (weekBtn && monthBtn) {
            // Reset classes
            weekBtn.className = 'px-3 py-1 text-sm rounded-md';
            monthBtn.className = 'px-3 py-1 text-sm rounded-md';

            // Add active class
            if (this.state.currentView === 'week') {
                weekBtn.className += ' bg-white text-gray-900 shadow';
                monthBtn.className += ' text-gray-600 hover:text-gray-900';
            } else {
                monthBtn.className += ' bg-white text-gray-900 shadow';
                weekBtn.className += ' text-gray-600 hover:text-gray-900';
            }
        }
    }

    /**
     * Render calendar grid
     */
    async renderCalendarGrid() {
        if (this.state.currentView === 'week') {
            await this.renderWeekGrid();
        } else {
            await this.renderMonthGrid();
        }
    }

    /**
     * Render week view grid
     */
    async renderWeekGrid() {
        const weekStart = this.getWeekStart(this.state.currentDate);
        const days = [];

        // Generate days for the week
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            days.push(date);
        }

        const gridHtml = `
            <div class="grid grid-cols-8 gap-0 border border-gray-200 rounded-lg overflow-hidden min-h-96">
                <!-- Time column -->
                <div class="bg-gray-50 border-r border-gray-200">
                    <div class="h-12 border-b border-gray-200"></div>
                    ${this.state.timeSlots.map(slot => `
                        <div class="h-12 flex items-center justify-center text-xs text-gray-500 border-b border-gray-100">
                            ${slot.display}
                        </div>
                    `).join('')}
                </div>

                <!-- Day columns -->
                ${days.map(date => `
                    <div class="border-r border-gray-200 last:border-r-0" data-date="${this.formatDate(date)}">
                        <div class="h-12 bg-gray-50 border-b border-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-xs text-gray-500 uppercase">${this.formatDayName(date)}</div>
                                <div class="text-sm font-medium text-gray-900 ${this.isToday(date) ? 'bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center' : ''}">${date.getDate()}</div>
                            </div>
                        </div>
                        ${this.state.timeSlots.map(slot => `
                            <div class="h-12 border-b border-gray-100 relative cursor-pointer hover:bg-gray-50 schedule-slot"
                                 data-date="${this.formatDate(date)}"
                                 data-time="${slot.time}">
                            </div>
                        `).join('')}
                    </div>
                `).join('')}
            </div>
        `;

        this.elements.grid.innerHTML = gridHtml;

        // Setup click handlers for time slots
        this.setupTimeSlotHandlers();
    }

    /**
     * Render month view grid
     */
    async renderMonthGrid() {
        const monthStart = new Date(this.state.currentDate.getFullYear(), this.state.currentDate.getMonth(), 1);
        const monthEnd = new Date(this.state.currentDate.getFullYear(), this.state.currentDate.getMonth() + 1, 0);
        const calendarStart = this.getWeekStart(monthStart);
        const days = [];

        // Generate days for the month view (including padding days)
        let currentDate = new Date(calendarStart);
        while (currentDate <= monthEnd || days.length % 7 !== 0) {
            days.push(new Date(currentDate));
            currentDate.setDate(currentDate.getDate() + 1);

            // Prevent infinite loop
            if (days.length > 42) break;
        }

        const gridHtml = `
            <div class="grid grid-cols-7 gap-0 border border-gray-200 rounded-lg overflow-hidden">
                <!-- Day headers -->
                ${['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'].map(day => `
                    <div class="h-10 bg-gray-50 border-b border-gray-200 flex items-center justify-center text-xs font-medium text-gray-700">
                        ${day}
                    </div>
                `).join('')}

                <!-- Day cells -->
                ${days.map(date => {
                    const isCurrentMonth = date.getMonth() === this.state.currentDate.getMonth();
                    const isToday = this.isToday(date);

                    return `
                        <div class="h-24 border-r border-b border-gray-100 last:border-r-0 p-1 cursor-pointer hover:bg-gray-50 ${!isCurrentMonth ? 'bg-gray-50 text-gray-400' : ''}"
                             data-date="${this.formatDate(date)}">
                            <div class="flex items-center justify-between h-6">
                                <span class="text-sm ${isToday ? 'bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center' : ''}">${date.getDate()}</span>
                            </div>
                            <div class="schedule-items space-y-1" data-date="${this.formatDate(date)}">
                                <!-- Schedule items will be rendered here -->
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        this.elements.grid.innerHTML = gridHtml;

        // Setup click handlers for day cells
        this.setupDayCellHandlers();
    }

    /**
     * Setup time slot click handlers
     */
    setupTimeSlotHandlers() {
        const slots = this.elements.grid.querySelectorAll('.schedule-slot');

        slots.forEach(slot => {
            slot.addEventListener('click', (e) => {
                const date = e.target.dataset.date;
                const time = e.target.dataset.time;
                this.handleTimeSlotClick(date, time);
            });
        });
    }

    /**
     * Setup day cell click handlers
     */
    setupDayCellHandlers() {
        const cells = this.elements.grid.querySelectorAll('[data-date]');

        cells.forEach(cell => {
            cell.addEventListener('click', (e) => {
                if (e.target.closest('.schedule-item')) return; // Don't trigger for schedule items

                const date = cell.dataset.date;
                this.handleDayCellClick(date);
            });
        });
    }

    /**
     * Handle time slot click
     */
    handleTimeSlotClick(date, time) {
        this.state.selectedDate = date;
        this.state.selectedTime = time;

        // Navigate to create schedule page with pre-filled data
        const createUrl = new URL('/admin/staff-schedules/create', window.location.origin);
        createUrl.searchParams.set('date', date);
        createUrl.searchParams.set('start_time', time);

        window.location.href = createUrl.toString();
    }

    /**
     * Handle day cell click (month view)
     */
    handleDayCellClick(date) {
        this.state.selectedDate = date;

        // Switch to week view and navigate to that date
        this.state.currentDate = new Date(date);
        this.switchView('week');
    }

    /**
     * Render schedules on the calendar
     */
    renderSchedules() {
        if (this.state.currentView === 'week') {
            this.renderWeekSchedules();
        } else {
            this.renderMonthSchedules();
        }
    }

    /**
     * Render schedules in week view
     */
    renderWeekSchedules() {
        // Clear existing schedule items
        const scheduleItems = this.elements.grid.querySelectorAll('.schedule-item');
        scheduleItems.forEach(item => item.remove());

        this.state.schedules.forEach(schedule => {
            const scheduleDate = this.formatDate(new Date(schedule.date));
            const dayColumn = this.elements.grid.querySelector(`[data-date="${scheduleDate}"]`);

            if (dayColumn) {
                const scheduleElement = this.createScheduleElement(schedule, 'week');
                this.positionScheduleInWeek(scheduleElement, schedule, dayColumn);
            }
        });
    }

    /**
     * Render schedules in month view
     */
    renderMonthSchedules() {
        // Clear existing schedule items
        const scheduleContainers = this.elements.grid.querySelectorAll('.schedule-items');
        scheduleContainers.forEach(container => {
            container.innerHTML = '';
        });

        this.state.schedules.forEach(schedule => {
            const scheduleDate = this.formatDate(new Date(schedule.date));
            const container = this.elements.grid.querySelector(`.schedule-items[data-date="${scheduleDate}"]`);

            if (container) {
                const scheduleElement = this.createScheduleElement(schedule, 'month');
                container.appendChild(scheduleElement);
            }
        });
    }

    /**
     * Create schedule element
     */
    createScheduleElement(schedule, viewType) {
        const isWeekView = viewType === 'week';

        const element = document.createElement('div');
        element.className = `schedule-item ${isWeekView ? 'absolute left-1 right-1 rounded px-2 py-1 text-xs' : 'rounded px-1 py-0.5 text-xs truncate'}`;
        element.dataset.scheduleId = schedule.id;

        // Color based on schedule type or status
        const colorClass = this.getScheduleColorClass(schedule);
        element.className += ` ${colorClass}`;

        // Content
        const timeText = isWeekView
            ? `${schedule.start_time} - ${schedule.end_time}`
            : schedule.start_time;

        element.innerHTML = `
            <div class="font-medium">${schedule.staff_name || 'Staff'}</div>
            ${isWeekView ? `<div class="text-xs opacity-75">${timeText}</div>` : `<div>${timeText}</div>`}
            ${isWeekView && schedule.type ? `<div class="text-xs opacity-75">${schedule.type}</div>` : ''}
        `;

        // Click handler
        element.addEventListener('click', (e) => {
            e.stopPropagation();
            this.handleScheduleClick(schedule);
        });

        return element;
    }

    /**
     * Position schedule element in week view
     */
    positionScheduleInWeek(element, schedule, dayColumn) {
        const startTime = this.parseTime(schedule.start_time);
        const endTime = this.parseTime(schedule.end_time);
        const dayStartTime = this.parseTime(this.options.startTime);

        // Calculate position
        const startOffset = (startTime - dayStartTime) / (this.options.slotDuration * 60000);
        const duration = (endTime - startTime) / (this.options.slotDuration * 60000);

        // Position the element
        const slotHeight = 48; // 3rem = 48px
        const topPosition = startOffset * slotHeight + 48; // Add header height
        const height = duration * slotHeight;

        element.style.top = `${topPosition}px`;
        element.style.height = `${height}px`;
        element.style.zIndex = '10';

        dayColumn.style.position = 'relative';
        dayColumn.appendChild(element);
    }

    /**
     * Get schedule color class based on type/status
     */
    getScheduleColorClass(schedule) {
        const colorMap = {
            'lezione': 'bg-blue-100 text-blue-800 border border-blue-200',
            'prova': 'bg-green-100 text-green-800 border border-green-200',
            'evento': 'bg-purple-100 text-purple-800 border border-purple-200',
            'pausa': 'bg-gray-100 text-gray-800 border border-gray-200',
            'default': 'bg-rose-100 text-rose-800 border border-rose-200'
        };

        return colorMap[schedule.type?.toLowerCase()] || colorMap.default;
    }

    /**
     * Handle schedule click
     */
    handleScheduleClick(schedule) {
        this.state.selectedSchedule = schedule;
        window.location.href = `/admin/staff-schedules/${schedule.id}`;
    }

    /**
     * Navigation methods
     */
    navigatePrevious() {
        if (this.state.currentView === 'week') {
            this.state.currentDate.setDate(this.state.currentDate.getDate() - 7);
        } else {
            this.state.currentDate.setMonth(this.state.currentDate.getMonth() - 1);
        }
        this.render();
    }

    navigateNext() {
        if (this.state.currentView === 'week') {
            this.state.currentDate.setDate(this.state.currentDate.getDate() + 7);
        } else {
            this.state.currentDate.setMonth(this.state.currentDate.getMonth() + 1);
        }
        this.render();
    }

    navigateToday() {
        this.state.currentDate = new Date();
        this.render();
    }

    /**
     * Switch calendar view
     */
    switchView(view) {
        if (view === this.state.currentView) return;

        this.state.currentView = view;
        this.render();

        // Show/hide calendar container
        if (this.elements.container) {
            this.elements.container.classList.remove('hidden');
        }
    }

    /**
     * Update schedules data
     */
    updateSchedules(schedules) {
        this.state.schedules.clear();

        schedules.forEach(schedule => {
            this.state.schedules.set(schedule.id, schedule);
        });

        // Re-render schedules if calendar is visible
        if (this.elements.container && !this.elements.container.classList.contains('hidden')) {
            this.renderSchedules();
        }
    }

    /**
     * Utility methods
     */
    parseTime(timeString) {
        const [hours, minutes] = timeString.split(':').map(Number);
        const date = new Date();
        date.setHours(hours, minutes, 0, 0);
        return date.getTime();
    }

    formatTime(date) {
        return date.toTimeString().slice(0, 5);
    }

    formatTimeDisplay(date) {
        return date.toLocaleTimeString('it-IT', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    formatDayName(date) {
        return date.toLocaleDateString('it-IT', { weekday: 'short' });
    }

    getWeekStart(date) {
        const d = new Date(date);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Adjust for Monday start
        return new Date(d.setDate(diff));
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.manager?.options.debug) {
            console.log('[CalendarManager]', ...args);
        }
    }

    /**
     * Show calendar
     */
    show() {
        if (this.elements.container) {
            this.elements.container.classList.remove('hidden');
            this.render();
        }
    }

    /**
     * Hide calendar
     */
    hide() {
        if (this.elements.container) {
            this.elements.container.classList.add('hidden');
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        // Clear event listeners
        this.eventListeners.clear();

        // Clear references
        this.elements = {};
        this.state.schedules.clear();

        this.log('CalendarManager destroyed');
    }
}