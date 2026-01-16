<?php
/**
 * LEADS.PHP - Lista zapytań od klientów (PDO)
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Zapytania';
$currentPage = 'leads';

// ============================================
// FILTRY
// ============================================

$filterStatus = $_GET['status'] ?? '';
$filterService = $_GET['service'] ?? '';
$filterSearch = $_GET['search'] ?? '';
$filterDate = $_GET['date'] ?? '';
$filterSource = $_GET['source'] ?? '';

// BUDOWANIE ZAPYTANIA
$where = [];
$params = [];

if ($filterStatus) {
    $where[] = "status = ?";
    $params[] = $filterStatus;
}

if ($filterService) {
    $where[] = "service_type = ?";
    $params[] = $filterService;
}

if ($filterSource) {
    $where[] = "source = ?";
    $params[] = $filterSource;
}

if ($filterSearch) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%{$filterSearch}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($filterDate) {
    switch ($filterDate) {
        case 'today':
            $where[] = "DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where[] = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            break;
    }
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ============================================
// POBIERANIE LEADÓW
// ============================================

$sql = "
    SELECT 
        id,
        name,
        email,
        phone,
        service_type,
        message,
        status,
        priority,
        source,
        created_at
    FROM leads
    {$whereClause}
    ORDER BY 
        CASE status 
            WHEN 'new' THEN 1 
            WHEN 'contacted' THEN 2 
            ELSE 3 
        END,
        created_at DESC
";

if ($params) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $leads = $stmt;
} else {
    $leads = $pdo->query($sql);
}

// ============================================
// STATYSTYKI
// ============================================

$stats = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count
    FROM leads
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
            <h1>Zapytania</h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">
                Zarządzaj zapytaniami od klientów (pytania z formularza kontaktowego)
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
    <a href="?status=contacted" class="status-filter-card <?php echo $filterStatus === 'contacted' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo $statusCounts['contacted'] ?? 0; ?></div>
        <div class="status-label">Kontakt</div>
    </a>
    <a href="?status=quoted" class="status-filter-card <?php echo $filterStatus === 'quoted' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo $statusCounts['quoted'] ?? 0; ?></div>
        <div class="status-label">Wycena</div>
    </a>
    <a href="?status=won" class="status-filter-card <?php echo $filterStatus === 'won' ? 'active' : ''; ?>">
        <div class="status-count"><?php echo $statusCounts['won'] ?? 0; ?></div>
        <div class="status-label">Wygrane</div>
    </a>
</div>

<!-- FILTRY -->
<div class="content-card" style="margin-bottom: 24px;">
    <div style="padding: 20px;">
        <form method="GET" class="filters-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                
                <!-- Wyszukiwanie -->
                <div class="form-group">
                    <label>Szukaj</label>
                    <input type="text" 
                           name="search" 
                           placeholder="Imię, email, telefon..." 
                           value="<?php echo htmlspecialchars($filterSearch); ?>"
                           class="form-control">
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Wszystkie</option>
                        <option value="new" <?php echo $filterStatus === 'new' ? 'selected' : ''; ?>>Nowe</option>
                        <option value="contacted" <?php echo $filterStatus === 'contacted' ? 'selected' : ''; ?>>Kontakt</option>
                        <option value="quoted" <?php echo $filterStatus === 'quoted' ? 'selected' : ''; ?>>Wycena</option>
                        <option value="won" <?php echo $filterStatus === 'won' ? 'selected' : ''; ?>>Wygrane</option>
                        <option value="lost" <?php echo $filterStatus === 'lost' ? 'selected' : ''; ?>>Przegrane</option>
                    </select>
                </div>
                
                <!-- Źródło -->
                <div class="form-group">
                    <label>Źródło</label>
                    <select name="source" class="form-control">
                        <option value="">Wszystkie</option>
                        <option value="website" <?php echo $filterSource === 'website' ? 'selected' : ''; ?>>Formularz kontaktowy</option>
                        <option value="calculator" <?php echo $filterSource === 'calculator' ? 'selected' : ''; ?>>Kalkulator</option>
                    </select>
                </div>
                
                <!-- Data -->
                <div class="form-group">
                    <label>Okres</label>
                    <select name="date" class="form-control">
                        <option value="">Wszystkie</option>
                        <option value="today" <?php echo $filterDate === 'today' ? 'selected' : ''; ?>>Dzisiaj</option>
                        <option value="week" <?php echo $filterDate === 'week' ? 'selected' : ''; ?>>Ostatnie 7 dni</option>
                        <option value="month" <?php echo $filterDate === 'month' ? 'selected' : ''; ?>>Ten miesiąc</option>
                    </select>
                </div>
                
                <!-- Przyciski -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    <a href="leads.php" class="btn btn-secondary">Wyczyść</a>
                </div>
                
            </div>
        </form>
    </div>
</div>

<!-- TABELA LEADÓW -->
<div class="content-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Klient</th>
                    <th>Kontakt</th>
                    <th>Pytanie (fragment)</th>
                    <th>Status</th>
                    <th>Źródło</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leads->rowCount() > 0): ?>
                    <?php while ($lead = $leads->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="lead-row lead-row--clickable" 
                        data-priority="<?php echo $lead['priority'] ?? 'medium'; ?>" 
                        onclick="window.location.href='lead-detail.php?id=<?php echo $lead['id']; ?>'">
                        <td>
                            <div style="font-size: 13px;">
                                <?php echo date('d.m.Y', strtotime($lead['created_at'])); ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary);">
                                <?php echo date('H:i', strtotime($lead['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($lead['name']); ?>
                            </div>
                            <?php if (isset($lead['priority']) && $lead['priority'] === 'high'): ?>
                            <span class="badge badge-danger" style="font-size: 10px; margin-top: 4px;">Pilne</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size: 13px;">
                                <?php if ($lead['phone']): ?>
                                <a href="tel:<?php echo htmlspecialchars($lead['phone']); ?>" 
                                   style="color: var(--text-primary); text-decoration: none;" 
                                   onclick="event.stopPropagation();">
                                    📞 <?php echo htmlspecialchars($lead['phone']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                <?php if ($lead['email']): ?>
                                <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>" 
                                   style="color: var(--text-secondary);" 
                                   onclick="event.stopPropagation();">
                                    <?php echo htmlspecialchars($lead['email']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 14px;">
                                <?php 
                                $message = $lead['message'] ?? '';
                                echo htmlspecialchars(mb_substr($message, 0, 60)); 
                                ?>
                                <?php if (strlen($message) > 60): ?>...<?php endif; ?>
                            </div>
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
                                <?php echo $statusLabel[$lead['status']] ?? $lead['status']; ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--text-secondary);">
                                <?php 
                                $sources = [
                                    'website' => '📝 Formularz',
                                    'calculator' => '🧮 Kalkulator'
                                ];
                                echo $sources[$lead['source'] ?? 'website'] ?? 'Strona';
                                ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="lead-detail.php?id=<?php echo $lead['id']; ?>" 
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
                        <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-secondary);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px; opacity: 0.3;">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Brak zapytań</div>
                            <div style="font-size: 14px;">Zmień filtry lub poczekaj na nowe zapytania</div>
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
.lead-row[data-priority="high"] {
    background: rgba(231, 76, 60, 0.03);
    border-left: 3px solid var(--danger);
}

/* KLIKALNE WIERSZE */
.lead-row--clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}
.lead-row--clickable:hover {
    background-color: rgba(230, 126, 34, 0.05);
}
</style>

<?php include 'includes/admin-footer.php'; ?>