<?php
/**
 * ADMIN/API/SEND-CAMPAIGN.PHP - NAPRAWIONY (zmienne + buttons-row)
 */

set_time_limit(600);
ini_set('max_execution_time', 600);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== NEWSLETTER CAMPAIGN START ===\n";

$campaignId = null;

if (php_sapi_name() === 'cli') {
    $campaignId = isset($argv[1]) ? (int)$argv[1] : null;
} else {
    $campaignId = isset($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : null;
}

if (!$campaignId) {
    die("ERROR: Brak campaign_id\n");
}

echo "Campaign ID: $campaignId\n";

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/email-helpers.php';

echo "✓ Zależności załadowane\n";

try {
    $stmt = $pdo->prepare("SELECT * FROM newsletter_campaigns WHERE id = ?");
    $stmt->execute([$campaignId]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$campaign) {
        throw new Exception("Kampania nie istnieje");
    }
    
    echo "✓ Kampania: {$campaign['name']}\n";
    echo "  Status: {$campaign['status']}\n";
    
    if ($campaign['status'] !== 'sending') {
        throw new Exception("Status musi być 'sending' (jest: {$campaign['status']})");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM newsletter_blocks WHERE campaign_id = ? ORDER BY block_order ASC");
    $stmt->execute([$campaignId]);
    $blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($blocks)) {
        throw new Exception("Brak bloków");
    }
    
    echo "✓ Bloki: " . count($blocks) . "\n";
    
    $emailHTML = renderCampaignHTML($campaign, $blocks);
    echo "✓ HTML wygenerowany (" . strlen($emailHTML) . " znaków)\n";
    
    $stmt = $pdo->query("
        SELECT DISTINCT email, additional_data 
        FROM marketing_consents 
        WHERE status = 'active' 
        AND consent_marketing = 1
    ");
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($subscribers)) {
        throw new Exception("Brak subskrybentów");
    }
    
    $total = count($subscribers);
    echo "✓ Subskrybenci: $total\n";
    echo "\n--- ROZPOCZYNAM WYSYŁKĘ ---\n\n";
    
    $sent = 0;
    $failed = 0;
    
    foreach ($subscribers as $subscriber) {
        $email = $subscriber['email'];
        
        // Wyciągnij imię
        $name = 'Użytkowniku';
        if (!empty($subscriber['additional_data'])) {
            $data = json_decode($subscriber['additional_data'], true);
            if (isset($data['name']) && !empty($data['name'])) {
                $name = $data['name'];
            }
        }
        
        echo "Wysyłam do: $email ($name) ... ";
        
        try {
            $personalizedHTML = personalizeEmail($emailHTML, $email, $name);
            
            if (sendNewsletterEmail($email, $campaign['subject'], $personalizedHTML, $campaignId)) {
                $sent++;
                echo "✅ OK\n";
                logSend($pdo, $campaignId, $email, 'sent');
            } else {
                $failed++;
                echo "❌ FAIL\n";
                logSend($pdo, $campaignId, $email, 'failed', 'PHPMailer error');
            }
            
            usleep(100000);
            
        } catch (Exception $e) {
            $failed++;
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            logSend($pdo, $campaignId, $email, 'failed', $e->getMessage());
        }
    }
    
    echo "\n--- WYSYŁKA ZAKOŃCZONA ---\n";
    echo "Wysłano: $sent / $total\n";
    echo "Błędy: $failed\n";
    
    $stmt = $pdo->prepare("
        UPDATE newsletter_campaigns 
        SET status = 'sent',
            sent_count = ?,
            sent_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$sent, $campaignId]);
    
    echo "✓ Status zaktualizowany\n";
    echo "\n=== NEWSLETTER CAMPAIGN END ===\n";
    
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'total' => $total
        ]);
    }
    
} catch (Exception $e) {
    echo "\n❌ BŁĄD: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    
    try {
        $stmt = $pdo->prepare("UPDATE newsletter_campaigns SET status = 'failed' WHERE id = ?");
        $stmt->execute([$campaignId]);
    } catch (Exception $e2) {}
    
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit(1);
}

// ========== FUNKCJE ==========

function renderCampaignHTML($campaign, $blocks) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'info@maltechnik.pl';
    $companyWebsite = $settings['company_website'] ?? 'www.maltechnik.pl';
    
    $blocksHTML = '';
    foreach ($blocks as $block) {
        $blocksHTML .= renderBlock($block);
    }
    
    return '
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($campaign['subject']) . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, sans-serif; background-color: #f5f5f5;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f5f5f5; padding: 20px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">' . htmlspecialchars($companyName) . '</h1>
                            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Newsletter</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            ' . $blocksHTML . '
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; border-top: 1px solid #e5e7eb;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 20px; text-align: center;">
                                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #6b7280; font-weight: 600;">' . htmlspecialchars($companyName) . '</p>
                                        <p style="margin: 0 0 5px 0; font-size: 14px; color: #6b7280;">📞 ' . htmlspecialchars($companyPhone) . '</p>
                                        <p style="margin: 0 0 5px 0; font-size: 14px; color: #6b7280;">✉️ ' . htmlspecialchars($companyEmail) . '</p>
                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">🌐 <a href="https://' . htmlspecialchars($companyWebsite) . '" style="color: #2B59A6; text-decoration: none;">' . htmlspecialchars($companyWebsite) . '</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
                                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #9ca3af;">© ' . date('Y') . ' ' . htmlspecialchars($companyName) . '. Wszelkie prawa zastrzeżone.</p>
                                        <p style="margin: 0; font-size: 12px;"><a href="{{unsubscribe_link}}" style="color: #9ca3af; text-decoration: underline;">Wypisz się z newslettera</a></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

function renderBlock($block) {
    $content = json_decode($block['content'], true);
    $type = $block['block_type'];
    
    switch ($type) {
        case 'heading':
            $size = $content['size'] ?? 'h2';
            $color = $content['color'] ?? '#111827';
            $align = $content['align'] ?? 'left';
            $text = $content['text'] ?? '';
            return "<$size style='color: $color; text-align: $align; margin: 0 0 16px 0;'>" . htmlspecialchars($text) . "</$size>";
        
        case 'text':
            $color = $content['color'] ?? '#374151';
            $align = $content['align'] ?? 'left';
            $text = $content['text'] ?? '';
            return "<p style='color: $color; text-align: $align; margin: 0 0 16px 0; line-height: 1.6;'>" . nl2br(htmlspecialchars($text)) . "</p>";
        
        case 'button':
            $bgColor = $content['bg_color'] ?? '#2B59A6';
            $textColor = $content['text_color'] ?? '#ffffff';
            $text = $content['text'] ?? 'Kliknij';
            $link = $content['link'] ?? '#';
            $align = $content['align'] ?? 'center';
            return "<div style='text-align: $align; margin: 20px 0;'><a href='$link' style='display: inline-block; background: $bgColor; color: $textColor; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;'>" . htmlspecialchars($text) . "</a></div>";
        
        case 'buttons-row':
            // NAPRAWIONE: Obsługa podwójnego przycisku
            $buttons = $content['buttons'] ?? [];
            if (empty($buttons)) {
                return '';
            }
            
            $html = '<div style="text-align: center; margin: 20px 0;">';
            $html .= '<table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;"><tr>';
            
            foreach ($buttons as $btn) {
                $btnText = $btn['text'] ?? 'Przycisk';
                $btnLink = $btn['link'] ?? '#';
                $btnBg = $btn['bg_color'] ?? '#2B59A6';
                $html .= '<td style="padding: 0 6px;"><a href="' . $btnLink . '" style="display: inline-block; background: ' . $btnBg . '; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;">' . htmlspecialchars($btnText) . '</a></td>';
            }
            
            $html .= '</tr></table>';
            $html .= '</div>';
            return $html;
        
        case 'image':
            $url = $content['url'] ?? '';
            $alt = $content['alt'] ?? '';
            $width = $content['width'] ?? '100%';
            $link = $content['link'] ?? '';
            $img = "<img src='$url' alt='" . htmlspecialchars($alt) . "' style='max-width: $width; height: auto; display: block; margin: 16px auto; border-radius: 8px;'>";
            return $link ? "<a href='$link' style='display: block; text-align: center;'>$img</a>" : "<div style='text-align: center;'>$img</div>";
        
        case 'divider':
            $color = $content['color'] ?? '#e5e7eb';
            $height = $content['height'] ?? '1px';
            return "<hr style='border: none; border-top: $height solid $color; margin: 24px 0;'>";
        
        case 'social':
            $links = [];
            if (!empty($content['facebook'])) {
                $links[] = "<a href='{$content['facebook']}' style='display: inline-block; margin: 0 12px;'><img src='https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg' width='32' height='32' alt='Facebook'></a>";
            }
            if (!empty($content['instagram'])) {
                $links[] = "<a href='{$content['instagram']}' style='display: inline-block; margin: 0 12px;'><img src='https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png' width='32' height='32' alt='Instagram'></a>";
            }
            if (!empty($content['linkedin'])) {
                $links[] = "<a href='{$content['linkedin']}' style='display: inline-block; margin: 0 12px;'><img src='https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png' width='32' height='32' alt='LinkedIn'></a>";
            }
            return empty($links) ? '' : "<div style='text-align: center; margin: 24px 0;'>" . implode('', $links) . "</div>";
        
        default:
            return '';
    }
}

function personalizeEmail($html, $email, $name = 'Użytkowniku') {
    // NAPRAWIONE: Używamy {{first_name}} zamiast [IMIE]
    $html = str_replace('{{first_name}}', htmlspecialchars($name), $html);
    $html = str_replace('{{email}}', htmlspecialchars($email), $html);
    $html = str_replace('{{unsubscribe_link}}', 'https://www.maltechnik.pl/unsubscribe.php?email=' . urlencode($email), $html);
    return $html;
}

function sendNewsletterEmail($toEmail, $subject, $htmlContent, $campaignId) {
    try {
        $mail = initPHPMailer();
        
        if (!$mail) {
            return false;
        }
        
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlContent;
        $mail->AltBody = strip_tags($htmlContent);
        
        $mail->addCustomHeader('X-Campaign-ID', $campaignId);
        $mail->addCustomHeader('List-Unsubscribe', '<https://www.maltechnik.pl/unsubscribe.php?email=' . urlencode($toEmail) . '>');
        
        return $mail->send();
        
    } catch (Exception $e) {
        echo "  (PHPMailer error: " . $e->getMessage() . ")";
        return false;
    }
}

function logSend($pdo, $campaignId, $email, $status, $error = null) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'newsletter_sends'");
        if ($stmt->rowCount() === 0) return;
        
        $stmt = $pdo->prepare("
            INSERT INTO newsletter_sends 
            (campaign_id, email, status, sent_at, error_message)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$campaignId, $email, $status, $error]);
    } catch (Exception $e) {}
}