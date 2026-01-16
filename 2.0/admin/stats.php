<?php
/**
 * STATS.PHP - Statystyki odwiedzin strony (SAFE VERSION)
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Statystyki';
$currentPage = 'stats';
$admin = getAdminData();

// ============================================
// SPRAWDŹ CZY TABELA page_views ISTNIEJE
// ============================================

$pageViewsExists = false;
try {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'page_views'");
    $pageViewsExists = ($tableCheck && $tableCheck->num_rows > 0);
} catch (Exception $e) {
    $pageViewsExists = false;
}

// ============================================
// FILTRY
// ============================================

$filterPeriod = $_GET['period'] ?? '7days';

$dateFilter = '';
switch ($filterPeriod) {
    case 'today':
        $dateFilter = "DATE(created_at) = CURDATE()";
        break;
    case '7days':
        $dateFilter = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case '30days':
        $dateFilter = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    case 'all':
        $dateFilter = "1=1";
        break;
}

// ============================================
// INICJALIZACJA ZMIENNYCH
// ============================================

$totalViews = 0;
$uniqueUsers = 0;
$newLeads = 0;
$consultations = 0;
$calculations = 0;
$conversionRate = 0;
$topPages = [];
$trafficSources = [];
$devices = [];
$leadsBySource = [];
$chartData = [];

// ============================================
// STATYSTYKI OGÓLNE (z obsługą błędów)
// ============================================

try {
    // Odwiedziny (jeśli tabela istnieje)
    if ($pageViewsExists) {
        $result = $conn->query("SELECT COUNT(*) as count FROM page_views WHERE {$dateFilter}");
        if ($result) {
            $totalViews = $result->fetch_assoc()['count'];
        }
        
        // Unikalni użytkownicy
        $result = $conn->query("SELECT COUNT(DISTINCT user_fingerprint) as count FROM page_views WHERE {$dateFilter}");
        if ($result) {
            $uniqueUsers = $result->fetch_assoc()['count'];
        }
    }
    
    // Leady
    $result = $conn->query("SELECT COUNT(*) as count FROM leads WHERE {$dateFilter}");
    if ($result) {
        $newLeads = $result->fetch_assoc()['count'];
    }
    
    // Konsultacje
    $result = $conn->query("SELECT COUNT(*) as count FROM consultations WHERE {$dateFilter}");
    if ($result) {
        $consultations = $result->fetch_assoc()['count'];
    }
    
    // Wyliczenia (jeśli tabela istnieje)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'calculations'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM calculations WHERE {$dateFilter}");
        if ($result) {
            $calculations = $result->fetch_assoc()['count'];
        }
    }
    
    // Konwersja
    $conversionRate = $totalViews > 0 ? round(($newLeads / $totalViews) * 100, 2) : 0;
    
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
}

// ============================================
// TOP STRONY
// ============================================

if ($pageViewsExists) {
    try {
        $result = $conn->query("
            SELECT 
                page_url,
                COUNT(*) as views,
                COUNT(DISTINCT user_fingerprint) as unique_views
            FROM page_views
            WHERE {$dateFilter}
            GROUP BY page_url
            ORDER BY views DESC
            LIMIT 10
        ");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $topPages[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Top pages error: " . $e->getMessage());
    }
}

// ============================================
// ŹRÓDŁA RUCHU
// ============================================

if ($pageViewsExists) {
    try {
        $result = $conn->query("
            SELECT 
                CASE 
                    WHEN referrer_url = '' OR referrer_url IS NULL THEN 'Bezpośrednie wejście'
                    WHEN referrer_url LIKE '%google%' THEN 'Google'
                    WHEN referrer_url LIKE '%facebook%' THEN 'Facebook'
                    WHEN referrer_url LIKE '%instagram%' THEN 'Instagram'
                    ELSE 'Inne źródła'
                END as source,
                COUNT(*) as count
            FROM page_views
            WHERE {$dateFilter}
            GROUP BY source
            ORDER BY count DESC
        ");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $trafficSources[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Traffic sources error: " . $e->getMessage());
    }
}

// ============================================
// URZĄDZENIA
// ============================================

if ($pageViewsExists) {
    try {
        $result = $conn->query("
            SELECT 
                CASE 
                    WHEN user_agent LIKE '%Mobile%' OR user_agent LIKE '%Android%' THEN 'Mobile'
                    WHEN user_agent LIKE '%Tablet%' OR user_agent LIKE '%iPad%' THEN 'Tablet'
                    ELSE 'Desktop'
                END as device_type,
                COUNT(*) as count
            FROM page_views
            WHERE {$dateFilter}
            GROUP BY device_type
            ORDER BY count DESC
        ");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $devices[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Devices error: " . $e->getMessage());
    }
}

// ============================================
// LEADY WEDŁUG ŹRÓDŁA
// ============================================

try {
    $result = $conn->query("
        SELECT 
            source,
            COUNT(*) as count
        FROM leads
        WHERE {$dateFilter}
        GROUP BY source
        ORDER BY count DESC
    ");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $leadsBySource[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Leads by source error: " . $e->getMessage());
}

// ============================================
// WYKRES DZIENNY (ostatnie 7 dni)
// ============================================

if ($pageViewsExists) {
    try {
        $result = $conn->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as views,
                COUNT(DISTINCT user_fingerprint) as unique_users
            FROM page_views
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $chartData[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Chart data error: " . $e->getMessage());
    }
}

// Header
include 'includes/admin-header.php';
?>

<div class="content-header">
    <h1><?php echo $pageTitle; ?></h1>
    <p>Analityka ruchu i konwersji na stronie</p>
</div>

<!-- FILTRY OKRESU -->
<div style="margin-bottom: 24px;">
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="?period=today" class="btn <?php echo $filterPeriod === 'today' ? 'btn-primary' : 'btn-secondary'; ?>">
            Dzisiaj
        </a>
        <a href="?period=7days" class="btn <?php echo $filterPeriod === '7days' ? 'btn-primary' : 'btn-secondary'; ?>">
            7 dni
        </a>
        <a href="?period=30days" class="btn <?php echo $filterPeriod === '30days' ? 'btn-primary' : 'btn-secondary'; ?>">
            30 dni
        </a>
        <a href="?period=all" class="btn <?php echo $filterPeriod === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
            Wszystko
        </a>
    </div>
</div>

<?php if (!$pageViewsExists): ?>
<!-- ALERT: Brak tabeli page_views -->
<div class="alert alert-warning" style="margin-bottom: 24px;">
    <strong>⚠️ Uwaga:</strong> Tabela <code>page_views</code> nie istnieje w bazie danych. 
    Statystyki odwiedzin nie są dostępne. Wyświetlane są tylko dane z zapytań i konsultacji.
    <br><br>
    <details style="margin-top: 12px;">
        <summary style="cursor: pointer; font-weight: 600;">📋 Utwórz tabelę page_views (kliknij aby rozwinąć)</summary>
        <pre style="background: #f5f5f5; padding: 12px; border-radius: 6px; margin-top: 12px; overflow-x: auto; font-size: 12px;">
CREATE TABLE page_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_url VARCHAR(500) NOT NULL,
    referrer_url VARCHAR(500),
    user_agent TEXT,
    user_fingerprint VARCHAR(64),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_fingerprint (user_fingerprint),
    INDEX idx_page_url (page_url(255))
);
        </pre>
    </details>
</div>
<?php endif; ?>

<!-- STATYSTYKI GŁÓWNE -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <?php if ($pageViewsExists): ?>
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo number_format($totalViews, 0, ',', ' '); ?></div>
                <div class="stat-card-label">Odwiedziny</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo number_format($uniqueUsers, 0, ',', ' '); ?></div>
                <div class="stat-card-label">Unikalni użytkownicy</div>
            </div>
            <div class="stat-card-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $newLeads; ?></div>
                <div class="stat-card-label">Nowe zapytania</div>
            </div>
            <div class="stat-card-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
            </div>
        </div>
    </div>
    
    <?php if ($pageViewsExists): ?>
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $conversionRate; ?>%</div>
                <div class="stat-card-label">Konwersja</div>
            </div>
            <div class="stat-card-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $consultations; ?></div>
                <div class="stat-card-label">Konsultacje</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $calculations; ?></div>
                <div class="stat-card-label">Wyliczenia</div>
            </div>
            <div class="stat-card-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                    <line x1="8" y1="6" x2="16" y2="6"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                    <line x1="8" y1="14" x2="12" y2="14"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    
    <!-- LEWA KOLUMNA -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <?php if ($pageViewsExists && !empty($chartData)): ?>
        <!-- WYKRES DZIENNY -->
        <div class="content-card">
            <div class="card-header">
                <h2>📈 Wykres odwiedzin (ostatnie 7 dni)</h2>
            </div>
            <div class="card-body">
                <canvas id="dailyChart" height="200"></canvas>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($pageViewsExists && !empty($topPages)): ?>
        <!-- TOP STRONY -->
        <div class="content-card">
            <div class="card-header">
                <h2>🏆 Najpopularniejsze strony (top 10)</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Adres URL</th>
                            <th style="text-align: center;">Odwiedziny</th>
                            <th style="text-align: center;">Unikalne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topPages as $page): ?>
                        <tr>
                            <td>
                                <code style="font-size: 12px; background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">
                                    <?php echo htmlspecialchars($page['page_url']); ?>
                                </code>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                <?php echo number_format($page['views']); ?>
                            </td>
                            <td style="text-align: center;">
                                <?php echo number_format($page['unique_views']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- PRAWA KOLUMNA -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <?php if ($pageViewsExists && !empty($trafficSources)): ?>
        <!-- ŹRÓDŁA RUCHU -->
        <div class="content-card">
            <div class="card-header">
                <h2>🌐 Źródła ruchu</h2>
            </div>
            <div class="card-body">
                <?php 
                $totalTraffic = array_sum(array_column($trafficSources, 'count'));
                foreach ($trafficSources as $source): 
                    $percentage = $totalTraffic > 0 ? round(($source['count'] / $totalTraffic) * 100, 1) : 0;
                ?>
                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($source['source']); ?></span>
                        <span style="color: var(--text-secondary); font-size: 14px;"><?php echo $percentage; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($pageViewsExists && !empty($devices)): ?>
        <!-- URZĄDZENIA -->
        <div class="content-card">
            <div class="card-header">
                <h2>📱 Urządzenia</h2>
            </div>
            <div class="card-body">
                <?php 
                $totalDevices = array_sum(array_column($devices, 'count'));
                foreach ($devices as $device): 
                    $percentage = $totalDevices > 0 ? round(($device['count'] / $totalDevices) * 100, 1) : 0;
                ?>
                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($device['device_type']); ?></span>
                        <span style="color: var(--text-secondary); font-size: 14px;"><?php echo $percentage; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($leadsBySource)): ?>
        <!-- LEADY WEDŁUG ŹRÓDŁA -->
        <div class="content-card">
            <div class="card-header">
                <h2>📊 Zapytania według źródła</h2>
            </div>
            <div class="card-body">
                <?php 
                $totalLeadsSources = array_sum(array_column($leadsBySource, 'count'));
                foreach ($leadsBySource as $leadSource): 
                    $percentage = $totalLeadsSources > 0 ? round(($leadSource['count'] / $totalLeadsSources) * 100, 1) : 0;
                    $sourceLabel = $leadSource['source'] ?: 'Inne';
                ?>
                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($sourceLabel); ?></span>
                        <span style="color: var(--text-secondary); font-size: 14px;"><?php echo $percentage; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
</div>

<?php if ($pageViewsExists && !empty($chartData)): ?>
<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('dailyChart');
const chartData = <?php echo json_encode($chartData); ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' });
        }),
        datasets: [
            {
                label: 'Odwiedziny',
                data: chartData.map(d => d.views),
                backgroundColor: 'rgba(52, 152, 219, 0.5)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2
            },
            {
                label: 'Unikalni',
                data: chartData.map(d => d.unique_users),
                backgroundColor: 'rgba(39, 174, 96, 0.5)',
                borderColor: 'rgba(39, 174, 96, 1)',
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<style>
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/admin-footer.php'; ?>