<?php
/**
 * NEWSLETTER.PHP - API endpoint do zapisywania do newslettera
 * Z wysyłką emaila potwierdzającego i notyfikacją dla admina (HTML)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Obsługa preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metoda niedozwolona'
    ]);
    exit;
}

// Include plików
$includesPath = __DIR__ . '/../includes/';
if (!file_exists($includesPath . 'functions.php')) {
    $includesPath = __DIR__ . '/../../includes/';
}

try {
    require_once $includesPath . 'functions.php';
    require_once $includesPath . 'db.php';
    require_once $includesPath . 'email-helpers.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd konfiguracji serwera'
    ]);
    exit;
}

// Pobierz dane z POST
$email = null;

// Sprawdź Content-Type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    // JSON format
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $email = $data['email'] ?? null;
} else {
    // FormData lub x-www-form-urlencoded
    $email = $_POST['email'] ?? null;
}

// Fallback - sprawdź też raw input
if (!$email) {
    $input = file_get_contents('php://input');
    parse_str($input, $postData);
    $email = $postData['email'] ?? null;
}

if (!$email) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Brak adresu email'
    ]);
    exit;
}

$email = trim($email);

// Walidacja email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowy adres email'
    ]);
    exit;
}

try {
    // Sprawdź czy tabela istnieje
    $stmt = $pdo->query("SHOW TABLES LIKE 'marketing_consents'");
    if ($stmt->rowCount() === 0) {
        throw new Exception('Tabela marketing_consents nie istnieje.');
    }
    
    // Sprawdź czy email już ma zgodę newsletter
    $stmt = $pdo->prepare("
        SELECT id FROM marketing_consents 
        WHERE email = ? 
        AND source = 'newsletter' 
        AND status = 'active'
    ");
    $stmt->execute([$email]);
    
    $alreadySubscribed = $stmt->fetch();
    
    if ($alreadySubscribed) {
        echo json_encode([
            'success' => true,
            'message' => 'Ten adres email jest już zapisany do newslettera'
        ]);
        exit;
    }
    
    // Dodaj zgodę newsletter do bazy
    $stmt = $pdo->prepare("
        INSERT INTO marketing_consents 
        (email, source, consent_marketing, subscribed_at, ip_address, user_agent, status) 
        VALUES (?, 'newsletter', 1, NOW(), ?, ?, 'active')
        ON DUPLICATE KEY UPDATE 
            consent_marketing = 1,
            subscribed_at = NOW(),
            status = 'active'
    ");
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $stmt->execute([$email, $ipAddress, $userAgent]);
    
    // Wyślij email potwierdzający do subskrybenta (HTML)
    sendNewsletterConfirmationToClient($email);
    
    // Wyślij notyfikację do admina (HTML)
    sendNewsletterNotificationToAdmin($email);
    
    echo json_encode([
        'success' => true,
        'message' => 'Dziękujemy za zapis do newslettera! Sprawdź swoją skrzynkę.'
    ]);
    
} catch (PDOException $e) {
    error_log('Newsletter PDO error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił błąd podczas zapisu. Spróbuj ponownie.'
    ]);
} catch (Exception $e) {
    error_log('Newsletter error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Wyślij email potwierdzający do subskrybenta (HTML)
 */
function sendNewsletterConfirmationToClient($email) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    
    $content = '
    <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 24px;">Dziękujemy za zapis!</h2>
    <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px; line-height: 1.6;">
        Cieszymy się, że dołączasz do naszego newslettera!
    </p>
    
    <div style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%); padding: 30px; border-radius: 8px; text-align: center; margin-bottom: 25px;">
        <div style="font-size: 48px; margin-bottom: 15px;">✅</div>
        <p style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 600;">Subskrypcja aktywowana!</p>
    </div>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Co otrzymasz w naszym newsletterze?</h3>
    
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
        <p style="margin: 0 0 8px 0; color: #374151; font-size: 15px; line-height: 1.6;">
            <strong style="color: #2B59A6;">📰 Aktualności</strong><br>
            <span style="font-size: 14px; color: #6b7280;">Najnowsze informacje o naszych usługach i realizacjach</span>
        </p>
    </div>
    
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
        <p style="margin: 0 0 8px 0; color: #374151; font-size: 15px; line-height: 1.6;">
            <strong style="color: #2B59A6;">💡 Porady ekspertów</strong><br>
            <span style="font-size: 14px; color: #6b7280;">Praktyczne wskazówki dotyczące remontów i wykończeń</span>
        </p>
    </div>
    
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0 0 8px 0; color: #374151; font-size: 15px; line-height: 1.6;">
            <strong style="color: #2B59A6;">🎁 Promocje</strong><br>
            <span style="font-size: 14px; color: #6b7280;">Specjalne oferty tylko dla subskrybentów</span>
        </p>
    </div>
    
    <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin-bottom: 25px; text-align: center;">
        <p style="margin: 0 0 10px 0; color: #1e40af; font-size: 14px; font-weight: 600;">
            Częstotliwość wysyłki:
        </p>
        <p style="margin: 0; color: #1e3a8a; font-size: 13px;">
            Wysyłamy newsletter raz w miesiącu.<br>
            Nie spamujemy! 😊
        </p>
    </div>
    
    <p style="margin: 0 0 20px 0; color: #6b7280; font-size: 14px; line-height: 1.6; text-align: center;">
        Możesz zrezygnować z subskrypcji w dowolnym momencie,<br>
        klikając link w stopce każdego newslettera.
    </p>
    
    <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
        Pozdrawiamy serdecznie,<br>
        <strong style="color: #2B59A6;">Zespół ' . htmlspecialchars($companyName) . '</strong>
    </p>
    ';
    
    $htmlEmail = getEmailTemplate($content, 'Potwierdzenie zapisu do newslettera');
    
    return sendHTMLEmail(
        $email,
        '',
        "Witaj w newsletterze {$companyName}! ✨",
        $htmlEmail
    );
}

/**
 * Wyślij notyfikację do admina o nowym subskrybencie (HTML)
 */
function sendNewsletterNotificationToAdmin($email) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'info@maltechnik.pl';
    $emailOnNewsletter = $settings['email_on_newsletter'] ?? '0';
    
    // Sprawdź czy wysyłać powiadomienia o nowych subskrybentach
    if ($emailOnNewsletter != '1') {
        return; // Nie wysyłaj jeśli wyłączone
    }
    
    $content = '
    <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
        <p style="margin: 0; font-size: 16px; color: #1e40af; font-weight: 600;">
            📧 NOWY SUBSKRYBENT NEWSLETTERA
        </p>
    </div>
    
    <div style="background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%); padding: 25px; border-radius: 8px; text-align: center; margin-bottom: 25px;">
        <p style="margin: 0 0 10px 0; color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 600;">Nowy subskrybent:</p>
        <p style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700;">
            <a href="mailto:' . htmlspecialchars($email) . '" style="color: #ffffff; text-decoration: none;">' . htmlspecialchars($email) . '</a>
        </p>
    </div>
    
    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Szczegóły:</h3>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px; width: 30%;"><strong style="color: #111827;">Email:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #2B59A6; text-decoration: none;">' . htmlspecialchars($email) . '</a></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Źródło:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">Newsletter (footer)</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Data zapisu:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . date('Y-m-d H:i:s') . '</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">IP:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '</td>
        </tr>
    </table>
    
    <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">✅ Email potwierdzający:</strong>
            Subskrybent otrzymał automatyczny email powitalny z potwierdzeniem zapisu.
        </p>
    </div>
    ';
    
    $htmlEmail = getEmailTemplate($content, '📧 Nowy subskrybent newslettera');
    
    return sendHTMLEmail(
        $notificationEmail,
        $companyName,
        "📧 Nowy subskrybent newslettera",
        $htmlEmail
    );
}