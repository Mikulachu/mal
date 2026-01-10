<?php
/**
 * PROCESS-CONTACT.PHP - Obsługa formularza kontaktowego
 * Konsultacja → consultations
 * Pytanie → leads
 */

// Debug logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // NIE pokazuj błędów w JSON
ini_set('log_errors', 1);
error_log("=== CONTACT FORM START ===");
error_log("POST data: " . print_r($_POST, true));

require_once 'includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda']);
    exit;
}

// ============================================
// POBIERZ I WALIDUJ DANE
// ============================================

$typ = trim($_POST['typ'] ?? '');
$imie = trim($_POST['imie'] ?? '');
$nazwisko = trim($_POST['nazwisko'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefon = trim($_POST['telefon'] ?? '');
$temat = trim($_POST['temat'] ?? '');
$pytanie = trim($_POST['pytanie'] ?? '');
$zgoda_rodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
$zgoda_marketing = isset($_POST['zgoda_marketing']) ? 1 : 0;

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
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ]);
    exit;
}

// ============================================
// ZAPIS DO BAZY
// ============================================

try {
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
        
        // Email do klienta
        sendClientEmail($fullName, $email, $typ, $temat);
        
        // Email do admina
        sendAdminEmailConsultation($fullName, $email, $telefon, $temat);
        
        $pdo->commit();
        
        echo json_encode([
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
        
        // Email do klienta
        sendClientEmail($fullName, $email, $typ, $pytanie);
        
        // Email do admina
        sendAdminEmailLead($fullName, $email, $telefon, $pytanie);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Dziękujemy! Otrzymaliśmy Twoje pytanie. Odpowiemy w ciągu 24 godzin.',
            'type' => 'pytanie',
            'id' => $insertId
        ]);
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("❌ Błąd PDO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie lub zadzwoń.',
        'error' => $e->getMessage() // DEBUG - usuń w produkcji
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("❌ Błąd ogólny: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił błąd. Spróbuj ponownie lub zadzwoń.',
        'error' => $e->getMessage() // DEBUG - usuń w produkcji
    ]);
}

// ============================================
// FUNKCJE WYSYŁKI EMAILI
// ============================================

/**
 * Email do klienta (potwierdzenie)
 */
function sendClientEmail($name, $email, $typ, $content) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 123 456 789';
    $companyEmail = $settings['company_email'] ?? 'kontakt@example.pl';
    
    $firstName = explode(' ', $name)[0];
    
    if ($typ === 'konsultacja') {
        $subject = 'Potwierdzenie - Konsultacja online';
        $message = "
Cześć {$firstName},

Dziękujemy za zgłoszenie do konsultacji online!

Temat konsultacji: {$content}

Skontaktujemy się z Tobą w ciągu 24 godzin, aby ustalić termin rozmowy.

W razie pilnych spraw możesz do nas zadzwonić:
📞 {$companyPhone} (Pon-Pt: 8:00-18:00, Sob: 9:00-14:00)

Pozdrawiamy,
Zespół {$companyName}

---
To jest automatyczna wiadomość. Prosimy na nią nie odpowiadać.
";
    } else {
        $subject = 'Potwierdzenie - Otrzymaliśmy Twoje pytanie';
        $message = "
Cześć {$firstName},

Dziękujemy za Twoje pytanie!

Otrzymaliśmy Twoją wiadomość i odpowiemy w ciągu 24 godzin.

W razie pilnych spraw możesz do nas zadzwonić:
📞 {$companyPhone} (Pon-Pt: 8:00-18:00, Sob: 9:00-14:00)

Pozdrawiamy,
Zespół {$companyName}

---
To jest automatyczna wiadomość. Prosimy na nią nie odpowiadać.
";
    }
    
    $headers = [
        "From: {$companyName} <noreply@example.pl>",
        "Reply-To: {$companyEmail}",
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Email do admina - KONSULTACJA
 */
function sendAdminEmailConsultation($name, $email, $phone, $topic) {
    $settings = getSettings();
    $notificationEmail = $settings['notification_email'] ?? 'kontakt@example.pl';
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    
    $subject = '📞 Nowa konsultacja online';
    
    $message = "
NOWA PROŚBA O KONSULTACJĘ ONLINE

--- DANE KLIENTA ---
Imię i nazwisko: {$name}
Email: {$email}
Telefon: {$phone}

--- TEMAT KONSULTACJI ---
{$topic}

--- AKCJA ---
Skontaktuj się z klientem w ciągu 24h i umów termin konsultacji.

Panel admin: https://yourdomain.pl/admin/consultations.php
Data zgłoszenia: " . date('Y-m-d H:i:s') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "
";
    
    $headers = [
        "From: System {$companyName} <noreply@example.pl>",
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}

/**
 * Email do admina - PYTANIE (LEAD)
 */
function sendAdminEmailLead($name, $email, $phone, $question) {
    $settings = getSettings();
    $notificationEmail = $settings['notification_email'] ?? 'kontakt@example.pl';
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    
    $subject = '❓ Nowe pytanie od klienta';
    
    $message = "
NOWE PYTANIE Z FORMULARZA KONTAKTOWEGO

--- DANE KLIENTA ---
Imię i nazwisko: {$name}
Email: {$email}
Telefon: {$phone}

--- PYTANIE ---
{$question}

--- AKCJA ---
Odpowiedz klientowi w ciągu 24h.

Panel admin: https://yourdomain.pl/admin/leads.php
Data zgłoszenia: " . date('Y-m-d H:i:s') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "
";
    
    $headers = [
        "From: System {$companyName} <noreply@example.pl>",
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}
?>