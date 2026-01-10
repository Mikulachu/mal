<?php
/**
 * CALCULATOR-HANDLER.PHP - API do obsługi kalkulatora
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

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
    // Walidacja danych
    $typUslugi = sanitizeInput($_POST['typ_uslugi'] ?? '');
    $metraz = floatval($_POST['metraz'] ?? 0);
    $standard = sanitizeInput($_POST['standard'] ?? '');
    $dodatkowe = $_POST['dodatkowe_uslugi'] ?? '[]';
    $cenaOd = floatval($_POST['cena_od'] ?? 0);
    $cenaDo = floatval($_POST['cena_do'] ?? 0);
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : null;
    
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
 * Wysyła wycenę na email
 */
function sendCalculationEmail($email, $data) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
    
    // Przygotuj treść emaila
    $subject = "Twoja wycena z kalkulatora - {$companyName}";
    
    $dodatkowe = json_decode($data['dodatkowe_uslugi'], true);
    $dodatkoweText = '';
    if (!empty($dodatkowe)) {
        $dodatkoweNames = [
            'projekt' => 'Projekt i wizualizacje',
            'koordynacja' => 'Koordynacja branż',
            'premium_pakiet' => 'Pakiet premium',
            'ekspresowa' => 'Realizacja ekspresowa'
        ];
        $dodatkoweList = array_map(function($item) use ($dodatkoweNames) {
            return $dodatkoweNames[$item] ?? $item;
        }, $dodatkowe);
        $dodatkoweText = "\nDodatkowe usługi: " . implode(', ', $dodatkoweList);
    }
    
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
    
    $message = "
Dziękujemy za skorzystanie z naszego kalkulatora!

Oto Twoja orientacyjna wycena:

Typ usługi: {$typNazwy[$data['typ_uslugi']]}
Powierzchnia: {$data['metraz']} m²
Standard: {$standardNazwy[$data['standard']]}
{$dodatkoweText}

Orientacyjny koszt: " . number_format($data['cena_od'], 0, ',', ' ') . " - " . number_format($data['cena_do'], 0, ',', ' ') . " PLN

To wycena orientacyjna. Aby otrzymać dokładną wycenę, skontaktuj się z nami:
- Telefon: {$companyPhone}
- E-mail: {$companyEmail}
- Strona: https://maltechnik.pl/kontakt.php

Pozdrawiamy,
Zespół {$companyName}
    ";
    
    $headers = [
        "From: {$companyName} <{$companyEmail}>",
        "Reply-To: {$companyEmail}",
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Wyślij email
    mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Wysyła notyfikację do admina o nowej wycenie z kalkulatora
 */
function sendCalculationNotificationToAdmin($email, $data) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'maltechnik.chojnice@gmail.com';
    $emailOnCalculation = $settings['email_on_calculation'] ?? '0';
    
    // Sprawdź czy wysyłać powiadomienia o kalkulatorze
    if ($emailOnCalculation != '1') {
        return; // Nie wysyłaj jeśli wyłączone
    }
    
    $subject = '📊 Nowa wycena z kalkulatora - klient podał email';
    
    $dodatkowe = json_decode($data['dodatkowe_uslugi'], true);
    $dodatkoweText = '';
    if (!empty($dodatkowe)) {
        $dodatkoweNames = [
            'projekt' => 'Projekt i wizualizacje',
            'koordynacja' => 'Koordynacja branż',
            'premium_pakiet' => 'Pakiet premium',
            'ekspresowa' => 'Realizacja ekspresowa'
        ];
        $dodatkoweList = array_map(function($item) use ($dodatkoweNames) {
            return $dodatkoweNames[$item] ?? $item;
        }, $dodatkowe);
        $dodatkoweText = "\nDodatkowe usługi: " . implode(', ', $dodatkoweList);
    }
    
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
    
    $message = "
NOWA WYCENA Z KALKULATORA

Klient użył kalkulatora i podał swój email - GORĄCY LEAD!

--- DANE KLIENTA ---
Email: {$email}

--- WYCENA ---
Typ usługi: {$typNazwy[$data['typ_uslugi']]}
Powierzchnia: {$data['metraz']} m²
Standard: {$standardNazwy[$data['standard']]}{$dodatkoweText}

Orientacyjna wycena: " . number_format($data['cena_od'], 0, ',', ' ') . " - " . number_format($data['cena_do'], 0, ',', ' ') . " PLN

--- DANE TECHNICZNE ---
IP: " . getUserIP() . "
Data: " . date('Y-m-d H:i:s') . "

---
Skontaktuj się z klientem w ciągu 24h!
Wycena została automatycznie wysłana na podany email.
    ";
    
    $headers = [
        "From: Kalkulator {$companyName} <noreply@maltechnik.pl>",
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Wyślij do notification_email z ustawień
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}