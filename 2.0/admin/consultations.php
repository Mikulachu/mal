<?php
/**
 * CONSULTATIONS.PHP - Zarządzanie konsultacjami online
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Konsultacje';
$currentPage = 'consultations';
$admin = getAdminData();

// ============================================
// FILTRY
// ============================================

$filterStatus = $_GET['status'] ?? '';
$filterDate = $_GET['date'] ?? '';

$where = [];
if ($filterStatus) {
    $where[] = "status = '{$filterStatus}'";
}

if ($filterDate) {
    switch ($filterDate) {
        case 'today':
            $where[] = "DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'upcoming':
            $where[] = "preferred_date >= CURDATE() AND status = 'scheduled'";
            break;
    }
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ============================================
// POBIERZ KONSULTACJE
// ============================================

$consultations = $pdo->query("
    SELECT * FROM consultations
    {$whereClause}
    ORDER BY 
        CASE status 
            WHEN 'new' THEN 1 
            WHEN 'scheduled' THEN 2 
            ELSE 3 
        END,
        created_at DESC
");

// ============================================
// STATYSTYKI
// ============================================

$stats = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count
    FROM consultations
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

$statusCounts = [];
foreach ($stats as $stat) {
    $statusCounts[$stat['status']] = $stat['count'];
}

?>
<?php include 'includes/admin-header.php'; ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Konsultacje online</h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">
                Zarządzaj zgłoszeniami na konsultacje
            </p>
        </div>
    </div>
</div>

<!-- STATYSTYKI SZYBKIE -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <a href="?status=" class="status-filter-card <?php echo $filterStatus === '' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo array_sum($statusCounts); ?></div>
        <div class="status-label">Wszystkie</div>
    </a>
    <a href="?status=new" class="status-filter-card <?php echo $filterStatus === 'new' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo $statusCounts['new'] ?? 0; ?></div>
        <div class="status-label">Nowe</div>
    </a>
    <a href="?status=scheduled" class="status-filter-card <?php echo $filterStatus === 'scheduled' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo $statusCounts['scheduled'] ?? 0; ?></div>
        <div class="status-label">Zaplanowane</div>
    </a>
    <a href="?status=completed" class="status-filter-card <?php echo $filterStatus === 'completed' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo $statusCounts['completed'] ?? 0; ?></div>
        <div class="status-label">Zakończone</div>
    </a>
</div>

<!-- FILTRY -->
<div class="content-card" style="margin-bottom: 24px;">
    <div style="padding: 20px;">
        <form method="GET" class="filters-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                
                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Wszystkie</option>
                        <option value="new" <?php echo $filterStatus === 'new' ? 'selected' : ''; ?>>Nowe</option>
                        <option value="scheduled" <?php echo $filterStatus === 'scheduled' ? 'selected' : ''; ?>>Zaplanowane</option>
                        <option value="completed" <?php echo $filterStatus === 'completed' ? 'selected' : ''; ?>>Zakończone</option>
                        <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Anulowane</option>
                    </select>
                </div>
                
                <!-- Okres -->
                <div class="form-group">
                    <label>Okres</label>
                    <select name="date" class="form-control">
                        <option value="">Wszystkie</option>
                        <option value="today" <?php echo $filterDate === 'today' ? 'selected' : ''; ?>>Dzisiaj</option>
                        <option value="week" <?php echo $filterDate === 'week' ? 'selected' : ''; ?>>Ostatnie 7 dni</option>
                        <option value="upcoming" <?php echo $filterDate === 'upcoming' ? 'selected' : ''; ?>>Nadchodzące</option>
                    </select>
                </div>
                
                <!-- Przyciski -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    <a href="consultations.php" class="btn btn-secondary">Wyczyść</a>
                </div>
                
            </div>
        </form>
    </div>
</div>

<!-- TABELA KONSULTACJI -->
<div class="content-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Klient</th>
                    <th>Kontakt</th>
                    <th>Temat</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($consultations->rowCount() > 0): ?>
                    <?php while ($consult = $consultations->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="consultation-row consultation-row--clickable" 
                        onclick="window.location.href='consultation-detail.php?id=<?php echo $consult['id']; ?>'">
                        <td>
                            <div style="font-size: 13px;">
                                <?php echo date('d.m.Y', strtotime($consult['created_at'])); ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary);">
                                <?php echo date('H:i', strtotime($consult['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($consult['name']); ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 13px;">
                                <?php if ($consult['phone']): ?>
                                <a href="tel:<?php echo htmlspecialchars($consult['phone']); ?>" 
                                   style="color: var(--text-primary); text-decoration: none;" 
                                   onclick="event.stopPropagation();">
                                    📞 <?php echo htmlspecialchars($consult['phone']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                <?php if ($consult['email']): ?>
                                <a href="mailto:<?php echo htmlspecialchars($consult['email']); ?>" 
                                   style="color: var(--text-secondary);" 
                                   onclick="event.stopPropagation();">
                                    <?php echo htmlspecialchars($consult['email']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 14px;">
                                <?php echo htmlspecialchars(mb_substr($consult['topic'], 0, 60)); ?>
                                <?php if (strlen($consult['topic']) > 60): ?>...<?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $statusClass = [
                                'new' => 'badge-new',
                                'scheduled' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger'
                            ];
                            $statusLabel = [
                                'new' => 'Nowa',
                                'scheduled' => 'Zaplanowana',
                                'completed' => 'Zakończona',
                                'cancelled' => 'Anulowana'
                            ];
                            ?>
                            <span class="badge <?php echo $statusClass[$consult['status']] ?? 'badge-default'; ?>">
                                <?php echo $statusLabel[$consult['status']] ?? $consult['status']; ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="consultation-detail.php?id=<?php echo $consult['id']; ?>" 
                               class="btn-icon" 
                               title="Zobacz szczegóły" 
                               onclick="event.stopPropagation();">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 60px; color: var(--text-secondary);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px; opacity: 0.3;">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Brak konsultacji</div>
                            <div style="font-size: 14px;">Zmień filtry lub poczekaj na nowe zgłoszenia</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.status-filter-card {
    background: white;
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
}
.status-filter-card:hover {
    border-color: var(--secondary);
    transform: translateY(-2px);
}
.status-filter-card.active {
    border-color: var(--secondary);
    background: rgba(230, 126, 34, 0.05);
}
.status-count {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.status-label {
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
    font-weight: 600;
}
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--text-primary);
}
.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s;
}
.form-control:focus {
    outline: none;
    border-color: var(--secondary);
}

/* KLIKALNE WIERSZE */
.consultation-row--clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}
.consultation-row--clickable:hover {
    background-color: rgba(230, 126, 34, 0.05);
}
</style>

<?php include 'includes/admin-footer.php'; ?>