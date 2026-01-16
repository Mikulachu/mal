<?php
/**
 * PROCESS-CONTACT.PHP - Obsługa formularza kontaktowego z PHPMailer i HTML
 * Konsultacja → consultations
 * Pytanie → leads
 * Konfiguracja SMTP z includes/db.php
 */

// Wyłącz wyświetlanie błędów
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Ustaw header na początku
header('Content-Type: application/json; charset=utf-8');

// Funkcja do bezpiecznego zwrócenia JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Sprawdź metodę
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Nieprawidłowa metoda'], 405);
}

// Załaduj zależności
try {
    if (!file_exists(__DIR__ . '/includes/db.php')) {
        throw new Exception('Brak pliku db.php');
    }
    if (!file_exists(__DIR__ . '/includes/functions.php')) {
        throw new Exception('Brak pliku functions.php');
    }
    if (!file_exists(__DIR__ . '/includes/email-helpers.php')) {
        throw new Exception('Brak pliku email-helpers.php');
    }
    
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/email-helpers.php';
    
} catch (Exception $e) {
    error_log("Błąd ładowania plików: " . $e->getMessage());
    sendJsonResponse([
        'success' => false, 
        'message' => 'Błąd konfiguracji serwera. Skontaktuj się z administratorem.'
    ], 500);
}

// ============================================
// POBIERZ I WALIDUJ DANE
// ============================================

$typ = isset($_POST['typ']) ? trim($_POST['typ']) : '';
$imie = isset($_POST['imie']) ? trim($_POST['imie']) : '';
$nazwisko = isset($_POST['nazwisko']) ? trim($_POST['nazwisko']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
$temat = isset($_POST['temat']) ? trim($_POST['temat']) : '';
$pytanie = isset($_POST['pytanie']) ? trim($_POST['pytanie']) : '';
$zgoda_rodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
$zgoda_marketing = isset($_POST['zgoda_marketing']) ? 1 : 0;

// Log dla debugowania
error_log("=== CONTACT FORM START ===");
error_log("Typ: $typ, Imie: $imie, Email: $email");

// Walidacja
$errors = [];

if (empty($typ) || !in_array($typ, ['konsultacja', 'pytanie'])) {
    $errors[] = 'Wybierz typ zapytania';
}

if (empty($imie)) {
    $errors[] = 'Imię jest wymagane';
}

if (empty($email)) {
    $errors[] = 'Email jest wymagany';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Podaj prawidłowy adres email';
}

if ($typ === 'konsultacja' && empty($temat)) {
    $errors[] = 'Temat konsultacji jest wymagany';
}

if ($typ === 'pytanie' && empty($pytanie)) {
    $errors[] = 'Pytanie jest wymagane';
}

if (!$zgoda_rodo) {
    $errors[] = 'Musisz zaakceptować politykę prywatności';
}

// Jeśli są błędy
if (!empty($errors)) {
    error_log("Błędy walidacji: " . implode(', ', $errors));
    sendJsonResponse([
        'success' => false,
        'message' => implode(', ', $errors)
    ], 400);
}

// ============================================
// ZAPIS DO BAZY
// ============================================

try {
    // Sprawdź czy $pdo istnieje
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Brak połączenia z bazą danych');
    }
    
    error_log("Rozpoczynam zapis do bazy. Typ: $typ");
    $pdo->beginTransaction();
    
    if ($typ === 'konsultacja') {
        // KONSULTACJA → consultations
        $stmt = $pdo->prepare("
            INSERT INTO consultations (
                name, email, phone, topic, 
                status, created_at
            ) VALUES (?, ?, ?, ?, 'new', NOW())
        ");
        
        $fullName = trim($imie . ' ' . $nazwisko);
        $stmt->execute([
            $fullName,
            $email,
            $telefon,
            $temat
        ]);
        
        $insertId = $pdo->lastInsertId();
        error_log("✓ Zapisano konsultację, ID: $insertId");
        
        // Zapisz zgodę marketingową
        if ($zgoda_marketing) {
            saveMarketingConsent($pdo, $email, $fullName, 'konsultacja', [
                'phone' => $telefon,
                'topic' => $temat
            ]);
        }
        
        // Commit PRZED wysyłką emaili
        $pdo->commit();
        
        // Email do klienta (HTML)
        sendConsultationEmailToClient($email, $fullName, $temat);
        
        // Email do admina (HTML)
        sendConsultationEmailToAdmin($fullName, $email, $telefon, $temat);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Dziękujemy! Otrzymaliśmy Twoją prośbę o konsultację. Skontaktujemy się wkrótce.',
            'type' => 'konsultacja',
            'id' => $insertId
        ]);
        
    } else {
        // PYTANIE → leads
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                name, email, phone, message, 
                source, status, created_at
            ) VALUES (?, ?, ?, ?, 'website', 'new', NOW())
        ");
        
        $fullName = trim($imie . ' ' . $nazwisko);
        $stmt->execute([
            $fullName,
            $email,
            $telefon,
            $pytanie
        ]);
        
        $insertId = $pdo->lastInsertId();
        error_log("✓ Zapisano pytanie (lead), ID: $insertId");
        
        // Zapisz zgodę marketingową
        if ($zgoda_marketing) {
            saveMarketingConsent($pdo, $email, $fullName, 'pytanie', [
                'phone' => $telefon,
                'message_preview' => substr($pytanie, 0, 100)
            ]);
        }
        
        // Commit PRZED wysyłką emaili
        $pdo->commit();
        
        // Email do klienta (HTML)
        sendQuestionEmailToClient($email, $fullName, $pytanie);
        
        // Email do admina (HTML)
        sendQuestionEmailToAdmin($fullName, $email, $telefon, $pytanie);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Dziękujemy! Otrzymaliśmy Twoje pytanie. Odpowiemy w ciągu 24 godzin.',
            'type' => 'pytanie',
            'id' => $insertId
        ]);
    }
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("❌ Błąd PDO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie lub zadzwoń.'
    ], 500);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("❌ Błąd ogólny: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Wystąpił błąd. Spróbuj ponownie lub zadzwoń.'
    ], 500);
}

// ============================================
// FUNKCJE POMOCNICZE
// ============================================

/**
 * Zapisz zgodę marketingową
 */
function saveMarketingConsent($pdo, $email, $name, $type, $additionalData = []) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO marketing_consents 
            (email, source, consent_marketing, additional_data, subscribed_at, ip_address, user_agent, status) 
            VALUES (?, 'contact', 1, ?, NOW(), ?, ?, 'active')
            ON DUPLICATE KEY UPDATE 
                consent_marketing = 1,
                additional_data = VALUES(additional_data),
                subscribed_at = NOW()
        ");
        
        $additionalData['name'] = $name;
        $additionalData['type'] = $type;
        
        $jsonData = json_encode($additionalData, JSON_UNESCAPED_UNICODE);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt->execute([$email, $jsonData, $ipAddress, $userAgent]);
        error_log("✓ Zapisano zgodę marketingową");
        
    } catch (PDOException $e) {
        error_log("⚠ Błąd zapisu zgody: " . $e->getMessage());
    }
}

/**
 * Email HTML do klienta - Konsultacja
 */
function sendConsultationEmailToClient($email, $name, $topic) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    
    $firstName = explode(' ', $name)[0];
    
    $content = '
    <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 24px;">Cześć ' . htmlspecialchars($firstName) . '!</h2>
    <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px; line-height: 1.6;">
        Dziękujemy za zgłoszenie do konsultacji online!
    </p>
    
    <div style="background: #eff6ff; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0 0 8px 0; color: #1e40af; font-size: 14px; font-weight: 600;">Temat konsultacji:</p>
        <p style="margin: 0; color: #1e3a8a; font-size: 16px; font-weight: 500;">' . htmlspecialchars($topic) . '</p>
    </div>
    
    <p style="margin: 0 0 20px 0; color: #374151; font-size: 15px; line-height: 1.6;">
        Skontaktujemy się z Tobą w ciągu <strong>24 godzin</strong>, aby ustalić termin rozmowy.
    </p>
    
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px;">⏰ W razie pilnych spraw:</strong>
            Możesz do nas zadzwonić:<br>
            <strong style="font-size: 16px; color: #2B59A6;">' . htmlspecialchars($companyPhone) . '</strong><br>
            <span style="font-size: 13px;">Pon-Pt: 8:00-18:00, Sob: 9:00-14:00</span>
        </p>
    </div>
    
    <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
        Pozdrawiamy serdecznie,<br>
        <strong style="color: #2B59A6;">Zespół ' . htmlspecialchars($companyName) . '</strong>
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, 'Potwierdzenie - Konsultacja online');
    
    return sendHTMLEmail(
        $email,
        $name,
        "Potwierdzenie konsultacji - {$companyName}",
        $htmlEmail
    );
}

/**
 * Email HTML do admina - Konsultacja
 */
function sendConsultationEmailToAdmin($name, $email, $phone, $topic) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'info@maltechnik.pl';
    
    $content = '
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
        <p style="margin: 0; font-size: 16px; color: #92400e; font-weight: 600;">
            📞 NOWA PROŚBA O KONSULTACJĘ ONLINE
        </p>
    </div>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Dane klienta:</h3>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px; width: 35%;"><strong style="color: #111827;">Imię i nazwisko:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . htmlspecialchars($name) . '</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Email:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #2B59A6; text-decoration: none;">' . htmlspecialchars($email) . '</a></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Telefon:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><strong style="color: #2B59A6;">' . htmlspecialchars($phone) . '</strong></td>
        </tr>
    </table>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Temat konsultacji:</h3>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0; color: #374151; font-size: 15px; line-height: 1.6;">' . nl2br(htmlspecialchars($topic)) . '</p>
    </div>
    
    <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">✅ AKCJA:</strong>
            Skontaktuj się z klientem w ciągu 24h i umów termin konsultacji!
        </p>
    </div>
    
    <p style="margin: 25px 0 0 0; font-size: 12px; color: #9ca3af;">
        Data zgłoszenia: ' . date('Y-m-d H:i:s') . '<br>
        IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, '📞 Nowa konsultacja online');
    
    return sendHTMLEmail(
        $notificationEmail,
        $companyName,
        "📞 Nowa konsultacja online",
        $htmlEmail,
        $email
    );
}

/**
 * Email HTML do klienta - Pytanie
 */
function sendQuestionEmailToClient($email, $name, $question) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    
    $firstName = explode(' ', $name)[0];
    
    $content = '
    <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 24px;">Cześć ' . htmlspecialchars($firstName) . '!</h2>
    <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px; line-height: 1.6;">
        Dziękujemy za Twoje pytanie!
    </p>
    
    <div style="background: #f9fafb; padding: 25px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e5e7eb;">
        <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Twoje pytanie:</p>
        <p style="margin: 0; color: #374151; font-size: 15px; line-height: 1.6;">' . nl2br(htmlspecialchars($question)) . '</p>
    </div>
    
    <p style="margin: 0 0 20px 0; color: #374151; font-size: 15px; line-height: 1.6;">
        Otrzymaliśmy Twoją wiadomość i <strong>odpowiemy w ciągu 24 godzin</strong>.
    </p>
    
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px;">⏰ W razie pilnych spraw:</strong>
            Możesz do nas zadzwonić:<br>
            <strong style="font-size: 16px; color: #2B59A6;">' . htmlspecialchars($companyPhone) . '</strong><br>
            <span style="font-size: 13px;">Pon-Pt: 8:00-18:00, Sob: 9:00-14:00</span>
        </p>
    </div>
    
    <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
        Pozdrawiamy serdecznie,<br>
        <strong style="color: #2B59A6;">Zespół ' . htmlspecialchars($companyName) . '</strong>
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, 'Otrzymaliśmy Twoje pytanie');
    
    return sendHTMLEmail(
        $email,
        $name,
        "Potwierdzenie - Otrzymaliśmy Twoje pytanie",
        $htmlEmail
    );
}

/**
 * Email HTML do admina - Pytanie
 */
function sendQuestionEmailToAdmin($name, $email, $phone, $question) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'info@maltechnik.pl';
    
    $content = '
    <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
        <p style="margin: 0; font-size: 16px; color: #1e40af; font-weight: 600;">
            ❓ NOWE PYTANIE OD KLIENTA
        </p>
    </div>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Dane klienta:</h3>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px; width: 35%;"><strong style="color: #111827;">Imię i nazwisko:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . htmlspecialchars($name) . '</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Email:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #2B59A6; text-decoration: none;">' . htmlspecialchars($email) . '</a></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Telefon:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><strong style="color: #2B59A6;">' . htmlspecialchars($phone) . '</strong></td>
        </tr>
    </table>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Treść pytania:</h3>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0; color: #374151; font-size: 15px; line-height: 1.6;">' . nl2br(htmlspecialchars($question)) . '</p>
    </div>
    
    <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">✅ AKCJA:</strong>
            Odpowiedz klientowi w ciągu 24h!
        </p>
    </div>
    
    <p style="margin: 25px 0 0 0; font-size: 12px; color: #9ca3af;">
        Data zgłoszenia: ' . date('Y-m-d H:i:s') . '<br>
        IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, '❓ Nowe pytanie od klienta');
    
    return sendHTMLEmail(
        $notificationEmail,
        $companyName,
        "❓ Nowe pytanie od klienta",
        $htmlEmail,
        $email
    );
}