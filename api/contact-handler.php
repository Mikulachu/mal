<?php
session_start();
/**
 * CONTACT-HANDLER.PHP - API do obsługi formularza kontaktowego
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

// Obsługa tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// ============================================
// SANITYZACJA I WALIDACJA DANYCH
// ============================================

$typ = sanitizeInput($_POST['typ'] ?? '');
$typ_uslugi = sanitizeInput($_POST['typ_uslugi'] ?? '');
$imie = sanitizeInput($_POST['imie'] ?? '');
$nazwisko = sanitizeInput($_POST['nazwisko'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$telefon = sanitizeInput($_POST['telefon'] ?? '');
$wiadomosc = sanitizeInput($_POST['wiadomosc'] ?? '');
$kalkulacja_cennika = sanitizeInput($_POST['kalkulacja_cennika'] ?? '');
$zrodlo_form = sanitizeInput($_POST['zrodlo'] ?? '');
$zgoda_rodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
$zgoda_marketing = isset($_POST['zgoda_marketing']) ? 1 : 0;

// Walidacja wymaganych pól
$errors = [];

if (empty($typ)) {
    $errors[] = 'Typ zapytania jest wymagany';
}

if (empty($imie)) {
    $errors[] = 'Imię jest wymagane';
}

if (empty($email)) {
    $errors[] = 'Email jest wymagany';
} elseif (!validateEmail($email)) {
    $errors[] = 'Podaj prawidłowy adres e-mail';
}

if (!empty($telefon) && !validatePhone($telefon)) {
    $errors[] = 'Podaj prawidłowy numer telefonu';
}

if (empty($wiadomosc)) {
    $errors[] = 'Wiadomość jest wymagana';
} elseif (strlen($wiadomosc) < 10) {
    $errors[] = 'Wiadomość jest za krótka (minimum 10 znaków)';
}

if (!$zgoda_rodo) {
    $errors[] = 'Musisz zaakceptować politykę prywatności';
}

// Jeśli są błędy - zwróć je
if (!empty($errors)) {
    jsonResponse([
        'success' => false,
        'message' => implode(', ', $errors),
        'errors' => $errors
    ], 400);
}

// ============================================
// ZAPIS DO BAZY
// ============================================

// Określ źródło zapytania
$zrodlo = $zrodlo_form ?: 'formularz_kontaktowy';
if (!$zrodlo_form && $typ === 'konsultacja') {
    $zrodlo = 'konsultacja_online';
}

$data = [
    'imie' => $imie,
    'nazwisko' => $nazwisko,
    'email' => $email,
    'telefon' => $telefon,
    'typ_uslugi' => $typ_uslugi,
    'wiadomosc' => $wiadomosc,
    'kalkulacja_cennika' => $kalkulacja_cennika,
    'zgoda_marketing' => $zgoda_marketing,
    'zgoda_rodo' => $zgoda_rodo,
    'zrodlo' => $zrodlo
];

$saved = saveLead($data);

if (!$saved) {
    jsonResponse([
        'success' => false,
        'message' => 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie lub zadzwoń.'
    ], 500);
}

// ============================================
// WYSYŁKA EMAILI
// ============================================

// Email do klienta (potwierdzenie)
sendClientConfirmation($imie, $email, $typ);

// Email do firmy (notyfikacja o nowym zapytaniu)
sendInternalNotification($data, $typ);

// ============================================
// RESET LIMITU KALKULATORA
// ============================================

// Reset licznika kalkulatora w sesji (stary system - kompatybilność wsteczna)
if (isset($_SESSION['calculator_usage_count'])) {
    $_SESSION['calculator_usage_count'] = 0;
    error_log('Calculator limit reset for user after contact form submission');
}
$_SESSION['calculator_unlocked'] = true;

// ============================================
// SUKCES
// ============================================

jsonResponse([
    'success' => true,
    'message' => 'Dziękujemy! Twoja wiadomość została wysłana.',
    'calculator_reset' => true
]);


// ============================================
// FUNKCJE POMOCNICZE
// ============================================

/**
 * Wyślij email potwierdzający do klienta
 */
function sendClientConfirmation($imie, $email, $typ) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
    
    $subject = "Potwierdzenie otrzymania wiadomości - {$companyName}";
    
    $typText = [
        'wycena' => 'zapytanie o wycenę',
        'konsultacja' => 'prośbę o konsultację online',
        'pytanie' => 'pytanie'
    ];
    
    $typDisplay = $typText[$typ] ?? 'wiadomość';
    
    $message = "
Cześć {$imie},

Dziękujemy za {$typDisplay}!

Otrzymaliśmy Twoją wiadomość i odezwiemy się w ciągu 24 godzin.

W razie pilnych spraw możesz do nas zadzwonić:
📞 {$companyPhone} (Pon-Pt: 8:00-18:00, Sob: 9:00-14:00)

Pozdrawiamy,
Zespół {$companyName}

---
To jest automatyczna wiadomość. Prosimy na nią nie odpowiadać.
Jeśli chcesz się z nami skontaktować, napisz na: {$companyEmail}
    ";
    
    $headers = [
        "From: {$companyName} <{$companyEmail}>",
        "Reply-To: {$companyEmail}",
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Wyślij notyfikację do firmy o nowym zapytaniu
 */
function sendInternalNotification($data, $typ) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'maltechnik.chojnice@gmail.com';
    $emailOnLead = $settings['email_on_lead'] ?? '1';
    
    // Sprawdź czy wysyłać powiadomienia o leadach
    if ($emailOnLead != '1') {
        return; // Nie wysyłaj jeśli wyłączone
    }
    
    $subject = '🔔 Nowe zapytanie z formularza kontaktowego';
    
    $typText = [
        'wycena' => 'Wycena',
        'konsultacja' => 'Konsultacja online',
        'pytanie' => 'Pytanie'
    ];
    
    $typDisplay = $typText[$typ] ?? 'Inne';
    
    // Jeśli jest kalkulacja cennika - pokaż ją osobno
    $kalkulacjaSection = '';
    if (!empty($data['kalkulacja_cennika'])) {
        $kalkulacjaSection = "
--- KALKULACJA Z CENNIKA ---
{$data['kalkulacja_cennika']}
";
    }
    
    $message = "
NOWE ZAPYTANIE Z FORMULARZA KONTAKTOWEGO

Typ: {$typDisplay}
Usługa: {$data['typ_uslugi']}

--- DANE KLIENTA ---
Imię: {$data['imie']} {$data['nazwisko']}
Email: {$data['email']}
Telefon: {$data['telefon']}
{$kalkulacjaSection}
--- WIADOMOŚĆ KLIENTA ---
{$data['wiadomosc']}

--- ZGODY ---
RODO: " . ($data['zgoda_rodo'] ? 'TAK' : 'NIE') . "
Marketing: " . ($data['zgoda_marketing'] ? 'TAK' : 'NIE') . "

--- DANE TECHNICZNE ---
IP: " . getUserIP() . "
Data: " . date('Y-m-d H:i:s') . "
Źródło: {$data['zrodlo']}

---
Odpowiedz klientowi w ciągu 24 godzin.
    ";
    
    $headers = [
        "From: Formularz {$companyName} <noreply@maltechnik.pl>",
        'Reply-To: ' . $data['email'],
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Wyślij do notification_email z ustawień
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}
?>