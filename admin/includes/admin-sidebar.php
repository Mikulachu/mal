<?php
/**
 * ADMIN-SIDEBAR.PHP - Menu boczne panelu admina (UPDATED)
  
 */

$currentPage = $currentPage ?? '';

// Pobierz liczbę nowych leadów
$newLeadsCount = 0;
$newConsultationsCount = 0;

try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM leads WHERE status = 'new'");
    $newLeadsCount = $stmt->fetch_assoc()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM consultations WHERE status = 'new'");
    $newConsultationsCount = $stmt->fetch_assoc()['count'];
} catch (Exception $e) {
    // Ignoruj błędy
}
?>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span class="logo-text">Premium<br>Elewacje</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="/admin/index.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Separator -->
            <li class="nav-separator">
                <span>Klienci</span>
            </li>
            
            <!-- Leady -->
            <li class="nav-item">
                <a href="/admin/leads.php" class="nav-link <?php echo $currentPage === 'leads' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    <span>Zapytania</span>
                    <?php if ($newLeadsCount > 0): ?>
                    <span class="nav-badge"><?php echo $newLeadsCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Konsultacje -->
            <li class="nav-item">
                <a href="/admin/consultations.php" class="nav-link <?php echo $currentPage === 'consultations' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Konsultacje</span>
                    <?php if ($newConsultationsCount > 0): ?>
                    <span class="nav-badge"><?php echo $newConsultationsCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Separator -->
            <li class="nav-separator">
                <span>Treść strony</span>
            </li>
            
            <!-- Realizacje -->
            <li class="nav-item">
                <a href="/admin/realizations.php" class="nav-link <?php echo $currentPage === 'realizations' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Realizacje</span>
                </a>
            </li>
            
            <!-- FAQ -->
            <li class="nav-item">
                <a href="/admin/faq.php" class="nav-link <?php echo $currentPage === 'faq' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span>FAQ</span>
                </a>
            </li>
            
            <!-- Separator -->
            <li class="nav-separator">
                <span>Ustawienia</span>
            </li>
            
            <!-- Cennik i Kalkulator (POŁĄCZONE) -->
            <li class="nav-item">
                <a href="/admin/pricing.php" class="nav-link <?php echo $currentPage === 'pricing' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span>Cennik i Kalkulator</span>
                </a>
            </li>
            
            <!-- Statystyki -->
            <li class="nav-item">
                <a href="/admin/stats.php" class="nav-link <?php echo $currentPage === 'stats' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span>Statystyki</span>
                </a>
            </li>
            
            <!-- Ustawienia -->
            <li class="nav-item">
                <a href="/admin/settings.php" class="nav-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6m4.22-13A10 10 0 0 1 22 12"/>
                    </svg>
                    <span>Ustawienia</span>
                </a>
            </li>
            
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar-small">
                <?php echo strtoupper(substr($admin['name'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="user-info">
                <div class="user-name-small"><?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></div>
                <a href="/admin/logout.php" class="user-logout">Wyloguj</a>
            </div>
        </div>
    </div>
</aside>