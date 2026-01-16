/**
 * NOTIFICATIONS.JS - Obsługa powiadomień w topbarze
 */

(function() {
    'use strict';
    
    console.log('🔔 Inicjalizacja powiadomień...');
    
    const notificationBtn = document.getElementById('notificationsBtn');
    const notificationCount = document.getElementById('notificationCount');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    // ============================================
    // POBIERZ POWIADOMIENIA
    // ============================================
    
    async function fetchNotifications() {
        try {
            const response = await fetch('/admin/get-notifications.php');
            const data = await response.json();
            
            if (data.success) {
                updateNotificationBadge(data.total);
                createNotificationDropdown(data.notifications);
                
                console.log('✅ Powiadomienia:', data.total);
            }
        } catch (error) {
            console.error('❌ Błąd pobierania powiadomień:', error);
        }
    }
    
    // ============================================
    // AKTUALIZUJ BADGE
    // ============================================
    
    function updateNotificationBadge(count) {
        if (count > 0) {
            notificationCount.textContent = count > 99 ? '99+' : count;
            notificationCount.style.display = 'block';
        } else {
            notificationCount.style.display = 'none';
        }
    }
    
    // ============================================
    // DROPDOWN POWIADOMIEŃ
    // ============================================
    
    function createNotificationDropdown(notifications) {
        // Usuń stary dropdown jeśli istnieje
        const oldDropdown = document.getElementById('notificationDropdown');
        if (oldDropdown) {
            oldDropdown.remove();
        }
        
        // Utwórz nowy
        const dropdown = document.createElement('div');
        dropdown.id = 'notificationDropdown';
        dropdown.className = 'notification-dropdown';
        dropdown.style.display = 'none';
        
        if (notifications.length === 0) {
            dropdown.innerHTML = `
                <div class="notification-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.3; margin-bottom: 12px;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <p>Brak powiadomień</p>
                </div>
            `;
        } else {
            let html = '<div class="notification-header">Powiadomienia</div>';
            
            notifications.forEach(notif => {
                const urgentClass = notif.urgent ? 'notification-item--urgent' : '';
                html += `
                    <a href="${notif.link}" class="notification-item ${urgentClass}">
                        <div class="notification-icon">${notif.icon}</div>
                        <div class="notification-content">
                            <div class="notification-message">${notif.message}</div>
                        </div>
                    </a>
                `;
            });
            
            html += `
                <div class="notification-footer">
                    <a href="/admin/consultations.php">Zobacz wszystkie konsultacje</a>
                    <a href="/admin/leads.php">Zobacz wszystkie zapytania</a>
                </div>
            `;
            
            dropdown.innerHTML = html;
        }
        
        notificationBtn.parentElement.appendChild(dropdown);
    }
    
    // ============================================
    // TOGGLE DROPDOWN
    // ============================================
    
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        
        if (dropdown) {
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';
            
            // Zamknij user dropdown
            if (userDropdown) {
                userDropdown.style.display = 'none';
            }
        }
    });
    
    // ============================================
    // USER MENU TOGGLE
    // ============================================
    
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = userDropdown.style.display === 'block';
            userDropdown.style.display = isVisible ? 'none' : 'block';
            
            // Zamknij notification dropdown
            const notifDropdown = document.getElementById('notificationDropdown');
            if (notifDropdown) {
                notifDropdown.style.display = 'none';
            }
        });
    }
    
    // ============================================
    // ZAMKNIJ PO KLIKNIĘCIU POZA
    // ============================================
    
    document.addEventListener('click', function() {
        const notifDropdown = document.getElementById('notificationDropdown');
        if (notifDropdown) {
            notifDropdown.style.display = 'none';
        }
        if (userDropdown) {
            userDropdown.style.display = 'none';
        }
    });
    
    // ============================================
    // AUTO-REFRESH
    // ============================================
    
    // Pobierz na starcie
    fetchNotifications();
    
    // Odświeżaj co 30 sekund
    setInterval(fetchNotifications, 30000);
    
    console.log('✅ Powiadomienia zainicjalizowane');
    
})();
