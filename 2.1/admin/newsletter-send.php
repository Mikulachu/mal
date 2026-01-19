<?php
/**
 * NEWSLETTER-SEND.PHP - FINAL (FB, IG, YT + pojedyncza wysyłka)
 */

session_start();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/email-helpers.php';

if (!isset($_SESSION['admin_logged_in'])) {
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
        $_SESSION['error_message'] = 'Ta kampania została już wysłana';
        header('Location: /admin/newsletter.php');
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Błąd: ' . $e->getMessage();
    header('Location: /admin/newsletter.php');
    exit;
}

try {
    $stmt = $pdo->query("SELECT COUNT(DISTINCT email) FROM marketing_consents WHERE status = 'active' AND consent_marketing = 1");
    $recipientsCount = $stmt->fetchColumn();
} catch (Exception $e) {
    $recipientsCount = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['send_type'] === 'now') {
    
    try {
        // Status
        $pdo->prepare("UPDATE newsletter_campaigns SET status = 'sending', recipients_count = ? WHERE id = ?")->execute([$recipientsCount, $campaignId]);
        
        // Bloki
        $stmt = $pdo->prepare("SELECT * FROM newsletter_blocks WHERE campaign_id = ? ORDER BY block_order");
        $stmt->execute([$campaignId]);
        $blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($blocks)) {
            throw new Exception("Brak bloków!");
        }
        
        // Generuj HTML
        $settings = getSettings();
        $companyName = $settings['company_name'] ?? 'Maltechnik';
        
        $blocksHTML = '';
        foreach ($blocks as $block) {
            $content = json_decode($block['content'], true);
            $type = $block['block_type'];
            
            switch ($type) {
                case 'heading':
                    $text = $content['text'] ?? '';
                    $size = $content['size'] ?? 'h2';
                    $color = $content['color'] ?? '#2B59A6';
                    $align = $content['align'] ?? 'left';
                    $blocksHTML .= "<$size style='color:$color;text-align:$align;margin:20px 0 10px 0;'>$text</$size>";
                    break;
                
                case 'text':
                    $text = $content['text'] ?? '';
                    $color = $content['color'] ?? '#374151';
                    $align = $content['align'] ?? 'left';
                    $blocksHTML .= "<p style='line-height:1.6;margin:10px 0;color:$color;text-align:$align;'>" . nl2br($text) . "</p>";
                    break;
                
                case 'button':
                    $text = $content['text'] ?? 'Kliknij';
                    $link = $content['link'] ?? '#';
                    $bgColor = $content['bg_color'] ?? '#2B59A6';
                    $textColor = $content['text_color'] ?? '#FFFFFF';
                    $align = $content['align'] ?? 'center';
                    $blocksHTML .= "<div style='text-align:$align;margin:25px 0;'><a href='$link' style='display:inline-block;background:$bgColor;color:$textColor;padding:14px 32px;text-decoration:none;border-radius:6px;font-weight:600;'>" . htmlspecialchars($text) . "</a></div>";
                    break;
                
                case 'buttons-row':
                    $buttons = $content['buttons'] ?? [];
                    if (!empty($buttons)) {
                        $blocksHTML .= "<div style='text-align:center;margin:25px 0;'>";
                        $blocksHTML .= "<table cellpadding='0' cellspacing='0' border='0' style='margin:0 auto;'><tr>";
                        foreach ($buttons as $btn) {
                            $btnText = $btn['text'] ?? 'Przycisk';
                            $btnLink = $btn['link'] ?? '#';
                            $btnBg = $btn['bg_color'] ?? '#2B59A6';
                            $blocksHTML .= "<td style='padding:0 6px;'><a href='$btnLink' style='display:inline-block;background:$btnBg;color:white;padding:14px 28px;text-decoration:none;border-radius:6px;font-weight:600;'>" . htmlspecialchars($btnText) . "</a></td>";
                        }
                        $blocksHTML .= "</tr></table></div>";
                    }
                    break;
                
                case 'image':
                    $url = $content['url'] ?? '';
                    if ($url) {
                        $alt = $content['alt'] ?? '';
                        $width = $content['width'] ?? '100%';
                        $link = $content['link'] ?? '';
                        $img = "<img src='$url' alt='" . htmlspecialchars($alt) . "' style='max-width:$width;height:auto;border-radius:8px;display:block;margin:0 auto;'>";
                        if ($link) {
                            $blocksHTML .= "<div style='text-align:center;margin:20px 0;'><a href='$link'>$img</a></div>";
                        } else {
                            $blocksHTML .= "<div style='text-align:center;margin:20px 0;'>$img</div>";
                        }
                    }
                    break;
                
                case 'divider':
                    $color = $content['color'] ?? '#e5e7eb';
                    $height = $content['height'] ?? '1px';
                    $blocksHTML .= "<hr style='border:none;border-top:$height solid $color;margin:30px 0;'>";
                    break;
                
                case 'conditional':
                    $condition = $content['condition'] ?? 'has_name';
                    $ifContent = $content['if_content'] ?? '';
                    $elseContent = $content['else_content'] ?? '';
                    
                    $blocksHTML .= "{{#conditional:$condition:start}}";
                    $blocksHTML .= "<div>" . nl2br($ifContent) . "</div>";
                    $blocksHTML .= "{{#conditional:$condition:else}}";
                    $blocksHTML .= "<div>" . nl2br($elseContent) . "</div>";
                    $blocksHTML .= "{{#conditional:$condition:end}}";
                    break;
                
                case 'social':
                    // NAPRAWIONE: FB, Instagram, YouTube
                    $links = [];
                    if (!empty($content['facebook'])) {
                        $links[] = "<a href='{$content['facebook']}' style='display:inline-block;margin:0 8px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733547.png' width='32' height='32' alt='Facebook' style='border-radius:4px;'></a>";
                    }
                    if (!empty($content['instagram'])) {
                        $links[] = "<a href='{$content['instagram']}' style='display:inline-block;margin:0 8px;'><img src='https://cdn-icons-png.flaticon.com/512/2111/2111463.png' width='32' height='32' alt='Instagram' style='border-radius:4px;'></a>";
                    }
                    if (!empty($content['youtube'])) {
                        $links[] = "<a href='{$content['youtube']}' style='display:inline-block;margin:0 8px;'><img src='https://cdn-icons-png.flaticon.com/512/1384/1384060.png' width='32' height='32' alt='YouTube' style='border-radius:4px;'></a>";
                    }
                    if (!empty($links)) {
                        $blocksHTML .= "<div style='text-align:center;margin:24px 0;'>" . implode('', $links) . "</div>";
                    }
                    break;
            }
        }
        
        $emailHTML = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='margin:0;padding:0;font-family:Arial,sans-serif;background:#f5f5f5;'><table cellpadding='0' cellspacing='0' border='0' width='100%' style='background:#f5f5f5;padding:20px 0;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' width='600' style='max-width:600px;background:#fff;border-radius:8px;'><tr><td style='background:linear-gradient(135deg,#2B59A6 0%,#1e3a8a 100%);padding:40px 30px;text-align:center;'><h1 style='margin:0;color:#fff;font-size:28px;'>{$companyName}</h1><p style='margin:8px 0 0 0;color:rgba(255,255,255,0.9);font-size:14px;'>Newsletter</p></td></tr><tr><td style='padding:40px 30px;'>{$blocksHTML}</td></tr><tr><td style='background:#f8f9fa;padding:30px;border-top:1px solid #e5e7eb;text-align:center;'><p style='margin:0;font-size:12px;color:#9ca3af;'><a href='{{unsubscribe_link}}' style='color:#9ca3af;'>Wypisz się</a></p></td></tr></table></td></tr></table></body></html>";
        
        // Pobierz subskrybentów TYLKO RAZ
        $stmt = $pdo->query("
            SELECT DISTINCT email, additional_data, source
            FROM marketing_consents 
            WHERE status = 'active' AND consent_marketing = 1
        ");
        $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($subscribers)) {
            throw new Exception("Brak subskrybentów!");
        }
        
        $sent = 0;
        $failed = 0;
        
        // WYSYŁKA - TYLKO JEDNA PĘTLA
        foreach ($subscribers as $subscriber) {
            $email = $subscriber['email'];
            $source = $subscriber['source'] ?? '';
            
            $name = '';
            $phone = '';
            if (!empty($subscriber['additional_data'])) {
                $data = json_decode($subscriber['additional_data'], true);
                $name = $data['name'] ?? '';
                $phone = $data['phone'] ?? '';
            }
            
            try {
                $personalizedHTML = personalizeEmail($emailHTML, $email, $name, $phone, $source);
                
                $mail = initPHPMailer();
                
                if (!$mail) {
                    $failed++;
                    continue;
                }
                
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $campaign['subject'];
                $mail->Body = $personalizedHTML;
                $mail->AltBody = strip_tags($personalizedHTML);
                
                if ($mail->send()) {
                    $sent++;
                } else {
                    $failed++;
                }
                
                usleep(100000); // 100ms delay
                
            } catch (Exception $e) {
                $failed++;
            }
        }
        
        // Aktualizuj status
        $pdo->prepare("UPDATE newsletter_campaigns SET status = 'sent', sent_count = ?, sent_at = NOW() WHERE id = ?")->execute([$sent, $campaignId]);
        
        // Sukces
        $_SESSION['success_message'] = "Kampania została wysłana! Wysłano: $sent emaili" . ($failed > 0 ? ", Błędy: $failed" : '');
        header('Location: /admin/newsletter.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Błąd wysyłki: ' . $e->getMessage();
        header('Location: /admin/newsletter.php');
        exit;
    }
}

function extractFirstName($fullName) {
    if (empty($fullName)) {
        return 'Użytkowniku';
    }
    
    $fullName = trim($fullName);
    $parts = explode(' ', $fullName);
    $firstName = $parts[0];
    
    if (strlen($firstName) < 2) {
        return $fullName;
    }
    
    return $firstName;
}

function personalizeEmail($html, $email, $name = '', $phone = '', $source = '') {
    $firstName = extractFirstName($name);
    
    $html = str_replace('{{first_name}}', htmlspecialchars($firstName), $html);
    $html = str_replace('{{email}}', htmlspecialchars($email), $html);
    $html = str_replace('{{unsubscribe_link}}', 'https://www.maltechnik.pl/unsubscribe.php?email=' . urlencode($email), $html);
    
    // Warunki
    if (preg_match('/\{\{#conditional:has_name:start\}\}(.*?)\{\{#conditional:has_name:else\}\}(.*?)\{\{#conditional:has_name:end\}\}/s', $html, $matches)) {
        $replacement = !empty($name) ? $matches[1] : $matches[2];
        $html = preg_replace('/\{\{#conditional:has_name:start\}\}.*?\{\{#conditional:has_name:end\}\}/s', $replacement, $html);
    }
    
    if (preg_match('/\{\{#conditional:has_phone:start\}\}(.*?)\{\{#conditional:has_phone:else\}\}(.*?)\{\{#conditional:has_phone:end\}\}/s', $html, $matches)) {
        $replacement = !empty($phone) ? $matches[1] : $matches[2];
        $html = preg_replace('/\{\{#conditional:has_phone:start\}\}.*?\{\{#conditional:has_phone:end\}\}/s', $replacement, $html);
    }
    
    if (preg_match('/\{\{#conditional:source_newsletter:start\}\}(.*?)\{\{#conditional:source_newsletter:else\}\}(.*?)\{\{#conditional:source_newsletter:end\}\}/s', $html, $matches)) {
        $replacement = ($source === 'newsletter') ? $matches[1] : $matches[2];
        $html = preg_replace('/\{\{#conditional:source_newsletter:start\}\}.*?\{\{#conditional:source_newsletter:end\}\}/s', $replacement, $html);
    }
    
    if (preg_match('/\{\{#conditional:source_contact:start\}\}(.*?)\{\{#conditional:source_contact:else\}\}(.*?)\{\{#conditional:source_contact:end\}\}/s', $html, $matches)) {
        $replacement = ($source === 'contact') ? $matches[1] : $matches[2];
        $html = preg_replace('/\{\{#conditional:source_contact:start\}\}.*?\{\{#conditional:source_contact:end\}\}/s', $replacement, $html);
    }
    
    if (preg_match('/\{\{#conditional:source_quote:start\}\}(.*?)\{\{#conditional:source_quote:else\}\}(.*?)\{\{#conditional:source_quote:end\}\}/s', $html, $matches)) {
        $replacement = ($source === 'quote') ? $matches[1] : $matches[2];
        $html = preg_replace('/\{\{#conditional:source_quote:start\}\}.*?\{\{#conditional:source_quote:end\}\}/s', $replacement, $html);
    }
    
    return $html;
}

include __DIR__ . '/includes/admin-header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.send-container { max-width: 900px; margin: 0 auto; }
.campaign-preview { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 24px; margin-bottom: 24px; }
.recipients-info { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 16px; margin-bottom: 24px; text-align: center; }
.recipients-info__number { font-size: 32px; font-weight: 700; color: #1E40AF; margin: 0; }
.warning-box { background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
.actions-bar { display: flex; gap: 12px; justify-content: flex-end; }
</style>

<div class="admin-content">
    <div class="send-container">
        <div class="page-header">
            <h1><i class="bi bi-send"></i> Wysyłka kampanii</h1>
            <a href="/admin/newsletter.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Powrót
            </a>
        </div>
        
        <div class="campaign-preview">
            <h3><?php echo htmlspecialchars($campaign['name']); ?></h3>
            <p><strong>Temat:</strong> <?php echo htmlspecialchars($campaign['subject']); ?></p>
        </div>
        
        <div class="recipients-info">
            <p class="recipients-info__number"><?php echo number_format($recipientsCount, 0, ',', ' '); ?></p>
            <p style="font-size: 14px; color: #1E3A8A; margin: 0;">Aktywnych subskrybentów</p>
        </div>
        
        <?php if ($recipientsCount > 0): ?>
        <form method="POST">
            <div class="warning-box">
                <p style="margin: 0; color: #92400E; font-weight: 600;">⚠️ Ważne</p>
                <p style="margin: 5px 0 0 0; color: #92400E; font-size: 13px;">
                    Po kliknięciu zostaniesz przekierowany do panelu. Wysyłka rozpocznie się natychmiast.
                </p>
            </div>
            
            <input type="hidden" name="send_type" value="now">
            
            <div class="actions-bar">
                <a href="/admin/newsletter.php" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Anuluj
                </a>
                <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 14px 32px;">
                    <i class="bi bi-send"></i> Wyślij natychmiast
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-warning">Brak subskrybentów</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>