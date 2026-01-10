/**
 * MAIN.JS - Główne interakcje aplikacji
  
 */

(function() {
    'use strict';
    
    // ============================================
    // MOBILE MENU
    // ============================================
    
    const burgerBtn = document.getElementById('burgerBtn');
    const mobileNav = document.getElementById('mobileNav');
    const header = document.getElementById('header');
    
    if (burgerBtn && mobileNav) {
        burgerBtn.addEventListener('click', function() {
            burgerBtn.classList.toggle('active');
            mobileNav.classList.toggle('active');
            document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
        });
        
        // Zamknij menu po kliknięciu w link
        const mobileLinks = mobileNav.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                burgerBtn.classList.remove('active');
                mobileNav.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Zamknij menu po kliknięciu poza nim
        mobileNav.addEventListener('click', function(e) {
            if (e.target === mobileNav) {
                burgerBtn.classList.remove('active');
                mobileNav.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // ============================================
    // SCROLL EFFECTS
    // ============================================
    
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        // Dodaj cień do headera po scrollu
        if (currentScroll > 0) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });
    
    // ============================================
    // SMOOTH SCROLL
    // ============================================
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Ignoruj puste anchory
            if (href === '#' || href === '#!') {
                e.preventDefault();
                return;
            }
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ============================================
    // NEWSLETTER FORM
    // ============================================
    
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[name="email"]');
            const email = emailInput.value.trim();
            
            // Prosta walidacja
            if (!isValidEmail(email)) {
                showNotification('Podaj prawidłowy adres e-mail', 'error');
                return;
            }
            
            // Wyślij dane
            const formData = new FormData();
            formData.append('email', email);
            formData.append('action', 'newsletter_subscribe');
            
            // Disable button
            const btn = this.querySelector('button');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Zapisuję...';
            
            fetch('/api/newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Dziękujemy! Zapisałeś się do newslettera.', 'success');
                    emailInput.value = '';
                } else {
                    showNotification(data.message || 'Coś poszło nie tak. Spróbuj ponownie.', 'error');
                }
            })
            .catch(error => {
                console.error('Newsletter error:', error);
                showNotification('Wystąpił błąd. Spróbuj ponownie później.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }
    
    // ============================================
    // FORM VALIDATION HELPERS
    // ============================================
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function isValidPhone(phone) {
        const re = /^(\+48)?[0-9]{9}$/;
        return re.test(phone.replace(/\s/g, ''));
    }
    
    // Real-time validation dla formularzy
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.classList.add('error');
                showFieldError(this, 'Podaj prawidłowy adres e-mail');
            } else {
                this.classList.remove('error');
                removeFieldError(this);
            }
        });
    });
    
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !isValidPhone(this.value)) {
                this.classList.add('error');
                showFieldError(this, 'Podaj prawidłowy numer telefonu');
            } else {
                this.classList.remove('error');
                removeFieldError(this);
            }
        });
    });
    
    function showFieldError(field, message) {
        removeFieldError(field);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function removeFieldError(field) {
        const existingError = field.parentNode.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    // ============================================
    // NOTIFICATIONS
    // ============================================
    
    function showNotification(message, type = 'info') {
        // Usuń istniejące notyfikacje
        const existing = document.querySelector('.notification');
        if (existing) {
            existing.remove();
        }
        
        // Utwórz nową notyfikację
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <div class="notification__content">
                <p>${message}</p>
                <button class="notification__close" aria-label="Zamknij">&times;</button>
            </div>
        `;
        
        // Dodaj style dla notyfikacji (jeśli nie ma w CSS)
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 10000;
            background: ${type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : '#3498db'};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        `;
        
        document.body.appendChild(notification);
        
        // Zamknij po kliknięciu
        const closeBtn = notification.querySelector('.notification__close');
        closeBtn.addEventListener('click', () => notification.remove());
        
        // Auto-zamknij po 5 sekundach
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    // Dodaj animacje CSS dla notyfikacji
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
            
            .notification__content {
                display: flex;
                align-items: center;
                gap: 16px;
            }
            
            .notification__content p {
                margin: 0;
                flex: 1;
            }
            
            .notification__close {
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 20px;
                line-height: 1;
                transition: background 0.2s;
            }
            
            .notification__close:hover {
                background: rgba(255,255,255,0.3);
            }
        `;
        document.head.appendChild(style);
    }
    
    // ============================================
    // LAZY LOADING IMAGES
    // ============================================
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // ============================================
    // SCROLL ANIMATIONS
    // ============================================
    
    if ('IntersectionObserver' in window) {
        const animateObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    animateObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            animateObserver.observe(el);
        });
    }
    
    // ============================================
    // UTILS - Eksportuj dla innych skryptów
    // ============================================
    
    window.AppUtils = {
        isValidEmail: isValidEmail,
        isValidPhone: isValidPhone,
        showNotification: showNotification,
        showFieldError: showFieldError,
        removeFieldError: removeFieldError
    };
    
    // ============================================
    // INIT
    // ============================================
    
    console.log('App initialized successfully');
    
})();