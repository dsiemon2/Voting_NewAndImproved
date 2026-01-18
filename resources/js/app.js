import './bootstrap';

// Session management utilities
const SessionManager = {
    setEventId(eventId) {
        sessionStorage.setItem('eventId', eventId);
    },

    getEventId() {
        return sessionStorage.getItem('eventId');
    },

    hasEventId() {
        return sessionStorage.getItem('eventId') !== null;
    },

    clearEventId() {
        sessionStorage.removeItem('eventId');
    },

    setUserInfo(userInfo) {
        sessionStorage.setItem('userInfo', JSON.stringify(userInfo));
    },

    getUserInfo() {
        const info = sessionStorage.getItem('userInfo');
        return info ? JSON.parse(info) : null;
    },

    isLoggedIn() {
        return this.getUserInfo() !== null;
    },

    logout() {
        sessionStorage.clear();
    }
};

// Toast notification system
const Toast = {
    show(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    }
};

// Voting form validation
const VotingFormValidator = {
    init(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', (e) => this.validate(e, form));
    },

    validate(e, form) {
        const inputs = form.querySelectorAll('.place-input');
        let hasValue = false;
        const valuesByDivision = {};

        inputs.forEach(input => {
            if (input.value.trim()) {
                hasValue = true;
                const division = input.dataset.division || '0';

                if (!valuesByDivision[division]) {
                    valuesByDivision[division] = [];
                }

                // Check for duplicates
                if (valuesByDivision[division].includes(input.value.trim())) {
                    e.preventDefault();
                    this.showError(input, 'Duplicate selection in this division');
                    return false;
                }

                valuesByDivision[division].push(input.value.trim());
            }
        });

        if (!hasValue) {
            e.preventDefault();
            Toast.error('Please make at least one selection.');
            return false;
        }

        return true;
    },

    showError(input, message) {
        input.classList.add('border-red-500');
        Toast.error(message);
        input.focus();
    }
};

// Live results auto-refresh
const LiveResults = {
    intervalId: null,
    refreshInterval: 10000, // 10 seconds

    start(eventId) {
        this.stop(); // Clear any existing interval

        this.refresh(eventId);
        this.intervalId = setInterval(() => this.refresh(eventId), this.refreshInterval);
    },

    stop() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    },

    async refresh(eventId) {
        try {
            const response = await fetch(`/api/results/${eventId}`);
            const data = await response.json();

            // Update the results table
            this.updateResults(data);
        } catch (error) {
            console.error('Failed to refresh results:', error);
        }
    },

    updateResults(data) {
        // Implementation depends on the results page structure
        const resultsContainer = document.getElementById('results-container');
        if (resultsContainer && data.results) {
            // Update logic here
        }
    }
};

// Sidebar toggle for mobile
const Sidebar = {
    init() {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024 &&
                    !sidebar.contains(e.target) &&
                    !toggleBtn.contains(e.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    }
};

// Form utilities
const FormUtils = {
    // Auto-submit form on select change
    initAutoSubmit(selector) {
        document.querySelectorAll(selector).forEach(select => {
            select.addEventListener('change', () => {
                select.closest('form').submit();
            });
        });
    },

    // Confirm before delete
    initDeleteConfirm() {
        document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    Sidebar.init();
    FormUtils.initDeleteConfirm();

    // Initialize voting form if present
    if (document.getElementById('votingForm')) {
        VotingFormValidator.init('votingForm');
    }

    // Initialize live results if on live results page
    const liveResultsPage = document.querySelector('[data-live-results]');
    if (liveResultsPage) {
        const eventId = liveResultsPage.dataset.eventId;
        LiveResults.start(eventId);
    }
});

// Export for use in inline scripts
window.SessionManager = SessionManager;
window.Toast = Toast;
window.VotingFormValidator = VotingFormValidator;
window.LiveResults = LiveResults;
