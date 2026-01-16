/**
 * ADMIN.JS - JavaScript panelu admina (ENHANCED)
  
 */

(function() {
    'use strict';
    
    console.log('🚀 Admin JS initializing...');
    
    // ============================================
    // USER MENU DROPDOWN
    // ============================================
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuBtn && userDropdown) {
        console.log('✅ User menu elements found');
        
        userMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle dropdown
            const isShowing = userDropdown.classList.contains('show');
            userDropdown.classList.toggle('show');
            
            console.log('👤 User menu toggled:', !isShowing ? 'OPEN' : 'CLOSED');
        });
        
        // Zamknij po kliknięciu poza menu
        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
        
        // Zapobiegaj zamknięciu gdy klikasz w dropdown
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
    } else {
        console.warn('⚠️ User menu elements not found!', {
            btn: !!userMenuBtn,
            dropdown: !!userDropdown
        });
    }
    
    // ============================================
    // NOTIFICATIONS (bonus)
    // ============================================
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationCount = document.getElementById('notificationCount');
    
    if (notificationsBtn) {
        console.log('✅ Notifications button found');
        
        notificationsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔔 Notifications clicked');
            // Tutaj możesz dodać dropdown z powiadomieniami
        });
    }
    
    // ============================================
    // MOBILE MENU TOGGLE
    // ============================================
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (menuToggle && sidebar) {
        console.log('✅ Mobile menu elements found');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            console.log('📱 Mobile menu toggled');
        });
        
        // Zamknij sidebar po kliknięciu w link (mobile)
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('show');
                }
            });
        });
    }
    
    // ============================================
    // AUTO-HIDE ALERTS
    // ============================================
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        console.log('✅ Found', alerts.length, 'alerts');
        
        alerts.forEach(function(alert) {
            // Dodaj przycisk zamknięcia
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = 'position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 24px; cursor: pointer; opacity: 0.5; line-height: 1;';
            closeBtn.addEventListener('click', function() {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
            alert.style.position = 'relative';
            alert.appendChild(closeBtn);
            
            // Auto-hide po 5 sekundach
            setTimeout(function() {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    }
    
    // ============================================
    // TOOLTIPS
    // ============================================
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(function(element) {
        const tooltipText = element.getAttribute('data-tooltip');
        element.setAttribute('title', tooltipText);
    });
    
    // ============================================
    // CONFIRM DIALOGS
    // ============================================
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    confirmLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const message = link.getAttribute('data-confirm') || 'Czy na pewno?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // ============================================
    // FORM VALIDATION HELPERS
    // ============================================
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            let isValid = true;
            
            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'var(--danger)';
                    setTimeout(function() {
                        input.style.borderColor = '';
                    }, 3000);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Wypełnij wszystkie wymagane pola');
            }
        });
    });
    
    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K = otwórz wyszukiwanie (jeśli istnieje)
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // ESC = zamknij dropdown/modal
        if (e.key === 'Escape') {
            document.querySelectorAll('.show').forEach(function(el) {
                el.classList.remove('show');
            });
        }
    });
    
    // ============================================
    // PERFORMANCE: Lazy load images
    // ============================================
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }
    
    // ============================================
    // FINALIZE
    // ============================================
    console.log('✅ Admin panel JavaScript loaded successfully!');
    console.log('📊 Stats:', {
        alerts: alerts.length,
        forms: forms.length,
        tooltips: tooltipElements.length,
        confirmLinks: confirmLinks.length
    });
    
})();

// ============================================
// UTILITY FUNCTIONS (global scope)
// ============================================

/**
 * Show notification (if you add notification system)
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + type;
    notification.textContent = message;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.style.opacity = '0';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Confirm action with custom message
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Format number with thousands separator
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Skopiowano do schowka!', 'success');
        });
    } else {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showNotification('Skopiowano!', 'success');
    }
}