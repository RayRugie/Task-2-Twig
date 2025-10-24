/**
 * Ticket Management System - Main JavaScript File
 * 
 * Handles client-side functionality including form validation,
 * localStorage management, and interactive features.
 */

// Global application object
window.TicketManager = {
    // Configuration
    config: {
        apiBaseUrl: '/api',
        localStorageKeys: {
            dashboardFilters: 'dashboardFilters',
            ticketFilters: 'ticketFilters',
            userPreferences: 'userPreferences'
        },
        debounceDelay: 300
    },
    
    // Initialize the application
    init: function() {
        this.setupEventListeners();
        this.loadUserPreferences();
        this.setupFormValidation();
        this.setupTooltips();
        this.setupAutoSave();
    },
    
    // Setup global event listeners
    setupEventListeners: function() {
        // Auto-dismiss alerts after 5 seconds
        this.autoDismissAlerts();
        
        // Setup form auto-save
        this.setupFormAutoSave();
        
        // Setup keyboard shortcuts
        this.setupKeyboardShortcuts();
        
        // Setup infinite scroll for tables
        this.setupInfiniteScroll();
    },
    
    // Auto-dismiss alerts
    autoDismissAlerts: function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });
    },
    
    // Setup form auto-save functionality
    setupFormAutoSave: function() {
        const forms = document.querySelectorAll('form[data-auto-save]');
        forms.forEach(form => {
            const formId = form.id || 'form_' + Math.random().toString(36).substr(2, 9);
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('input', this.debounce(() => {
                    this.saveFormData(formId, form);
                }, this.config.debounceDelay));
            });
            
            // Load saved data on page load
            this.loadFormData(formId, form);
        });
    },
    
    // Save form data to localStorage
    saveFormData: function(formId, form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        localStorage.setItem(`form_${formId}`, JSON.stringify(data));
    },
    
    // Load form data from localStorage
    loadFormData: function(formId, form) {
        const savedData = localStorage.getItem(`form_${formId}`);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = data[key];
                    }
                });
            } catch (e) {
                console.warn('Failed to load saved form data:', e);
            }
        }
    },
    
    // Clear saved form data
    clearFormData: function(formId) {
        localStorage.removeItem(`form_${formId}`);
    },
    
    // Setup keyboard shortcuts
    setupKeyboardShortcuts: function() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K - Focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Ctrl/Cmd + N - New ticket
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const newTicketBtn = document.querySelector('a[href="/tickets/create"]');
                if (newTicketBtn) {
                    window.location.href = newTicketBtn.href;
                }
            }
            
            // Escape - Close modals/alerts
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                });
                
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }
        });
    },
    
    // Setup infinite scroll for tables
    setupInfiniteScroll: function() {
        const tables = document.querySelectorAll('.table-infinite');
        tables.forEach(table => {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            
            let loading = false;
            let page = 2; // Start from page 2
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !loading) {
                        loadMoreData();
                    }
                });
            });
            
            // Observe the last row
            const lastRow = tbody.querySelector('tr:last-child');
            if (lastRow) {
                observer.observe(lastRow);
            }
            
            function loadMoreData() {
                loading = true;
                // Implementation would depend on your API
                console.log('Loading more data...');
            }
        });
    },
    
    // Setup form validation
    setupFormValidation: function() {
        const forms = document.querySelectorAll('form[novalidate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    },
    
    // Validate form
    validateForm: function(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Validate individual input
    validateInput: function(input) {
        const value = input.value.trim();
        const type = input.type;
        const required = input.hasAttribute('required');
        
        // Clear previous validation
        input.classList.remove('is-valid', 'is-invalid');
        
        // Required field validation
        if (required && !value) {
            this.showInputError(input, 'This field is required');
            return false;
        }
        
        // Type-specific validation
        if (value) {
            switch (type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        this.showInputError(input, 'Please enter a valid email address');
                        return false;
                    }
                    break;
                    
                case 'password':
                    if (input.name === 'password' || input.name === 'new_password') {
                        const errors = this.validatePassword(value);
                        if (errors.length > 0) {
                            this.showInputError(input, errors[0]);
                            return false;
                        }
                    }
                    break;
                    
                case 'text':
                    if (input.name === 'username') {
                        if (value.length < 3) {
                            this.showInputError(input, 'Username must be at least 3 characters');
                            return false;
                        }
                    }
                    break;
            }
        }
        
        // Custom validation
        const customValidation = input.dataset.validation;
        if (customValidation && value) {
            if (!this.runCustomValidation(customValidation, value, input)) {
                return false;
            }
        }
        
        this.showInputSuccess(input);
        return true;
    },
    
    // Show input error
    showInputError: function(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        
        let errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            input.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    },
    
    // Show input success
    showInputSuccess: function(input) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
    },
    
    // Validate email
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Validate password strength
    validatePassword: function(password) {
        const errors = [];
        
        if (password.length < 8) {
            errors.push('Password must be at least 8 characters long');
        }
        
        if (!/(?=.*[a-z])/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }
        
        if (!/(?=.*[A-Z])/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }
        
        if (!/(?=.*\d)/.test(password)) {
            errors.push('Password must contain at least one number');
        }
        
        return errors;
    },
    
    // Run custom validation
    runCustomValidation: function(validation, value, input) {
        switch (validation) {
            case 'password-match':
                const confirmPassword = document.querySelector('input[name="confirm_password"]');
                if (confirmPassword && value !== confirmPassword.value) {
                    this.showInputError(input, 'Passwords do not match');
                    return false;
                }
                break;
                
            case 'username-available':
                // This would typically make an AJAX call
                return true;
                
            default:
                return true;
        }
        return true;
    },
    
    // Setup tooltips
    setupTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },
    
    // Load user preferences from localStorage
    loadUserPreferences: function() {
        const preferences = localStorage.getItem(this.config.localStorageKeys.userPreferences);
        if (preferences) {
            try {
                const prefs = JSON.parse(preferences);
                this.applyUserPreferences(prefs);
            } catch (e) {
                console.warn('Failed to load user preferences:', e);
            }
        }
    },
    
    // Apply user preferences
    applyUserPreferences: function(preferences) {
        // Apply theme
        if (preferences.theme) {
            document.body.setAttribute('data-theme', preferences.theme);
        }
        
        // Apply table preferences
        if (preferences.tablePageSize) {
            const pageSizeSelect = document.querySelector('select[name="limit"]');
            if (pageSizeSelect) {
                pageSizeSelect.value = preferences.tablePageSize;
            }
        }
    },
    
    // Save user preferences
    saveUserPreferences: function(preferences) {
        const currentPrefs = JSON.parse(localStorage.getItem(this.config.localStorageKeys.userPreferences) || '{}');
        const newPrefs = { ...currentPrefs, ...preferences };
        localStorage.setItem(this.config.localStorageKeys.userPreferences, JSON.stringify(newPrefs));
    },
    
    // Setup auto-save functionality
    setupAutoSave: function() {
        // Auto-save dashboard filters
        const dashboardFilters = document.querySelectorAll('select[name="status"], select[name="priority"], select[name="category"]');
        dashboardFilters.forEach(filter => {
            filter.addEventListener('change', () => {
                this.saveDashboardFilters();
            });
        });
        
        // Auto-save ticket filters
        const ticketFilters = document.querySelectorAll('select[name="status"], select[name="priority"], select[name="category"], input[name="search"]');
        ticketFilters.forEach(filter => {
            filter.addEventListener('change', () => {
                this.saveTicketFilters();
            });
        });
    },
    
    // Save dashboard filters
    saveDashboardFilters: function() {
        const filters = {
            status: document.querySelector('select[name="status"]')?.value || '',
            priority: document.querySelector('select[name="priority"]')?.value || '',
            category: document.querySelector('select[name="category"]')?.value || '',
            lastUpdated: new Date().toISOString()
        };
        
        localStorage.setItem(this.config.localStorageKeys.dashboardFilters, JSON.stringify(filters));
    },
    
    // Save ticket filters
    saveTicketFilters: function() {
        const filters = {
            status: document.querySelector('select[name="status"]')?.value || '',
            priority: document.querySelector('select[name="priority"]')?.value || '',
            category: document.querySelector('select[name="category"]')?.value || '',
            search: document.querySelector('input[name="search"]')?.value || '',
            limit: document.querySelector('select[name="limit"]')?.value || '10',
            lastUpdated: new Date().toISOString()
        };
        
        localStorage.setItem(this.config.localStorageKeys.ticketFilters, JSON.stringify(filters));
    },
    
    // Utility function for debouncing
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Utility function for throttling
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Show loading state
    showLoading: function(element) {
        if (element) {
            element.classList.add('loading');
            const originalText = element.textContent;
            element.dataset.originalText = originalText;
            element.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Loading...';
        }
    },
    
    // Hide loading state
    hideLoading: function(element) {
        if (element) {
            element.classList.remove('loading');
            if (element.dataset.originalText) {
                element.textContent = element.dataset.originalText;
                delete element.dataset.originalText;
            }
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info', duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remove after duration
        setTimeout(() => {
            if (alertDiv.parentNode) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, duration);
    },
    
    // Format date for display
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
};

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    TicketManager.init();
});

// Export for use in other scripts
window.TicketManager = TicketManager;
