<?php
/**
 * EMAIL-HELPERS.PHP - Pomocnicze funkcje do wysyłki emaili przez PHPMailer
 * Wszystkie funkcje używają konfiguracji z includes/db.php
 */

/**
 * Inicjalizacja PHPMailer z konfiguracją z db.php
 * @return PHPMailer|false
 */
function initPHPMailer() {
    try {
        // Sprawdź czy PHPMailer jest załadowany
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Spróbuj załadować
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require __DIR__ . '/../vendor/autoload.php';
            } elseif (file_exists(__DIR__ . '/../lib/phpmailer/src/PHPMailer.php')) {
                require __DIR__ . '/../lib/phpmailer/src/PHPMailer.php';
                require __DIR__ . '/../lib/phpmailer/src/SMTP.php';
                require __DIR__ . '/../lib/phpmailer/src/Exception.php';
            } else {
                error_log("⚠ PHPMailer nie jest zainstalowany");
                return false;
            }
        }
        
        // Pobierz konfigurację SMTP z db.php
        $smtpConfig = getSMTPConfig();
        
        // Utwórz instancję PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Konfiguracja SMTP
        $mail->isSMTP();
        $mail->Host = $smtpConfig['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['smtp_username'];
        $mail->Password = $smtpConfig['smtp_password'];
        $mail->SMTPSecure = $smtpConfig['smtp_encryption'];
        $mail->Port = $smtpConfig['smtp_port'];
        $mail->CharSet = 'UTF-8';
        
        // Nadawca z konfiguracji
        $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
        
        return $mail;
        
    } catch (Exception $e) {
        error_log("⚠ Błąd inicjalizacji PHPMailer: " . $e->getMessage());
        return false;
    }
}

/**
 * Bazowy szablon HTML dla emaili
 */
function getEmailTemplate($content, $title = '') {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'info@maltechnik.pl';
    
    return '
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, sans-serif; background-color: #f5f5f5;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f5f5f5; padding: 20px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">' . htmlspecialchars($companyName) . '</h1>
                            ' . ($title ? '<p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">' . htmlspecialchars($title) . '</p>' : '') . '
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            ' . $content . '
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; border-top: 1px solid #e5e7eb;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #6b7280;"><strong style="color: #111827;">Kontakt:</strong></p>
                                        <p style="margin: 0 0 5px 0; font-size: 14px; color: #6b7280;">📞 ' . htmlspecialchars($companyPhone) . '</p>
                                        <p style="margin: 0 0 5px 0; font-size: 14px; color: #6b7280;">✉️ ' . htmlspecialchars($companyEmail) . '</p>
                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">🌐 <a href="https://www.maltechnik.pl" style="color: #2B59A6; text-decoration: none;">www.maltechnik.pl</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
                                        <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                            © ' . date('Y') . ' ' . htmlspecialchars($companyName) . '. Wszelkie prawa zastrzeżone.
                                        </p>
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

/**
 * Wyślij email z użyciem PHPMailer
 * 
 * @param string $to Email odbiorcy
 * @param string $toName Imię odbiorcy
 * @param string $subject Temat
 * @param string $htmlContent Treść HTML
 * @param string $replyTo Opcjonalny reply-to email
 * @return bool
 */
function sendHTMLEmail($to, $toName, $subject, $htmlContent, $replyTo = null) {
    try {
        $mail = initPHPMailer();
        
        if (!$mail) {
            error_log("⚠ Nie można zainicjalizować PHPMailer");
            return false;
        }
        
        // Odbiorca
        $mail->addAddress($to, $toName);
        
        // Reply-To
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }
        
        // Treść
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlContent;
        
        // Alternatywna wersja tekstowa (bez HTML)
        $mail->AltBody = strip_tags($htmlContent);
        
        // Wyślij
        $mail->send();
        error_log("✓ Email wysłany do: $to");
        return true;
        
    } catch (Exception $e) {
        error_log("⚠ Błąd wysyłki emaila do $to: " . $e->getMessage());
        if (isset($mail)) {
            error_log("⚠ PHPMailer Error: " . $mail->ErrorInfo);
        }
        return false;
    }
}