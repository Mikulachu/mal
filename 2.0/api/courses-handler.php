<?php
/**
 * COURSES-HANDLER.PHP - API do obsługi formularza zainteresowania kursami
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

// Obsługa tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Sprawdź action
$action = $_POST['action'] ?? '';

if ($action === 'save_course_interest') {
    saveCourseInterestHandler();
} else {
    jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

/**
 * Handler zapisu zainteresowania kursem
 */
function saveCourseInterestHandler() {
    // Walidacja danych
    $imie = sanitizeInput($_POST['imie'] ?? '');
    $nazwisko = sanitizeInput($_POST['nazwisko'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telefon = sanitizeInput($_POST['telefon'] ?? '');
    $typKursu = sanitizeInput($_POST['typ_kursu'] ?? '');
    $doswiadczenie = sanitizeInput($_POST['doswiadczenie'] ?? '');
    $wiadomosc = sanitizeInput($_POST['wiadomosc'] ?? '');
    $zgoda_rodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
    $zgoda_marketing = isset($_POST['zgoda_marketing']) ? 1 : 0;
    
    // Walidacja wymaganych pól
    $errors = [];
    
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
    
    // Przygotuj dane do zapisu
    $data = [
        'imie' => $imie,
        'nazwisko' => $nazwisko,
        'email' => $email,
        'telefon' => $telefon,
        'typ_kursu' => $typKursu,
        'doswiadczenie' => $doswiadczenie,
        'wiadomosc' => $wiadomosc,
        'zgoda_marketing' => $zgoda_marketing,
        'zgoda_rodo' => $zgoda_rodo
    ];
    
    // Zapisz do bazy (funkcja saveCourseInterest już istnieje w functions.php)
    $saved = saveCourseInterest($data);
    
    if ($saved) {
        // Wyślij emaile
        sendCourseConfirmationEmail($imie, $email);
        sendCourseNotificationEmail($data);
        
        jsonResponse([
            'success' => true,
            'message' => 'Dziękujemy! Skontaktujemy się z Tobą wkrótce.'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie.'
        ], 500);
    }
}

/**
 * Wyślij email potwierdzający do osoby zainteresowanej
 */
function sendCourseConfirmationEmail($imie, $email) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
    
    $subject = "Potwierdzenie zgłoszenia na kurs - {$companyName}";
    
    $message = "
Cześć {$imie},

Dziękujemy za zainteresowanie naszymi kursami malarskimi!

Otrzymaliśmy Twoje zgłoszenie i w ciągu 2-3 dni roboczych skontaktujemy się z Tobą z informacjami o:
- Najbliższych terminach kursów
- Cenach i metodach płatności
- Szczegółach programu szkolenia
- Lokalizacji i godzinach

Jeśli masz pilne pytania, zadzwoń:
📞 {$companyPhone} (Pon-Pt: 8:00-18:00)

Pozdrawiamy,
Zespół {$companyName}

---
To jest automatyczna wiadomość. Prosimy na nią nie odpowiadać.
    ";
    
    $headers = [
        "From: {$companyName} <{$companyEmail}>",
        "Reply-To: {$companyEmail}",
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Wyślij notyfikację do firmy o nowym zainteresowaniu kursem
 */
function sendCourseNotificationEmail($data) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'maltechnik.chojnice@gmail.com';
    $emailOnLead = $settings['email_on_lead'] ?? '1';
    
    // Sprawdź czy wysyłać powiadomienia (kursy używają tej samej flagi co leady)
    if ($emailOnLead != '1') {
        return; // Nie wysyłaj jeśli wyłączone
    }
    
    $subject = '🎓 Nowe zgłoszenie na kurs';
    
    $typKursu = [
        'podstawy' => 'Podstawy malarstwa budowlanego',
        'gladzie' => 'Gładzie gipsowe premium (Q4)',
        'tynki' => 'Tynki dekoracyjne',
        'firmowe' => 'Szkolenia dla ekip (B2B)',
        'inne' => 'Chce dowiedzieć się więcej'
    ];
    
    $doswiadczenieText = [
        'poczatkujacy' => 'Początkujący (brak doświadczenia)',
        'sredniozaawansowany' => 'Średniozaawansowany (1-3 lata)',
        'zaawansowany' => 'Zaawansowany (3+ lata)'
    ];
    
    $message = "
NOWE ZAINTERESOWANIE KURSEM

--- DANE OSOBY ---
Imię: {$data['imie']} {$data['nazwisko']}
Email: {$data['email']}
Telefon: {$data['telefon']}

--- ZAINTERESOWANIE ---
Typ kursu: " . ($typKursu[$data['typ_kursu']] ?? $data['typ_kursu']) . "
Doświadczenie: " . ($doswiadczenieText[$data['doswiadczenie']] ?? $data['doswiadczenie']) . "

--- WIADOMOŚĆ ---
" . ($data['wiadomosc'] ?: '(brak)') . "

--- ZGODY ---
RODO: " . ($data['zgoda_rodo'] ? 'TAK' : 'NIE') . "
Marketing: " . ($data['zgoda_marketing'] ? 'TAK' : 'NIE') . "

--- DANE TECHNICZNE ---
IP: " . getUserIP() . "
Data: " . date('Y-m-d H:i:s') . "

---
Skontaktuj się z osobą w ciągu 2-3 dni roboczych.
    ";
    
    $headers = [
        "From: Formularz kursów {$companyName} <noreply@maltechnik.pl>",
        'Reply-To: ' . $data['email'],
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Wyślij do notification_email z ustawień
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}