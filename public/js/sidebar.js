/**
 * Sidebar Navigation JavaScript
 *
 * Handles sidebar functionality including:
 * - Mobile toggle
 * - Scroll position persistence
 * - Event management cookies
 */

const Sidebar = {
    /**
     * Initialize sidebar functionality
     */
    init: function() {
        this.initScrollPersistence();
        this.initManagedEventCookie();
    },

    /**
     * Toggle sidebar visibility (mobile)
     */
    toggle: function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    },

    /**
     * Initialize scroll position persistence
     * Saves scroll position on link click and restores on page load
     */
    initScrollPersistence: function() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        // Restore scroll position
        const savedScrollPos = sessionStorage.getItem('sidebarScrollPos');
        if (savedScrollPos) {
            sidebar.scrollTop = parseInt(savedScrollPos, 10);
        }

        // Save scroll position before navigating
        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
            });
        });
    },

    /**
     * Initialize managed event cookie based on window.managingEventId
     */
    initManagedEventCookie: function() {
        if (typeof window.managingEventId !== 'undefined' && window.managingEventId) {
            this.setCookie('managing_event_id', window.managingEventId, 1);
        }
    },

    /**
     * Clear managed event and redirect to events list
     */
    clearManagedEvent: function() {
        this.deleteCookie('managing_event_id');
        window.location.href = '/admin/events';
    },

    /**
     * Set a cookie
     * @param {string} name - Cookie name
     * @param {string} value - Cookie value
     * @param {number} days - Days until expiration
     */
    setCookie: function(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/';
    },

    /**
     * Get a cookie value
     * @param {string} name - Cookie name
     * @returns {string} Cookie value or empty string
     */
    getCookie: function(name) {
        return document.cookie.split('; ').reduce(function(r, v) {
            const parts = v.split('=');
            return parts[0] === name ? decodeURIComponent(parts[1]) : r;
        }, '');
    },

    /**
     * Delete a cookie
     * @param {string} name - Cookie name
     */
    deleteCookie: function(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    Sidebar.init();
});

// Global function for mobile toggle button
function toggleSidebar() {
    Sidebar.toggle();
}

// Global function for clearing managed event (used in onclick)
function clearManagedEvent() {
    Sidebar.clearManagedEvent();
}
