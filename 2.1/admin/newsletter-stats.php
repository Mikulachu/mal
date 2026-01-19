<?php
/**
 * ADMIN/NEWSLETTER-STATS.PHP - Statystyki kampanii
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

$pageTitle = 'Statystyki kampanii';
$currentPage = 'newsletter';

// Pobierz ID kampanii
$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$campaignId) {
    $_SESSION['error_message'] = 'Nieprawidłowe ID kampanii';
    header('Location: /admin/newsletter.php');
    exit;
}

// Pobierz kampanię
try {
    $stmt = $pdo->prepare("SELECT * FROM newsletter_campaigns WHERE id = ?");
    $stmt->execute([$campaignId]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$campaign) {
        $_SESSION['error_message'] = 'Kampania nie została znaleziona';
        header('Location: /admin/newsletter.php');
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Błąd pobierania kampanii';
    header('Location: /admin/newsletter.php');
    exit;
}

// Oblicz statystyki
$recipientsCount = $campaign['recipients_count'] ?: 0;
$sentCount = $campaign['sent_count'] ?: 0;
$openedCount = $campaign['opened_count'] ?: 0;
$clickedCount = $campaign['clicked_count'] ?: 0;

$openRate = $sentCount > 0 ? round(($openedCount / $sentCount) * 100, 1) : 0;
$clickRate = $sentCount > 0 ? round(($clickedCount / $sentCount) * 100, 1) : 0;
$clickToOpenRate = $openedCount > 0 ? round(($clickedCount / $openedCount) * 100, 1) : 0;

// Pobierz szczegółowe logi (jeśli istnieje tabela newsletter_sends)
$detailedLogs = [];
try {
    $stmt = $pdo->prepare("
        SELECT email, status, sent_at, opened_at, clicked_at, error_message
        FROM newsletter_sends
        WHERE campaign_id = ?
        ORDER BY sent_at DESC
        LIMIT 100
    ");
    $stmt->execute([$campaignId]);
    $detailedLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Tabela nie istnieje lub błąd
}

include __DIR__ . '/includes/admin-header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.stats-container {
    max-width: 1200px;
    margin: 0 auto;
}

.campaign-header {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}

.campaign-header h2 {
    margin: 0 0 8px 0;
    font-size: 20px;
    color: #111827;
}

.campaign-header p {
    margin: 0;
    color: #6B7280;
    font-size: 14px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 20px;
}

.stat-card__value {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px 0;
}

.stat-card__label {
    font-size: 13px;
    color: #6B7280;
    margin: 0 0 12px 0;
}

.stat-card__progress {
    width: 100%;
    height: 8px;
    background: #E5E7EB;
    border-radius: 4px;
    overflow: hidden;
}

.stat-card__progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2B59A6 0%, #3B82F6 100%);
    transition: width 0.3s;
}

.stat-card--sent .stat-card__progress-fill { background: linear-gradient(90deg, #10B981 0%, #34D399 100%); }
.stat-card--opened .stat-card__progress-fill { background: linear-gradient(90deg, #F59E0B 0%, #FBBF24 100%); }
.stat-card--clicked .stat-card__progress-fill { background: linear-gradient(90deg, #3B82F6 0%, #60A5FA 100%); }

.details-section {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}

.details-section h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #F3F4F6;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row__label {
    color: #6B7280;
    font-size: 14px;
}

.detail-row__value {
    color: #111827;
    font-weight: 600;
    font-size: 14px;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th {
    background: #F9FAFB;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    color: #6B7280;
    border-bottom: 1px solid #E5E7EB;
}

.logs-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 13px;
}

.logs-table tbody tr:hover {
    background: #F9FAFB;
}

.status-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
}

.status-indicator--sent { background: #10B981; }
.status-indicator--opened { background: #F59E0B; }
.status-indicator--clicked { background: #3B82F6; }
.status-indicator--failed { background: #EF4444; }

.empty-logs {
    text-align: center;
    padding: 60px 20px;
    color: #9CA3AF;
}

.empty-logs i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}
</style>

<div class="admin-content">
    <div class="stats-container">
        <div class="page-header">
            <h1><i class="bi bi-bar-chart"></i> Statystyki kampanii</h1>
            <a href="/admin/newsletter.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Powrót
            </a>
        </div>
        
        <!-- Nagłówek kampanii -->
        <div class="campaign-header">
            <h2><?php echo htmlspecialchars($campaign['name']); ?></h2>
            <p><strong>Temat:</strong> <?php echo htmlspecialchars($campaign['subject']); ?></p>
            <p style="margin-top: 8px;">
                <strong>Status:</strong> 
                <?php
                $statusLabels = [
                    'draft' => 'Szkic',
                    'scheduled' => 'Zaplanowana',
                    'sending' => 'Wysyłanie...',
                    'sent' => 'Wysłana',
                    'failed' => 'Błąd'
                ];
                echo $statusLabels[$campaign['status']] ?? $campaign['status'];
                ?>
                <?php if ($campaign['sent_at']): ?>
                | <strong>Wysłano:</strong> <?php echo date('d.m.Y H:i', strtotime($campaign['sent_at'])); ?>
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Główne statystyki -->
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-card__value"><?php echo number_format($recipientsCount, 0, ',', ' '); ?></p>
                <p class="stat-card__label">Odbiorców</p>
                <div class="stat-card__progress">
                    <div class="stat-card__progress-fill" style="width: 100%;"></div>
                </div>
            </div>
            
            <div class="stat-card stat-card--sent">
                <p class="stat-card__value"><?php echo number_format($sentCount, 0, ',', ' '); ?></p>
                <p class="stat-card__label">Wysłano</p>
                <div class="stat-card__progress">
                    <div class="stat-card__progress-fill" style="width: <?php echo $recipientsCount > 0 ? ($sentCount / $recipientsCount * 100) : 0; ?>%;"></div>
                </div>
            </div>
            
            <div class="stat-card stat-card--opened">
                <p class="stat-card__value"><?php echo $openRate; ?>%</p>
                <p class="stat-card__label">Open Rate (<?php echo number_format($openedCount, 0, ',', ' '); ?>)</p>
                <div class="stat-card__progress">
                    <div class="stat-card__progress-fill" style="width: <?php echo $openRate; ?>%;"></div>
                </div>
            </div>
            
            <div class="stat-card stat-card--clicked">
                <p class="stat-card__value"><?php echo $clickRate; ?>%</p>
                <p class="stat-card__label">Click Rate (<?php echo number_format($clickedCount, 0, ',', ' '); ?>)</p>
                <div class="stat-card__progress">
                    <div class="stat-card__progress-fill" style="width: <?php echo $clickRate; ?>%;"></div>
                </div>
            </div>
        </div>
        
        <!-- Szczegóły -->
        <div class="details-section">
            <h3>Szczegółowe dane</h3>
            
            <div class="detail-row">
                <span class="detail-row__label">Click-to-Open Rate</span>
                <span class="detail-row__value"><?php echo $clickToOpenRate; ?>%</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-row__label">Nieotwarte</span>
                <span class="detail-row__value"><?php echo number_format($sentCount - $openedCount, 0, ',', ' '); ?> (<?php echo $sentCount > 0 ? round((($sentCount - $openedCount) / $sentCount) * 100, 1) : 0; ?>%)</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-row__label">Otwarte bez kliknięć</span>
                <span class="detail-row__value"><?php echo number_format($openedCount - $clickedCount, 0, ',', ' '); ?></span>
            </div>
            
            <?php if ($campaign['scheduled_at']): ?>
            <div class="detail-row">
                <span class="detail-row__label">Zaplanowana data</span>
                <span class="detail-row__value"><?php echo date('d.m.Y H:i', strtotime($campaign['scheduled_at'])); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="detail-row">
                <span class="detail-row__label">Utworzono</span>
                <span class="detail-row__value"><?php echo date('d.m.Y H:i', strtotime($campaign['created_at'])); ?></span>
            </div>
        </div>
        
        <!-- Szczegółowe logi (jeśli istnieją) -->
        <?php if (!empty($detailedLogs)): ?>
        <div class="details-section">
            <h3>Historia wysyłek (ostatnie 100)</h3>
            
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Wysłano</th>
                        <th>Otwarto</th>
                        <th>Kliknięto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailedLogs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['email']); ?></td>
                        <td>
                            <?php if ($log['clicked_at']): ?>
                                <span class="status-indicator status-indicator--clicked"></span> Kliknięto
                            <?php elseif ($log['opened_at']): ?>
                                <span class="status-indicator status-indicator--opened"></span> Otwarto
                            <?php elseif ($log['status'] === 'sent'): ?>
                                <span class="status-indicator status-indicator--sent"></span> Wysłano
                            <?php else: ?>
                                <span class="status-indicator status-indicator--failed"></span> Błąd
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 12px;">
                            <?php echo $log['sent_at'] ? date('d.m.Y H:i', strtotime($log['sent_at'])) : '-'; ?>
                        </td>
                        <td style="font-size: 12px;">
                            <?php echo $log['opened_at'] ? date('d.m.Y H:i', strtotime($log['opened_at'])) : '-'; ?>
                        </td>
                        <td style="font-size: 12px;">
                            <?php echo $log['clicked_at'] ? date('d.m.Y H:i', strtotime($log['clicked_at'])) : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="details-section">
            <div class="empty-logs">
                <i class="bi bi-inbox"></i>
                <p>Brak szczegółowych logów wysyłki</p>
                <small style="color: #6B7280;">Logi są dostępne tylko dla kampanii wysłanych przez system</small>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
