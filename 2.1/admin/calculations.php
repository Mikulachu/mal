<?php
/**
 * CALCULATIONS.PHP - Historia wyliczeń z kalkulatora
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Kalkulator - Historia';
$currentPage = 'calculations';

// ============================================
// FILTRY
// ============================================

$filterDate = $_GET['date'] ?? '';
$filterMinValue = $_GET['min_value'] ?? '';
$filterMaxValue = $_GET['max_value'] ?? '';

// ============================================
// BUDOWANIE ZAPYTANIA
// ============================================

$where = [];
$params = [];
$types = '';

if ($filterDate) {
    switch ($filterDate) {
        case 'today':
            $where[] = "DATE(c.created_at) = CURDATE()";
            break;
        case 'week':
            $where[] = "c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where[] = "MONTH(c.created_at) = MONTH(CURRENT_DATE()) AND YEAR(c.created_at) = YEAR(CURRENT_DATE())";
            break;
    }
}

if ($filterMinValue) {
    $where[] = "c.total_value >= ?";
    $params[] = $filterMinValue;
    $types .= 'd';
}

if ($filterMaxValue) {
    $where[] = "c.total_value <= ?";
    $params[] = $filterMaxValue;
    $types .= 'd';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ============================================
// POBIERZ WYLICZENIA
// ============================================

$sql = "
    SELECT 
        c.id,
        c.fingerprint,
        c.total_value,
        c.standard_type,
        c.has_email,
        c.created_at,
        COUNT(ci.id) as items_count
    FROM calculations c
    LEFT JOIN calculation_items ci ON c.id = ci.calculation_id
    {$whereClause}
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 100
";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $calculations = $stmt->get_result();
} else {
    $calculations = $conn->query($sql);
}

// ============================================
// STATYSTYKI
// ============================================

// Łączna liczba wyliczeń
$totalCalcs = $conn->query("SELECT COUNT(*) as count FROM calculations")->fetch_assoc()['count'];

// Średnia wartość wyliczenia
$avgValue = $conn->query("SELECT AVG(total_value) as avg FROM calculations WHERE total_value > 0")->fetch_assoc()['avg'];

// Wyliczenia z emailem
$withEmail = $conn->query("SELECT COUNT(*) as count FROM calculations WHERE has_email = 1")->fetch_assoc()['count'];

// Najpopularniejsze usługi
$popularServices = $conn->query("
    SELECT 
        service_name,
        service_category,
        COUNT(*) as count,
        AVG(quantity) as avg_quantity
    FROM calculation_items
    GROUP BY service_name, service_category
    ORDER BY count DESC
    LIMIT 10
");

?>
<?php include 'includes/admin-header.php'; ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Historia kalkulatora</h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">
                Przegląd wyliczeń i najczęściej wybieranych usług
            </p>
        </div>
    </div>
</div>

<!-- STATYSTYKI -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $totalCalcs; ?></div>
                <div class="stat-card-label">Wyliczeń ogółem</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                    <line x1="8" y1="6" x2="16" y2="6"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo number_format($avgValue, 0, ',', ' '); ?> zł</div>
                <div class="stat-card-label">Średnia wartość</div>
            </div>
            <div class="stat-card-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $withEmail; ?></div>
                <div class="stat-card-label">Z emailem</div>
            </div>
            <div class="stat-card-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
            <?php echo $totalCalcs > 0 ? round(($withEmail / $totalCalcs) * 100) : 0; ?>% konwersja
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value">
                    <?php 
                    $weekCalcs = $conn->query("SELECT COUNT(*) as count FROM calculations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
                    echo $weekCalcs;
                    ?>
                </div>
                <div class="stat-card-label">Ostatnie 7 dni</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    
    <!-- LEWA KOLUMNA: LISTA WYLICZEŃ -->
    <div>
        
        <!-- FILTRY -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div style="padding: 20px;">
                <form method="GET" class="filters-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                        
                        <!-- Okres -->
                        <div class="form-group">
                            <label>Okres</label>
                            <select name="date" class="form-control">
                                <option value="">Wszystkie</option>
                                <option value="today" <?php echo $filterDate === 'today' ? 'selected' : ''; ?>>Dzisiaj</option>
                                <option value="week" <?php echo $filterDate === 'week' ? 'selected' : ''; ?>>Ostatnie 7 dni</option>
                                <option value="month" <?php echo $filterDate === 'month' ? 'selected' : ''; ?>>Ten miesiąc</option>
                            </select>
                        </div>
                        
                        <!-- Min wartość -->
                        <div class="form-group">
                            <label>Wartość min (zł)</label>
                            <input type="number" 
                                   name="min_value" 
                                   class="form-control"
                                   placeholder="0"
                                   value="<?php echo htmlspecialchars($filterMinValue); ?>">
                        </div>
                        
                        <!-- Max wartość -->
                        <div class="form-group">
                            <label>Wartość max (zł)</label>
                            <input type="number" 
                                   name="max_value" 
                                   class="form-control"
                                   placeholder="100000"
                                   value="<?php echo htmlspecialchars($filterMaxValue); ?>">
                        </div>
                        
                        <!-- Przyciski -->
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn btn-primary">Filtruj</button>
                            <a href="calculations.php" class="btn btn-secondary">Wyczyść</a>
                        </div>
                        
                    </div>
                </form>
            </div>
        </div>
        
        <!-- LISTA WYLICZEŃ -->
        <div class="content-card">
            <div class="card-header">
                <h2>Ostatnie wyliczenia (<?php echo $calculations->num_rows; ?>)</h2>
            </div>
            
            <div class="calculations-list">
                <?php if ($calculations->num_rows > 0): ?>
                    <?php while ($calc = $calculations->fetch_assoc()): ?>
                    <div class="calculation-item">
                        <div class="calc-header">
                            <div class="calc-date">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <?php echo date('d.m.Y H:i', strtotime($calc['created_at'])); ?>
                            </div>
                            <div class="calc-value">
                                <?php echo number_format($calc['total_value'], 2, ',', ' '); ?> zł
                            </div>
                        </div>
                        
                        <div class="calc-details">
                            <div class="calc-meta">
                                <span class="calc-badge">
                                    <?php echo $calc['standard_type'] === 'premium' ? '⭐ Premium' : '📦 Standard'; ?>
                                </span>
                                <span class="calc-badge">
                                    <?php echo $calc['items_count']; ?> usług
                                </span>
                                <?php if ($calc['has_email']): ?>
                                <span class="calc-badge calc-badge-success">
                                    ✉️ Email
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <button class="btn-expand" 
                                    onclick="toggleCalculation(<?php echo $calc['id']; ?>)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                                Szczegóły
                            </button>
                        </div>
                        
                        <!-- SZCZEGÓŁY (ukryte domyślnie) -->
                        <div class="calc-items" id="calc-<?php echo $calc['id']; ?>" style="display: none;">
                            <?php
                            $items = $conn->query("
                                SELECT * FROM calculation_items 
                                WHERE calculation_id = {$calc['id']}
                                ORDER BY service_category, service_name
                            ");
                            
                            if ($items->num_rows > 0):
                            ?>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Usługa</th>
                                        <th>Ilość</th>
                                        <th>Cena jedn.</th>
                                        <th>Wartość</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($item['service_name']); ?></div>
                                            <div style="font-size: 11px; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($item['service_category']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?> m²</td>
                                        <td><?php echo number_format($item['unit_price'], 2, ',', ' '); ?> zł</td>
                                        <td style="font-weight: 600;">
                                            <?php echo number_format($item['item_total'], 2, ',', ' '); ?> zł
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px; opacity: 0.3;">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                            <line x1="8" y1="6" x2="16" y2="6"/>
                            <line x1="8" y1="10" x2="16" y2="10"/>
                        </svg>
                        <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Brak wyliczeń</div>
                        <div style="font-size: 14px;">Zmień filtry lub poczekaj na nowe wyliczenia</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <!-- PRAWA KOLUMNA: POPULARNE USŁUGI -->
    <div>
        <div class="content-card">
            <div class="card-header">
                <h2>Popularne usługi</h2>
            </div>
            
            <div style="padding: 20px;">
                <?php if ($popularServices->num_rows > 0): ?>
                    <?php 
                    $maxCount = $popularServices->fetch_assoc();
                    $popularServices->data_seek(0); // Reset pointer
                    $maxValue = $maxCount['count'];
                    ?>
                    <?php while ($service = $popularServices->fetch_assoc()): ?>
                    <div style="margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <div>
                                <div style="font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($service['service_category']); ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 13px; font-weight: 700; color: var(--secondary);">
                                    <?php echo $service['count']; ?>×
                                </div>
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    ~<?php echo round($service['avg_quantity']); ?> m²
                                </div>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($service['count'] / $maxValue) * 100; ?>%;"></div>
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
        
        <!-- INFO -->
        <div class="content-card" style="margin-top: 24px;">
            <div style="padding: 20px;">
                <h3 style="font-size: 14px; margin-bottom: 12px;">💡 Wskazówka</h3>
                <p style="font-size: 13px; line-height: 1.6; color: var(--text-secondary);">
                    Wyliczenia z emailem to potencjalni klienci. Skontaktuj się z nimi, oferując bezpłatną wycenę lub konsultację.
                </p>
            </div>
        </div>
    </div>
    
</div>

<script>
function toggleCalculation(id) {
    const element = document.getElementById('calc-' + id);
    if (element.style.display === 'none') {
        element.style.display = 'block';
    } else {
        element.style.display = 'none';
    }
}
</script>

<style>
.calculations-list {
    max-height: 800px;
    overflow-y: auto;
}
.calculation-item {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    transition: background 0.3s;
}
.calculation-item:hover {
    background: rgba(0, 0, 0, 0.02);
}
.calculation-item:last-child {
    border-bottom: none;
}
.calc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.calc-date {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-secondary);
}
.calc-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--secondary);
}
.calc-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.calc-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.calc-badge {
    display: inline-block;
    padding: 4px 10px;
    background: var(--bg-body);
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}
.calc-badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}
.btn-expand {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: none;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-expand:hover {
    background: var(--bg-body);
    border-color: var(--secondary);
}
.calc-items {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}
.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.items-table th {
    padding: 8px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-secondary);
    background: var(--bg-body);
}
.items-table td {
    padding: 10px 8px;
    border-bottom: 1px solid var(--border);
}
.items-table tbody tr:last-child td {
    border-bottom: none;
}
</style>

<?php include 'includes/admin-footer.php'; ?>
