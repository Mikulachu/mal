<?php
/**
 * ADMIN/NEWSLETTER.PHP - Panel Newsletter
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Sprawdź autoryzację
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

$pageTitle = 'Newsletter';
$currentPage = 'newsletter';

// Obsługa wysyłki natychmiastowej
if (isset($_GET['send_now']) && is_numeric($_GET['send_now'])) {
    $campaignId = (int)$_GET['send_now'];
    
    try {
        // Pobierz kampanię - TYLKO draft
        $stmt = $pdo->prepare("SELECT * FROM newsletter_campaigns WHERE id = ? AND status = 'draft'");
        $stmt->execute([$campaignId]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($campaign) {
            // Pobierz aktywnych subskrybentów
            $stmt = $pdo->query("
                SELECT COUNT(DISTINCT email) as count
                FROM marketing_consents
                WHERE status = 'active' AND consent_marketing = 1
            ");
            $recipientsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Aktualizuj status TYLKO JEŚLI to draft
            $stmt = $pdo->prepare("
                UPDATE newsletter_campaigns 
                SET status = 'sent',
                    recipients_count = ?,
                    sent_count = ?,
                    sent_at = NOW(),
                    updated_at = NOW()
                WHERE id = ? AND status = 'draft'
            ");
            $stmt->execute([$recipientsCount, $recipientsCount, $campaignId]);
            
            $_SESSION['success_message'] = "Kampania została wysłana do {$recipientsCount} subskrybentów";
            
            // TODO: Tutaj wywołaj skrypt wysyłkowy (async/cron)
            // exec("php " . __DIR__ . "/../scripts/send-campaign.php {$campaignId} > /dev/null 2>&1 &");
            
        } else {
            $_SESSION['error_message'] = 'Ta kampania została już wysłana lub nie istnieje';
        }
        
        header('Location: /admin/newsletter.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Błąd wysyłki: ' . $e->getMessage();
        header('Location: /admin/newsletter.php');
        exit;
    }
}

// Obsługa duplikowania
if (isset($_GET['duplicate']) && is_numeric($_GET['duplicate'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM newsletter_campaigns WHERE id = ?");
        $stmt->execute([$_GET['duplicate']]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($campaign) {
            $stmt = $pdo->prepare("
                INSERT INTO newsletter_campaigns 
                (name, subject, content_html, status, created_at, created_by)
                VALUES (?, ?, ?, 'draft', NOW(), ?)
            ");
            $stmt->execute([
                $campaign['name'] . ' (kopia)',
                $campaign['subject'],
                $campaign['content_html'],
                $_SESSION['admin_id'] ?? null
            ]);
            
            $_SESSION['success_message'] = 'Kampania została zduplikowana';
        }
        
        header('Location: /admin/newsletter.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Błąd duplikowania';
    }
}

// Obsługa usuwania kampanii
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM newsletter_campaigns WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success_message'] = 'Kampania została usunięta';
        header('Location: /admin/newsletter.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Błąd usuwania kampanii';
    }
}

// Sprawdź czy tabele istnieją
function checkNewsletterTables($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'newsletter_campaigns'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

$tablesExist = checkNewsletterTables($pdo);

if ($tablesExist) {
    // Statystyki
    try {
        // Subskrybenci
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT email) as count
            FROM marketing_consents
            WHERE status = 'active' AND consent_marketing = 1
        ");
        $subscribersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        // Nowi w ostatnim miesiącu
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT email) as count
            FROM marketing_consents
            WHERE status = 'active' 
            AND consent_marketing = 1
            AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $newSubscribers = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        // Wysłane wiadomości
        $stmt = $pdo->query("SELECT COALESCE(SUM(sent_count), 0) as total FROM newsletter_campaigns WHERE status = 'sent'");
        $totalSent = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Open rate
        $stmt = $pdo->query("
            SELECT AVG((opened_count / NULLIF(sent_count, 0)) * 100) as rate
            FROM newsletter_campaigns
            WHERE status = 'sent' AND sent_count > 0
        ");
        $openRate = round($stmt->fetch(PDO::FETCH_ASSOC)['rate'] ?? 0, 1);
        
        // Click rate
        $stmt = $pdo->query("
            SELECT AVG((clicked_count / NULLIF(sent_count, 0)) * 100) as rate
            FROM newsletter_campaigns
            WHERE status = 'sent' AND sent_count > 0
        ");
        $clickRate = round($stmt->fetch(PDO::FETCH_ASSOC)['rate'] ?? 0, 1);
        
    } catch (Exception $e) {
        $subscribersCount = 0;
        $newSubscribers = 0;
        $totalSent = 0;
        $openRate = 0;
        $clickRate = 0;
    }
    
    // Kampanie
    try {
        $stmt = $pdo->query("
            SELECT *
            FROM newsletter_campaigns
            ORDER BY 
                CASE status
                    WHEN 'sending' THEN 1
                    WHEN 'scheduled' THEN 2
                    WHEN 'draft' THEN 3
                    WHEN 'sent' THEN 4
                    ELSE 5
                END,
                created_at DESC
            LIMIT 20
        ");
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $campaigns = [];
    }
} else {
    $subscribersCount = 0;
    $newSubscribers = 0;
    $totalSent = 0;
    $openRate = 0;
    $clickRate = 0;
    $campaigns = [];
}

include __DIR__ . '/includes/admin-header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #E5E7EB;
}

.stat-card__icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 12px;
}

.stat-card--subscribers .stat-card__icon { background: #E9F0FF; color: #2B59A6; }
.stat-card--sent .stat-card__icon { background: #D1FAE5; color: #10B981; }
.stat-card--opened .stat-card__icon { background: #FEF3C7; color: #F59E0B; }
.stat-card--clicked .stat-card__icon { background: #DBEAFE; color: #3B82F6; }

.stat-card__value {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px 0;
}

.stat-card__label {
    font-size: 13px;
    color: #6B7280;
    margin: 0;
}

.stat-card__change {
    font-size: 12px;
    margin-top: 8px;
    color: #10B981;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0 20px 0;
}

.section-header h2 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.btn-group {
    display: flex;
    gap: 10px;
}

.table-wrapper {
    background: white;
    border-radius: 8px;
    border: 1px solid #E5E7EB;
    overflow: hidden;
}

.table-wrapper table {
    width: 100%;
    border-collapse: collapse;
}

.table-wrapper th {
    background: #F9FAFB;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    color: #6B7280;
    border-bottom: 1px solid #E5E7EB;
}

.table-wrapper td {
    padding: 12px 16px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 14px;
}

.table-wrapper tbody tr:hover {
    background: #F9FAFB;
}

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge--draft { background: #F3F4F6; color: #6B7280; }
.status-badge--scheduled { background: #FEF3C7; color: #92400E; }
.status-badge--sending { background: #DBEAFE; color: #1E40AF; }
.status-badge--sent { background: #D1FAE5; color: #065F46; }
.status-badge--failed { background: #FEE2E2; color: #991B1B; }

.action-icons {
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
}

.action-icons a {
    color: #6B7280;
    text-decoration: none;
    font-size: 18px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 6px;
}

.action-icons a:hover {
    color: #2B59A6;
    background: #EFF6FF;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6B7280;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #374151;
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: #E5E7EB;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 4px;
}

.progress-bar__fill {
    height: 100%;
    background: #10B981;
}
</style>

<div class="admin-content">
    <div class="page-header">
        <h1><i class="bi bi-envelope"></i> Newsletter</h1>
    </div>
    
    <?php if (!$tablesExist): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Wymagana konfiguracja:</strong> 
        Tabele newslettera nie istnieją. 
        <a href="/admin/newsletter-setup.php" style="text-decoration: underline;">Kliknij tutaj aby je utworzyć</a>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i>
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-x-circle"></i>
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
    </div>
    <?php endif; ?>
    
    <!-- Statystyki -->
    <div class="stats-grid">
        <div class="stat-card stat-card--subscribers">
            <div class="stat-card__icon">
                <i class="bi bi-people"></i>
            </div>
            <p class="stat-card__value"><?php echo number_format($subscribersCount, 0, ',', ' '); ?></p>
            <p class="stat-card__label">Aktywni subskrybenci</p>
            <?php if ($newSubscribers > 0): ?>
            <p class="stat-card__change">
                <i class="bi bi-arrow-up"></i> +<?php echo $newSubscribers; ?> w ostatnim miesiącu
            </p>
            <?php endif; ?>
        </div>
        
        <div class="stat-card stat-card--sent">
            <div class="stat-card__icon">
                <i class="bi bi-send"></i>
            </div>
            <p class="stat-card__value"><?php echo number_format($totalSent, 0, ',', ' '); ?></p>
            <p class="stat-card__label">Wysłane wiadomości</p>
        </div>
        
        <div class="stat-card stat-card--opened">
            <div class="stat-card__icon">
                <i class="bi bi-envelope-open"></i>
            </div>
            <p class="stat-card__value"><?php echo $openRate; ?>%</p>
            <p class="stat-card__label">Średni Open Rate</p>
        </div>
        
        <div class="stat-card stat-card--clicked">
            <div class="stat-card__icon">
                <i class="bi bi-cursor"></i>
            </div>
            <p class="stat-card__value"><?php echo $clickRate; ?>%</p>
            <p class="stat-card__label">Średni Click Rate</p>
        </div>
    </div>
    
    <!-- Kampanie -->
    <div class="section-header">
        <h2>Kampanie</h2>
        <div class="btn-group">
            <a href="/admin/newsletter-subscribers.php" class="btn btn-secondary">
                <i class="bi bi-people"></i> Subskrybenci
            </a>
            <a href="/admin/newsletter-create.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nowa kampania
            </a>
        </div>
    </div>
    
    <div class="table-wrapper">
        <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <i class="bi bi-mailbox"></i>
            <h3>Brak kampanii</h3>
            <p>Utwórz pierwszą kampanię newsletterową</p>
            <a href="/admin/newsletter-create.php" class="btn btn-primary" style="margin-top: 16px;">
                <i class="bi bi-plus-lg"></i> Utwórz kampanię
            </a>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nazwa / Temat</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th style="text-align: center;">Odbiorcy</th>
                    <th style="text-align: center;">Wysłane</th>
                    <th style="text-align: center;">Open Rate</th>
                    <th style="text-align: center;">Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td>
                        <strong style="display: block; margin-bottom: 4px;"><?php echo htmlspecialchars($campaign['name']); ?></strong>
                        <small style="color: #6B7280;"><?php echo htmlspecialchars($campaign['subject']); ?></small>
                    </td>
                    <td>
                        <?php
                        $statusLabels = [
                            'draft' => 'Szkic',
                            'scheduled' => 'Zaplanowana',
                            'sending' => 'Wysyłanie...',
                            'sent' => 'Wysłana',
                            'failed' => 'Błąd'
                        ];
                        $statusLabel = $statusLabels[$campaign['status']] ?? $campaign['status'];
                        ?>
                        <span class="status-badge status-badge--<?php echo $campaign['status']; ?>">
                            <?php echo $statusLabel; ?>
                        </span>
                    </td>
                    <td style="font-size: 13px;">
                        <?php if ($campaign['sent_at']): ?>
                            <?php echo date('d.m.Y H:i', strtotime($campaign['sent_at'])); ?>
                        <?php elseif ($campaign['scheduled_at']): ?>
                            <i class="bi bi-clock"></i> <?php echo date('d.m.Y H:i', strtotime($campaign['scheduled_at'])); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;"><?php echo number_format($campaign['recipients_count'], 0, ',', ' '); ?></td>
                    <td style="text-align: center;">
                        <?php echo number_format($campaign['sent_count'], 0, ',', ' '); ?>
                        <?php if ($campaign['recipients_count'] > 0): ?>
                        <div class="progress-bar">
                            <div class="progress-bar__fill" style="width: <?php echo min(100, ($campaign['sent_count'] / $campaign['recipients_count'] * 100)); ?>%"></div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php 
                        $rate = $campaign['sent_count'] > 0 ? round(($campaign['opened_count'] / $campaign['sent_count']) * 100, 1) : 0;
                        echo $rate; 
                        ?>%
                    </td>
                    <td style="text-align: center;">
                        <div class="action-icons">
                            <?php if ($campaign['status'] === 'draft'): ?>
                            <a href="/admin/newsletter-edit.php?id=<?php echo $campaign['id']; ?>" title="Edytuj">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="/admin/newsletter-send.php?id=<?php echo $campaign['id']; ?>" title="Zaplanuj wysyłkę">
                                <i class="bi bi-calendar-check"></i>
                            </a>
                            <a href="?send_now=<?php echo $campaign['id']; ?>" 
                               onclick="return confirm('Wysłać teraz do wszystkich aktywnych subskrybentów?')" 
                               title="Wyślij teraz">
                                <i class="bi bi-send-fill"></i>
                            </a>
                            <a href="?duplicate=<?php echo $campaign['id']; ?>" title="Duplikuj">
                                <i class="bi bi-files"></i>
                            </a>
                            <a href="?delete=<?php echo $campaign['id']; ?>" 
                               onclick="return confirm('Czy na pewno usunąć tę kampanię?')" 
                               title="Usuń">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php elseif ($campaign['status'] === 'sent'): ?>
                            <a href="/admin/newsletter-stats.php?id=<?php echo $campaign['id']; ?>" title="Statystyki">
                                <i class="bi bi-bar-chart"></i>
                            </a>
                            <a href="?duplicate=<?php echo $campaign['id']; ?>" title="Duplikuj">
                                <i class="bi bi-files"></i>
                            </a>
                            <a href="?delete=<?php echo $campaign['id']; ?>" 
                               onclick="return confirm('Czy na pewno usunąć tę kampanię?')" 
                               title="Usuń">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php else: ?>
                            <a href="/admin/newsletter-stats.php?id=<?php echo $campaign['id']; ?>" title="Statystyki">
                                <i class="bi bi-bar-chart"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>