<?php
/**
 * SEND-QUOTE.PHP - Wysyłanie wyceny na email z PHPMailer
 * Lokalizacja: /api/send-quote.php
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/email-helpers.php';

header('Content-Type: application/json');

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda niedozwolona']);
    exit;
}

try {
    // Pobierz dane
    $email = trim($_POST['email'] ?? '');
    $imie = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $calculationData = trim($_POST['calculation_data'] ?? '');
    $zgodaRodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
    $zgodaMarketing = isset($_POST['zgoda_marketing']) ? 1 : 0;
    
    // Walidacja
    if (empty($email)) {
        throw new Exception('Email jest wymagany');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Nieprawidłowy format email');
    }
    
    if (empty($imie)) {
        throw new Exception('Imię jest wymagane');
    }
    
    if (empty($calculationData)) {
        throw new Exception('Brak danych kalkulacji');
    }
    
    if (!$zgodaRodo) {
        throw new Exception('Musisz zaakceptować politykę prywatności');
    }
    
    // Parsuj dane kalkulacji
    $calcData = json_decode($calculationData, true);
    
    if (!$calcData) {
        throw new Exception('Błędne dane kalkulacji');
    }
    
    $fullName = $imie . ($nazwisko ? ' ' . $nazwisko : '');
    
    // Zapisz do bazy leads
    $sql = "INSERT INTO leads 
            (name, email, phone, service_type, message, status, priority, source, created_at, updated_at) 
            VALUES (:name, :email, :phone, 'wycena', :message, 'new', 'high', 'calculator_quote', NOW(), NOW())";
    
    $message = "WYCENA Z KALKULATORA\n\nStandard: " . $calcData['standard'] . "\n\n";
    foreach ($calcData['services'] as $service) {
        $message .= sprintf(
            "%s: %.2f m² × %.2f zł/m² = %.2f zł\n",
            $service['name'],
            $service['meters'],
            $service['price_per_m2'],
            $service['total']
        );
    }
    $message .= "\nRAZEM: " . number_format($calcData['total'], 2, ',', ' ') . " zł";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $fullName,
        'email' => $email,
        'phone' => $telefon,
        'message' => $message
    ]);
    
    $leadId = $pdo->lastInsertId();
    
    // Zapisz zgodę marketingową
    if ($zgodaMarketing) {
        try {
            $stmtConsent = $pdo->prepare("
                INSERT INTO marketing_consents 
                (email, source, consent_marketing, additional_data, subscribed_at, ip_address, user_agent, status) 
                VALUES (?, 'quote', 1, ?, NOW(), ?, ?, 'active')
                ON DUPLICATE KEY UPDATE 
                    consent_marketing = 1,
                    additional_data = VALUES(additional_data),
                    subscribed_at = NOW()
            ");
            
            $additionalData = json_encode([
                'name' => $fullName,
                'phone' => $telefon,
                'total_price' => $calcData['total'],
                'services' => array_column($calcData['services'], 'name'),
                'quote_date' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmtConsent->execute([$email, $additionalData, $ipAddress, $userAgent]);
        } catch (PDOException $e) {
            error_log("⚠ Błąd zapisu zgody: " . $e->getMessage());
        }
    }
    
    // Wyślij email HTML do klienta
    sendQuoteEmailToClient($email, $fullName, $calcData, $leadId);
    
    // Wyślij notyfikację do admina
    sendQuoteNotificationToAdmin($email, $fullName, $telefon, $calcData, $leadId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Wycena została wysłana na Twój email',
        'lead_id' => $leadId
    ]);
    
} catch (Exception $e) {
    error_log('Send quote error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Wyślij wycenę HTML do klienta przez PHPMailer
 */
function sendQuoteEmailToClient($email, $name, $calcData, $leadId) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    
    $firstName = explode(' ', $name)[0];
    
    // Generuj wiersze tabeli
    $itemsHTML = '';
    foreach ($calcData['services'] as $service) {
        $itemsHTML .= '
        <tr>
            <td style="padding: 15px 12px; border-bottom: 1px solid #e5e7eb; color: #374151;">' . htmlspecialchars($service['name']) . '</td>
            <td style="padding: 15px 12px; border-bottom: 1px solid #e5e7eb; text-align: center; color: #6b7280;">' . number_format($service['meters'], 2, ',', ' ') . ' m²</td>
            <td style="padding: 15px 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #6b7280;">' . number_format($service['price_per_m2'], 2, ',', ' ') . ' zł/m²</td>
            <td style="padding: 15px 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #111827;">' . number_format($service['total'], 2, ',', ' ') . ' zł</td>
        </tr>';
    }
    
    $standardLabel = $calcData['standard'] === 'premium' ? 'Premium' : 'Standard';
    
    // Treść emaila
    $content = '
    <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 24px; font-weight: 700;">Cześć ' . htmlspecialchars($firstName) . '!</h2>
    <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px; line-height: 1.6;">
        Dziękujemy za skorzystanie z naszego kalkulatora! Poniżej znajdziesz szczegółowe zestawienie wybranych usług.
    </p>
    
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 5px 0; color: #374151; font-size: 18px; font-weight: 600;">Wybrane usługi</h3>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">Standard: <strong style="color: #2B59A6;">' . $standardLabel . '</strong></p>
    </div>
    
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; margin-bottom: 25px;">
        <thead>
            <tr style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%);">
                <th style="padding: 15px 12px; text-align: left; color: #ffffff; font-weight: 600; font-size: 14px;">Usługa</th>
                <th style="padding: 15px 12px; text-align: center; color: #ffffff; font-weight: 600; font-size: 14px;">Metraż</th>
                <th style="padding: 15px 12px; text-align: right; color: #ffffff; font-weight: 600; font-size: 14px;">Cena/m²</th>
                <th style="padding: 15px 12px; text-align: right; color: #ffffff; font-weight: 600; font-size: 14px;">Kwota</th>
            </tr>
        </thead>
        <tbody>
            ' . $itemsHTML . '
        </tbody>
        <tfoot>
            <tr style="background: #f9fafb;">
                <td colspan="3" style="padding: 20px 12px; text-align: right; font-size: 18px; font-weight: 600; color: #111827;">RAZEM:</td>
                <td style="padding: 20px 12px; text-align: right; font-size: 26px; font-weight: 700; color: #2B59A6;">' . number_format($calcData['total'], 2, ',', ' ') . ' zł</td>
            </tr>
        </tfoot>
    </table>
    
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">⚠️ Ważne:</strong>
            Kwoty są orientacyjne. Ostateczna cena może się zmienić po bezpłatnej ocenie na miejscu (stan podłoża, naprawy, dostęp, zabezpieczenia, technologia, materiały).
        </p>
    </div>
    
    <div style="text-align: center; margin: 35px 0;">
        <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #111827;">Chcesz uzyskać dokładną wycenę?</p>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6b7280;">Skontaktuj się z nami, a umówimy bezpłatną wizytę i wycenę!</p>
        <table cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td style="border-radius: 6px; background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%);">
                    <a href="https://www.maltechnik.pl/kontakt.php" style="display: inline-block; padding: 14px 32px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px;">
                        Skontaktuj się z nami
                    </a>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin-top: 30px;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e3a8a; text-align: center;">
            <strong>📱 Szybki kontakt:</strong>
        </p>
        <p style="margin: 0; font-size: 14px; color: #1e40af; text-align: center;">
            Telefon: <a href="tel:' . str_replace(' ', '', $companyPhone) . '" style="color: #2B59A6; text-decoration: none; font-weight: 600;">' . htmlspecialchars($companyPhone) . '</a>
        </p>
    </div>
    
    <p style="margin: 30px 0 0 0; font-size: 12px; color: #9ca3af; text-align: center;">
        Wycena #' . $leadId . ' | ' . date('d.m.Y H:i') . '
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, 'Twoja orientacyjna wycena');
    
    return sendHTMLEmail(
        $email,
        $name,
        "Twoja wycena - {$companyName}",
        $htmlEmail
    );
}

/**
 * Notyfikacja do admina
 */
function sendQuoteNotificationToAdmin($email, $name, $telefon, $calcData, $leadId) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'info@maltechnik.pl';
    $emailOnCalculation = $settings['email_on_calculation'] ?? '0';
    
    // Sprawdź czy wysyłać powiadomienia
    if ($emailOnCalculation != '1') {
        return;
    }
    
    $itemsList = '';
    foreach ($calcData['services'] as $service) {
        $itemsList .= sprintf(
            "- %s: %.2f m² × %.2f zł/m² = %.2f zł\n",
            $service['name'],
            $service['meters'],
            $service['price_per_m2'],
            $service['total']
        );
    }
    
    $content = '
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
        <p style="margin: 0; font-size: 16px; color: #92400e; font-weight: 600;">
            🔥 GORĄCY LEAD - Klient wypełnił kalkulator i podał email!
        </p>
    </div>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Dane klienta:</h3>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Imię:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . htmlspecialchars($name) . '</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Email:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #2B59A6; text-decoration: none;">' . htmlspecialchars($email) . '</a></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Telefon:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . ($telefon ?: '(nie podano)') . '</td>
        </tr>
    </table>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Wycena:</h3>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0 0 5px 0; color: #6b7280; font-size: 14px;">Standard: <strong style="color: #2B59A6;">' . htmlspecialchars($calcData['standard']) . '</strong></p>
        <pre style="font-family: monospace; font-size: 13px; color: #374151; line-height: 1.8; margin: 15px 0 0 0; white-space: pre-wrap;">' . htmlspecialchars($itemsList) . '</pre>
        <p style="margin: 15px 0 0 0; font-size: 18px; font-weight: 700; color: #2B59A6;">RAZEM: ' . number_format($calcData['total'], 2, ',', ' ') . ' zł</p>
    </div>
    
    <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">✅ Następne kroki:</strong>
            Skontaktuj się z klientem w ciągu 24h i umów bezpłatną wizytę!<br>
            Wycena HTML została automatycznie wysłana na podany email.
        </p>
    </div>
    
    <p style="margin: 25px 0 0 0; font-size: 12px; color: #9ca3af;">
        Lead #' . $leadId . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' . date('Y-m-d H:i:s') . '
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, '💰 Nowa wycena z kalkulatora');
    
    return sendHTMLEmail(
        $notificationEmail,
        $companyName,
        "💰 Nowa wycena z kalkulatora - #{$leadId}",
        $htmlEmail,
        $email  // Reply-To klienta
    );
}