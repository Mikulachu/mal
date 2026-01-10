<?php
/**
 * SEND-QUOTE.PHP - Wysyłanie wyceny na email
 * Lokalizacja: /api/send-quote.php
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

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
    
    // Zapisz do bazy leads (jako źródło: calculator_quote)
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
 * Wyślij wycenę HTML do klienta
 */
function sendQuoteEmailToClient($email, $name, $calcData, $leadId) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
    
    $subject = "Twoja wycena - {$companyName}";
    
    // Generuj HTML wyceny
    $itemsHTML = '';
    foreach ($calcData['services'] as $service) {
        $itemsHTML .= '
        <tr>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">' . htmlspecialchars($service['name']) . '</td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">' . number_format($service['meters'], 2, ',', ' ') . ' m²</td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: right;">' . number_format($service['price_per_m2'], 2, ',', ' ') . ' zł/m²</td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: right; font-weight: 600;">' . number_format($service['total'], 2, ',', ' ') . ' zł</td>
        </tr>';
    }
    
    $standardLabel = $calcData['standard'] === 'premium' ? 'Premium' : 'Standard';
    
    $htmlMessage = '
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twoja wycena</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">' . htmlspecialchars($companyName) . '</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Twoja orientacyjna wycena</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">Cześć ' . htmlspecialchars($name) . ',</p>
        <p style="font-size: 14px;">Dziękujemy za skorzystanie z naszego kalkulatora! Poniżej znajdziesz szczegółowe zestawienie wybranych usług.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #2c3e50; font-size: 20px;">Wybrane usługi</h2>
            <p style="margin: 5px 0 15px 0; color: #6c757d; font-size: 14px;">Standard: <strong>' . $standardLabel . '</strong></p>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #34495e; color: white;">
                        <th style="padding: 12px; text-align: left;">Usługa</th>
                        <th style="padding: 12px; text-align: center;">Metraż</th>
                        <th style="padding: 12px; text-align: right;">Cena/m²</th>
                        <th style="padding: 12px; text-align: right;">Kwota</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $itemsHTML . '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="padding: 15px 12px; text-align: right; font-size: 18px; font-weight: 600;">RAZEM:</td>
                        <td style="padding: 15px 12px; text-align: right; font-size: 24px; font-weight: 700; color: #e67e22;">' . number_format($calcData['total'], 2, ',', ' ') . ' zł</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; color: #856404;">
                <strong>Ważne:</strong> Kwoty są orientacyjne. Ostateczna cena może się zmienić po bezpłatnej ocenie na miejscu.
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <p style="font-size: 16px; font-weight: 600; color: #2c3e50;">Chcesz uzyskać dokładną wycenę?</p>
            <a href="https://maltechnik.pl/kontakt.php" style="display: inline-block; background: #e67e22; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 10px 0;">Skontaktuj się z nami</a>
        </div>
        
        <div style="border-top: 2px solid #dee2e6; padding-top: 20px; margin-top: 20px;">
            <p style="font-size: 14px; margin: 5px 0;"><strong>📞 Telefon:</strong> ' . htmlspecialchars($companyPhone) . '</p>
            <p style="font-size: 14px; margin: 5px 0;"><strong>✉️ Email:</strong> ' . htmlspecialchars($companyEmail) . '</p>
        </div>
        
        <p style="font-size: 12px; color: #6c757d; text-align: center; margin-top: 30px;">
            © 2026 ' . htmlspecialchars($companyName) . '<br>
            Wycena #' . $leadId . ' | ' . date('Y-m-d H:i') . '
        </p>
    </div>
</body>
</html>';
    
    $headers = [
        "From: {$companyName} <{$companyEmail}>",
        "Reply-To: {$companyEmail}",
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    mail($email, $subject, $htmlMessage, implode("\r\n", $headers));
}

/**
 * Notyfikacja do admina
 */
function sendQuoteNotificationToAdmin($email, $name, $telefon, $calcData, $leadId) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'maltechnik.chojnice@gmail.com';
    $emailOnCalculation = $settings['email_on_calculation'] ?? '0';
    
    // Sprawdź czy wysyłać powiadomienia
    if ($emailOnCalculation != '1') {
        return; // Nie wysyłaj jeśli wyłączone
    }
    
    $subject = '💰 Nowa wycena z kalkulatora - #' . $leadId;
    
    $itemsText = '';
    foreach ($calcData['services'] as $service) {
        $itemsText .= sprintf(
            "- %s: %.2f m² × %.2f zł/m² = %.2f zł\n",
            $service['name'],
            $service['meters'],
            $service['price_per_m2'],
            $service['total']
        );
    }
    
    $message = "
NOWA WYCENA Z KALKULATORA

Klient wypełnił kalkulator i podał swój email - GORĄCY LEAD!

--- DANE KLIENTA ---
Imię: $name
Email: $email
Telefon: " . ($telefon ?: '(nie podano)') . "

--- WYCENA ---
Standard: {$calcData['standard']}

$itemsText

RAZEM: " . number_format($calcData['total'], 2, ',', ' ') . " zł

--- DANE TECHNICZNE ---
IP: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "
Data: " . date('Y-m-d H:i:s') . "
ID: #$leadId

---
👉 Panel admin: https://maltechnik.pl/admin/lead-detail.php?id=$leadId

Skontaktuj się z klientem w ciągu 24h!
Wycena HTML została automatycznie wysłana na podany email.
    ";
    
    $headers = [
        "From: Kalkulator {$companyName} <noreply@maltechnik.pl>",
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}
?>