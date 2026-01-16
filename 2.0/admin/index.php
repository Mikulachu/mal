<?php
/**
 * INDEX.PHP - Dashboard panelu admina
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

// Wymagaj zalogowania
requireLogin();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// ============================================
// STATYSTYKI
// ============================================

// Nowe leady (ostatnie 7 dni)
$stmt = $conn->query("
    SELECT COUNT(*) as count 
    FROM leads 
    WHERE status = 'new' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$newLeadsCount = $stmt->fetch_assoc()['count'];

// Wszystkie leady w tym miesiącu
$stmt = $conn->query("
    SELECT COUNT(*) as count 
    FROM leads 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");
$monthLeadsCount = $stmt->fetch_assoc()['count'];

// Wyliczenia z kalkulatora (ostatnie 7 dni)
$stmt = $conn->query("
    SELECT COUNT(*) as count 
    FROM calculations 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$calculationsCount = $stmt->fetch_assoc()['count'];

// Nowe konsultacje
$stmt = $conn->query("
    SELECT COUNT(*) as count 
    FROM consultations 
    WHERE status = 'new'
");
$newConsultationsCount = $stmt->fetch_assoc()['count'];

// ============================================
// OSTATNIE LEADY
// ============================================

$recentLeads = $conn->query("
    SELECT 
        id,
        name,
        email,
        phone,
        service_type,
        status,
        created_at
    FROM leads
    ORDER BY created_at DESC
    LIMIT 5
");

// ============================================
// POPULARNE USŁUGI Z KALKULATORA
// ============================================

$popularServices = $conn->query("
    SELECT 
        service_name,
        COUNT(*) as count
    FROM calculation_items
    GROUP BY service_name
    ORDER BY count DESC
    LIMIT 5
");

?>
<?php include 'includes/admin-header.php'; ?>

<!-- STATYSTYKI -->
<div class="content-header">
    <h1>Dashboard</h1>
    <p style="color: var(--text-secondary); margin-top: 8px;">
        Witaj z powrotem, <strong><?php echo htmlspecialchars(getAdminData()['name']); ?></strong>! 
        Oto podsumowanie ostatniej aktywności.
    </p>
</div>

<div class="stats-grid">
    <!-- Nowe leady -->
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $newLeadsCount; ?></div>
                <div class="stat-card-label">Nowe zapytania</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
            Ostatnie 7 dni
        </div>
    </div>
    
    <!-- Leady w tym miesiącu -->
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $monthLeadsCount; ?></div>
                <div class="stat-card-label">Zapytań w miesiącu</div>
            </div>
            <div class="stat-card-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
            <?php echo date('F Y'); ?>
        </div>
    </div>
    
    <!-- Wyliczenia -->
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $calculationsCount; ?></div>
                <div class="stat-card-label">Wyliczeń kalkulatora</div>
            </div>
            <div class="stat-card-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                    <line x1="8" y1="6" x2="16" y2="6"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                    <line x1="8" y1="14" x2="12" y2="14"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
            Ostatnie 7 dni
        </div>
    </div>
    
    <!-- Konsultacje -->
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $newConsultationsCount; ?></div>
                <div class="stat-card-label">Nowe konsultacje</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
            Do zaplanowania
        </div>
    </div>
</div>

<!-- CONTENT GRID -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 30px;">
    
    <!-- OSTATNIE ZAPYTANIA -->
    <div class="content-card">
        <div class="card-header">
            <h2>Ostatnie zapytania</h2>
            <a href="leads.php" class="btn-link">Zobacz wszystkie →</a>
        </div>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Klient</th>
                        <th>Usługa</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentLeads->num_rows > 0): ?>
                        <?php while ($lead = $recentLeads->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-size: 13px;">
                                    <?php echo date('d.m.Y', strtotime($lead['created_at'])); ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    <?php echo date('H:i', strtotime($lead['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($lead['name']); ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($lead['phone'] ?? $lead['email']); ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $serviceLabels = [
                                    'elewacja' => 'Elewacja',
                                    'wnetrze' => 'Wnętrze',
                                    'remont' => 'Remont',
                                    'konsultacja' => 'Konsultacja',
                                    'inne' => 'Inne'
                                ];
                                echo $serviceLabels[$lead['service_type']] ?? 'Inne';
                                ?>
                            </td>
                            <td>
                                <?php 
                                $statusClass = [
                                    'new' => 'badge-new',
                                    'contacted' => 'badge-info',
                                    'quoted' => 'badge-warning',
                                    'won' => 'badge-success',
                                    'lost' => 'badge-danger'
                                ];
                                $statusLabel = [
                                    'new' => 'Nowy',
                                    'contacted' => 'Kontakt',
                                    'quoted' => 'Wycena',
                                    'won' => 'Wygrana',
                                    'lost' => 'Przegrana'
                                ];
                                ?>
                                <span class="badge <?php echo $statusClass[$lead['status']] ?? 'badge-default'; ?>">
                                    <?php echo $statusLabel[$lead['status']] ?? 'Nieznany'; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="lead-detail.php?id=<?php echo $lead['id']; ?>" class="btn-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 18l6-6-6-6"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                Brak zapytań
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- POPULARNE USŁUGI -->
    <div class="content-card">
        <div class="card-header">
            <h2>Popularne usługi</h2>
        </div>
        
        <div style="padding: 20px;">
            <?php if ($popularServices->num_rows > 0): ?>
                <?php while ($service = $popularServices->fetch_assoc()): ?>
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 600;">
                            <?php echo htmlspecialchars($service['service_name']); ?>
                        </span>
                        <span style="font-size: 13px; color: var(--text-secondary);">
                            <?php echo $service['count']; ?>×
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, $service['count'] * 10); ?>%;"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 20px 0;">
                    Brak danych
                </p>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- SZYBKIE AKCJE -->
<div style="margin-top: 30px;">
    <h2 style="margin-bottom: 20px;">Szybkie akcje</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        
        <a href="leads.php?status=new" class="quick-action-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <line x1="20" y1="8" x2="20" y2="14"/>
            </svg>
            <div>
                <strong>Nowe zapytania</strong>
                <p>Przejrzyj i odpowiedz</p>
            </div>
        </a>
        
        <a href="realizations.php" class="quick-action-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
            <div>
                <strong>Dodaj realizację</strong>
                <p>Pokaż nowy projekt</p>
            </div>
        </a>
        
        <a href="pricing.php" class="quick-action-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <div>
                <strong>Zaktualizuj cennik</strong>
                <p>Zmień stawki usług</p>
            </div>
        </a>
        
        <a href="stats.php" class="quick-action-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <div>
                <strong>Statystyki</strong>
                <p>Zobacz raporty</p>
            </div>
        </a>
        
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
