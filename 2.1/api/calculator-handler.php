<?php
/**
 * CALCULATOR-HANDLER.PHP - API do obsługi kalkulatora
 * Zaktualizowany z PHPMailer i ładnymi emailami HTML
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/email-helpers.php';

// Obsługa tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Sprawdź action
$action = $_POST['action'] ?? '';

if ($action === 'save_calculation') {
    saveCalculationHandler();
} else {
    jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

/**
 * Handler zapisu wyliczenia kalkulatora
 */
function saveCalculationHandler() {
    global $pdo;
    
    // Walidacja danych
    $typUslugi = sanitizeInput($_POST['typ_uslugi'] ?? '');
    $metraz = floatval($_POST['metraz'] ?? 0);
    $standard = sanitizeInput($_POST['standard'] ?? '');
    $dodatkowe = $_POST['dodatkowe_uslugi'] ?? '[]';
    $cenaOd = floatval($_POST['cena_od'] ?? 0);
    $cenaDo = floatval($_POST['cena_do'] ?? 0);
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : null;
    $zgoda_marketing = isset($_POST['zgoda_marketing']) ? 1 : 0;
    
    // Walidacja wymaganych pól
    if (empty($typUslugi) || $metraz <= 0 || empty($standard)) {
        jsonResponse([
            'success' => false,
            'message' => 'Nieprawidłowe dane wejściowe'
        ], 400);
    }
    
    // Walidacja emaila (jeśli podany)
    if ($email && !validateEmail($email)) {
        jsonResponse([
            'success' => false,
            'message' => 'Nieprawidłowy adres e-mail'
        ], 400);
    }
    
    // Przygotuj dane do zapisu
    $data = [
        'typ_uslugi' => $typUslugi,
        'metraz' => $metraz,
        'standard' => $standard,
        'dodatkowe_uslugi' => $dodatkowe,
        'cena_od' => $cenaOd,
        'cena_do' => $cenaDo,
        'email' => $email
    ];
    
    // Zapisz do bazy
    $saved = saveCalculatorLog($data);
    
    if ($saved) {
        // Zapisz zgodę marketingową
        if ($email && $zgoda_marketing) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO marketing_consents 
                    (email, source, consent_marketing, additional_data, subscribed_at, ip_address, user_agent, status) 
                    VALUES (?, 'calculator', 1, ?, NOW(), ?, ?, 'active')
                    ON DUPLICATE KEY UPDATE 
                        consent_marketing = 1,
                        additional_data = VALUES(additional_data),
                        subscribed_at = NOW()
                ");
                
                $additionalData = json_encode([
                    'service_type' => $typUslugi,
                    'meters' => $metraz,
                    'standard' => $standard,
                    'price_range' => "$cenaOd - $cenaDo zł",
                    'calculation_date' => date('Y-m-d H:i:s')
                ], JSON_UNESCAPED_UNICODE);
                
                $ipAddress = getUserIP();
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                
                $stmt->execute([$email, $additionalData, $ipAddress, $userAgent]);
            } catch (PDOException $e) {
                error_log("⚠ Błąd zapisu zgody: " . $e->getMessage());
            }
        }
        
        // Jeśli podano email - wyślij wycenę
        if ($email) {
            sendCalculationEmail($email, $data);
            sendCalculationNotificationToAdmin($email, $data);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Wyliczenie zapisane'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Błąd podczas zapisywania'
        ], 500);
    }
}

/**
 * Wysyła wycenę na email przez PHPMailer (HTML)
 */
function sendCalculationEmail($email, $data) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    
    // Mapowanie nazw
    $typNazwy = [
        'elewacja' => 'Elewacja budynku',
        'wnetrze' => 'Wykończenie wnętrz',
        'remont' => 'Remont kompleksowy'
    ];
    
    $standardNazwy = [
        'podstawowy' => 'Podstawowy',
        'premium' => 'Premium',
        'lux' => 'Lux'
    ];
    
    // Dodatkowe usługi
    $dodatkowe = json_decode($data['dodatkowe_uslugi'], true);
    $dodatkoweHTML = '';
    
    if (!empty($dodatkowe)) {
        $dodatkoweNames = [
            'projekt' => 'Projekt i wizualizacje',
            'koordynacja' => 'Koordynacja branż',
            'premium_pakiet' => 'Pakiet premium',
            'ekspresowa' => 'Realizacja ekspresowa'
        ];
        
        $dodatkoweHTML = '<div style="background: #eff6ff; padding: 15px; border-radius: 6px; margin-top: 15px;">
            <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #1e40af;">Dodatkowe usługi:</p>
            <ul style="margin: 0; padding-left: 20px; color: #1e3a8a; font-size: 14px;">';
        
        foreach ($dodatkowe as $item) {
            $dodatkoweHTML .= '<li>' . ($dodatkoweNames[$item] ?? $item) . '</li>';
        }
        
        $dodatkoweHTML .= '</ul></div>';
    }
    
    // Treść emaila
    $content = '
    <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 24px; font-weight: 700;">Twoja orientacyjna wycena</h2>
    <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px; line-height: 1.6;">
        Dziękujemy za skorzystanie z naszego kalkulatora! Poniżej znajdziesz orientacyjną wycenę dla wybranych parametrów.
    </p>
    
    <div style="background: #f9fafb; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td style="padding: 10px 0; color: #6b7280; font-size: 14px; width: 40%;"><strong style="color: #111827;">Typ usługi:</strong></td>
                <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . htmlspecialchars($typNazwy[$data['typ_uslugi']] ?? $data['typ_uslugi']) . '</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Powierzchnia:</strong></td>
                <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . number_format($data['metraz'], 2, ',', ' ') . ' m²</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Standard:</strong></td>
                <td style="padding: 10px 0; color: #374151; font-size: 14px;"><strong style="color: #2B59A6;">' . htmlspecialchars($standardNazwy[$data['standard']] ?? $data['standard']) . '</strong></td>
            </tr>
        </table>
        ' . $dodatkoweHTML . '
    </div>
    
    <div style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%); padding: 30px; border-radius: 8px; text-align: center; margin-bottom: 25px;">
        <p style="margin: 0 0 5px 0; color: rgba(255,255,255,0.9); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Orientacyjny koszt</p>
        <p style="margin: 0; color: #ffffff; font-size: 36px; font-weight: 700;">' . number_format($data['cena_od'], 0, ',', ' ') . ' - ' . number_format($data['cena_do'], 0, ',', ' ') . ' zł</p>
    </div>
    
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">⚠️ Ważne:</strong>
            To wycena orientacyjna. Aby otrzymać dokładną wycenę dopasowaną do Twojego projektu, skontaktuj się z nami. 
            Oferujemy bezpłatną wizytę i szczegółową wycenę na miejscu.
        </p>
    </div>
    
    <div style="text-align: center; margin: 35px 0;">
        <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #111827;">Chcesz uzyskać dokładną wycenę?</p>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6b7280;">Skontaktuj się z nami już dziś!</p>
        <table cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td style="border-radius: 6px; background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%);">
                    <a href="https://www.maltechnik.pl/kontakt.php" style="display: inline-block; padding: 14px 32px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px;">
                        Umów bezpłatną wycenę
                    </a>
                </td>
            </tr>
        </table>
    </div>
    ';
    
    $htmlEmail = getEmailTemplate($content, 'Twoja orientacyjna wycena');
    
    return sendHTMLEmail(
        $email,
        '',
        "Twoja wycena z kalkulatora - {$companyName}",
        $htmlEmail
    );
}

/**
 * Wysyła notyfikację do admina
 */
function sendCalculationNotificationToAdmin($email, $data) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'info@maltechnik.pl';
    $emailOnCalculation = $settings['email_on_calculation'] ?? '0';
    
    if ($emailOnCalculation != '1') {
        return;
    }
    
    $typNazwy = ['elewacja' => 'Elewacja budynku', 'wnetrze' => 'Wykończenie wnętrz', 'remont' => 'Remont kompleksowy'];
    $standardNazwy = ['podstawowy' => 'Podstawowy', 'premium' => 'Premium', 'lux' => 'Lux'];
    
    $content = '
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
        <p style="margin: 0; font-size: 16px; color: #92400e; font-weight: 600;">
            🔥 GORĄCY LEAD - Klient użył kalkulatora i podał email!
        </p>
    </div>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Email klienta:</h3>
    <p style="margin: 0 0 25px 0;"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #2B59A6; text-decoration: none; font-size: 16px; font-weight: 600;">' . htmlspecialchars($email) . '</a></p>
    
    <div style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%); padding: 25px; border-radius: 8px; text-align: center; color: white;">
        <p style="margin: 0 0 5px 0; font-size: 13px; opacity: 0.9;">Orientacyjna wycena</p>
        <p style="margin: 0; font-size: 28px; font-weight: 700;">' . number_format($data['cena_od'], 0, ',', ' ') . ' - ' . number_format($data['cena_do'], 0, ',', ' ') . ' zł</p>
    </div>
    ';
    
    $htmlEmail = getEmailTemplate($content, '📊 Nowa wycena z kalkulatora');
    
    return sendHTMLEmail($notificationEmail, $companyName, "📊 Nowa wycena z kalkulatora", $htmlEmail, $email);
}