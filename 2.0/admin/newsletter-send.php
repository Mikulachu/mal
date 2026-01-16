<?php
/**
 * ADMIN/NEWSLETTER-SEND.PHP - FINALNA WERSJA
 * Wywołuje skrypt BEZPOŚREDNIO (include) zamiast exec/curl
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

$pageTitle = 'Wysyłka kampanii';
$currentPage = 'newsletter';

$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$campaignId) {
    $_SESSION['error_message'] = 'Nieprawidłowe ID kampanii';
    header('Location: /admin/newsletter.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM newsletter_campaigns WHERE id = ?");
    $stmt->execute([$campaignId]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$campaign) {
        $_SESSION['error_message'] = 'Kampania nie została znaleziona';
        header('Location: /admin/newsletter.php');
        exit;
    }
    
    if ($campaign['status'] !== 'draft') {
        $_SESSION['error_message'] = 'Ta kampania została już wysłana lub jest w trakcie wysyłki';
        header('Location: /admin/newsletter.php');
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Błąd pobierania kampanii';
    header('Location: /admin/newsletter.php');
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT email) as count
        FROM marketing_consents
        WHERE status = 'active' AND consent_marketing = 1
    ");
    $recipientsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (Exception $e) {
    $recipientsCount = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sendType = $_POST['send_type'] ?? '';
    $scheduledDate = $_POST['scheduled_date'] ?? '';
    $scheduledTime = $_POST['scheduled_time'] ?? '';
    
    $errors = [];
    
    if ($sendType === 'scheduled') {
        if (empty($scheduledDate) || empty($scheduledTime)) {
            $errors[] = 'Podaj datę i godzinę wysyłki';
        } else {
            $scheduledAt = $scheduledDate . ' ' . $scheduledTime . ':00';
            if (strtotime($scheduledAt) < time()) {
                $errors[] = 'Data wysyłki nie może być w przeszłości';
            }
        }
    }
    
    if (empty($errors)) {
        try {
            if ($sendType === 'now') {
                // Zmień status na 'sending'
                $stmt = $pdo->prepare("
                    UPDATE newsletter_campaigns 
                    SET status = 'sending',
                        recipients_count = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$recipientsCount, $campaignId]);
                
                // WYWOŁAJ SKRYPT BEZPOŚREDNIO
                // Ustawiamy zmienną globalną dla skryptu
                $_SERVER['argc'] = 2;
                $_SERVER['argv'] = ['send-campaign.php', $campaignId];
                
                // Buforuj output (żeby nie zepsuć nagłówków HTTP)
                ob_start();
                
                try {
                    // Include skryptu (działa jak wywołanie)
                    include __DIR__ . '/api/send-campaign.php';
                    
                    // Pobierz output
                    $output = ob_get_clean();
                    
                    // Loguj output do pliku
                    $logFile = __DIR__ . '/../logs/newsletter-send.log';
                    $logDir = dirname($logFile);
                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0755, true);
                    }
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Campaign #$campaignId\n" . $output . "\n\n", FILE_APPEND);
                    
                    $_SESSION['success_message'] = "Wysyłka zakończona pomyślnie! Wysłano do {$recipientsCount} subskrybentów.";
                    
                } catch (Exception $e) {
                    ob_end_clean();
                    throw $e;
                }
                
            } else {
                // Zaplanowana wysyłka
                $stmt = $pdo->prepare("
                    UPDATE newsletter_campaigns 
                    SET status = 'scheduled',
                        recipients_count = ?,
                        scheduled_at = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$recipientsCount, $scheduledAt, $campaignId]);
                
                $_SESSION['success_message'] = "Kampania zaplanowana na " . date('d.m.Y H:i', strtotime($scheduledAt));
            }
            
            header('Location: /admin/newsletter.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = 'Błąd: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/admin-header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.send-container { max-width: 800px; margin: 0 auto; }
.campaign-preview { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 24px; margin-bottom: 24px; }
.campaign-preview h3 { margin: 0 0 8px 0; font-size: 18px; }
.campaign-preview p { margin: 0; color: #6B7280; font-size: 14px; }
.send-options { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 24px; margin-bottom: 24px; }
.send-options h4 { margin: 0 0 20px 0; font-size: 16px; font-weight: 600; }
.send-option { border: 2px solid #E5E7EB; border-radius: 8px; padding: 20px; margin-bottom: 16px; cursor: pointer; transition: all 0.2s; }
.send-option:hover { border-color: #2B59A6; background: #F9FAFB; }
.send-option.active { border-color: #2B59A6; background: #EFF6FF; }
.send-option input[type="radio"] { margin-right: 12px; }
.send-option__header { display: flex; align-items: center; margin-bottom: 8px; }
.send-option__title { font-weight: 600; font-size: 15px; }
.send-option__desc { color: #6B7280; font-size: 13px; margin-left: 28px; }
.schedule-fields { display: none; margin-top: 16px; padding-top: 16px; border-top: 1px solid #E5E7EB; }
.schedule-fields.active { display: block; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-group input { width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; }
.recipients-info { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
.recipients-info__number { font-size: 32px; font-weight: 700; color: #1E40AF; margin: 0; }
.recipients-info__label { font-size: 14px; color: #1E3A8A; margin: 0; }
.warning-box { background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
.warning-box__title { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #92400E; margin-bottom: 8px; }
.warning-box__text { color: #92400E; font-size: 13px; }
.actions-bar { display: flex; gap: 12px; justify-content: flex-end; }
</style>

<div class="admin-content">
    <div class="send-container">
        <div class="page-header">
            <h1><i class="bi bi-send"></i> Wysyłka kampanii</h1>
            <a href="/admin/newsletter.php" class="btn btn-secondary">
                <i class="bi bi-x"></i> Anuluj
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-x-circle"></i>
            <ul style="margin: 8px 0 0 20px; padding: 0;">
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="campaign-preview">
            <h3><?php echo htmlspecialchars($campaign['name']); ?></h3>
            <p><strong>Temat:</strong> <?php echo htmlspecialchars($campaign['subject']); ?></p>
        </div>
        
        <div class="recipients-info">
            <p class="recipients-info__number"><?php echo number_format($recipientsCount, 0, ',', ' '); ?></p>
            <p class="recipients-info__label">Aktywnych subskrybentów</p>
        </div>
        
        <?php if ($recipientsCount === 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Brak aktywnych subskrybentów.
        </div>
        <?php else: ?>
        
        <form method="POST">
            <div class="send-options">
                <h4>Wybierz sposób wysyłki</h4>
                
                <div class="send-option active" onclick="selectOption('now')">
                    <div class="send-option__header">
                        <input type="radio" name="send_type" value="now" id="send_now" checked>
                        <label for="send_now" class="send-option__title">Wyślij natychmiast</label>
                    </div>
                    <div class="send-option__desc">
                        Kampania zostanie wysłana natychmiast do wszystkich subskrybentów
                    </div>
                </div>
                
                <div class="send-option" onclick="selectOption('scheduled')">
                    <div class="send-option__header">
                        <input type="radio" name="send_type" value="scheduled" id="send_scheduled">
                        <label for="send_scheduled" class="send-option__title">Zaplanuj wysyłkę</label>
                    </div>
                    <div class="send-option__desc">
                        Ustaw datę i godzinę (wymaga crona)
                    </div>
                    
                    <div class="schedule-fields" id="schedule-fields">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="scheduled_date">Data</label>
                                <input type="date" id="scheduled_date" name="scheduled_date" 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="scheduled_time">Godzina</label>
                                <input type="time" id="scheduled_time" name="scheduled_time">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="warning-box">
                <div class="warning-box__title">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Ważne</span>
                </div>
                <div class="warning-box__text">
                    Po wysłaniu nie będzie można edytować ani zatrzymać kampanii. Upewnij się, że wszystko jest poprawne.
                </div>
            </div>
            
            <div class="actions-bar">
                <a href="/admin/newsletter.php" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Anuluj
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Potwierdź wysyłkę
                </button>
            </div>
        </form>
        
        <?php endif; ?>
    </div>
</div>

<script>
function selectOption(type) {
    document.querySelectorAll('.send-option').forEach(opt => opt.classList.remove('active'));
    document.getElementById('schedule-fields').classList.remove('active');
    
    if (type === 'now') {
        document.getElementById('send_now').checked = true;
        document.querySelector('[onclick="selectOption(\'now\')"]').classList.add('active');
    } else {
        document.getElementById('send_scheduled').checked = true;
        document.querySelector('[onclick="selectOption(\'scheduled\')"]').classList.add('active');
        document.getElementById('schedule-fields').classList.add('active');
    }
}

document.querySelectorAll('.send-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        selectOption(this.value);
    });
});
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>