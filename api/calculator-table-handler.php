<?php
/**
 * CALCULATOR-TABLE-HANDLER.PHP - API dla tabelarycznego kalkulatora
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

// Obsługa tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$action = $_POST['action'] ?? '';

if ($action === 'save_calculation') {
    saveCalculationHandler();
} elseif ($action === 'save_with_email') {
    saveWithEmailHandler();
} else {
    jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

/**
 * Zapisz wyliczenie bez emaila
 */
function saveCalculationHandler() {
    $calculationData = $_POST['calculation_data'] ?? '';
    $totalPrice = floatval($_POST['total_price'] ?? 0);
    
    if (empty($calculationData)) {
        jsonResponse(['success' => false, 'message' => 'Brak danych kalkulacji'], 400);
    }
    
    // Zapisz do bazy
    $data = [
        'calculation_data' => $calculationData,
        'total_price' => $totalPrice,
        'email' => null
    ];
    
    $saved = saveCalculatorTableLog($data);
    
    if ($saved) {
        // Zwiększ licznik użycia
        incrementCalculatorUsage();
        
        jsonResponse([
            'success' => true,
            'message' => 'Wyliczenie zapisane',
            'usage_count' => getCalculatorUsageCount()
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Błąd zapisu'], 500);
    }
}

/**
 * Zapisz wyliczenie z emailem (po limicie)
 */
function saveWithEmailHandler() {
    $calculationData = $_POST['calculation_data'] ?? '';
    $totalPrice = floatval($_POST['total_price'] ?? 0);
    $imie = sanitizeInput($_POST['imie'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telefon = sanitizeInput($_POST['telefon'] ?? '');
    $zgoda_rodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
    
    // Walidacja
    $errors = [];
    
    if (empty($imie)) {
        $errors[] = 'Imię jest wymagane';
    }
    
    if (empty($email)) {
        $errors[] = 'Email jest wymagany';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Nieprawidłowy adres email';
    }
    
    if (!$zgoda_rodo) {
        $errors[] = 'Musisz zaakceptować politykę prywatności';
    }
    
    if (empty($calculationData)) {
        $errors[] = 'Brak danych kalkulacji';
    }
    
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
    }
    
    // Zapisz do bazy
    $data = [
        'calculation_data' => $calculationData,
        'total_price' => $totalPrice,
        'email' => $email,
        'imie' => $imie,
        'telefon' => $telefon
    ];
    
    $saved = saveCalculatorTableLog($data);
    
    if ($saved) {
        // Wyślij email do klienta
        sendCalculationEmailToClient($imie, $email, $calculationData, $totalPrice);
        
        // Wyślij notyfikację do admina
        sendCalculationEmailToAdmin($imie, $email, $telefon, $calculationData, $totalPrice);
        
        // Reset licznika użycia
        resetCalculatorUsage();
        
        jsonResponse([
            'success' => true,
            'message' => 'Wyliczenie wysłane na email',
            'usage_count' => 0
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Błąd zapisu'], 500);
    }
}

/**
 * Zapisz do bazy danych
 */
function saveCalculatorTableLog($data) {
    global $pdo;
    
    try {
        $sql = "INSERT INTO kalkulator_table_logs 
                (calculation_data, total_price, email, imie, telefon, ip, user_agent, created_at) 
                VALUES 
                (:calculation_data, :total_price, :email, :imie, :telefon, :ip, :user_agent, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'calculation_data' => $data['calculation_data'],
            'total_price' => $data['total_price'],
            'email' => $data['email'] ?? null,
            'imie' => $data['imie'] ?? null,
            'telefon' => $data['telefon'] ?? null,
            'ip' => getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log('Calc save error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Email do klienta z wyliczeniem
 */
function sendCalculationEmailToClient($imie, $email, $calculationData, $totalPrice) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
    $companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
    
    $items = json_decode($calculationData, true);
    
    $itemsList = '';
    foreach ($items as $item) {
        $itemsList .= sprintf(
            "- %s: %.2f m² × %.2f zł/m² = %.2f zł\n",
            $item['service_name'],
            $item['meters'],
            $item['price_per_m2'],
            $item['total']
        );
    }
    
    $subject = "Twoje wyliczenie z kalkulatora - {$companyName}";
    
    $message = "
Cześć {$imie},

Dziękujemy za skorzystanie z naszego kalkulatora cennikowego!

Oto Twoje wyliczenie:

{$itemsList}

SUMA CAŁKOWITA: " . number_format($totalPrice, 2, ',', ' ') . " zł

WAŻNE: Kwoty są orientacyjne. Ostateczna cena może się zmienić po ocenie na miejscu 
(stan podłoża, naprawy, dostęp, zabezpieczenia, technologia, materiały).

Aby otrzymać dokładną wycenę dopasowaną do Twojego projektu:
📞 Zadzwoń: {$companyPhone}
✉️ Napisz: {$companyEmail}
🌐 Formularz: https://maltechnik.pl/kontakt.php

Pozdrawiamy,
Zespół {$companyName}
    ";
    
    $headers = [
        "From: {$companyName} <{$companyEmail}>",
        "Reply-To: {$companyEmail}",
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Notyfikacja do admina
 */
function sendCalculationEmailToAdmin($imie, $email, $telefon, $calculationData, $totalPrice) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'maltechnik.chojnice@gmail.com';
    $emailOnCalculation = $settings['email_on_calculation'] ?? '0';
    
    // Sprawdź czy wysyłać powiadomienia o kalkulatorze
    if ($emailOnCalculation != '1') {
        return; // Nie wysyłaj jeśli wyłączone
    }
    
    $items = json_decode($calculationData, true);
    
    $itemsList = '';
    foreach ($items as $item) {
        $itemsList .= sprintf(
            "- %s: %.2f m² × %.2f zł/m² = %.2f zł\n",
            $item['service_name'],
            $item['meters'],
            $item['price_per_m2'],
            $item['total']
        );
    }
    
    $subject = '💰 Nowe wyliczenie z kalkulatora - klient podał email!';
    
    $message = "
NOWE WYLICZENIE Z KALKULATORA

Klient wypełnił kalkulator i podał swój email - GORĄCY LEAD!

--- DANE KLIENTA ---
Imię: {$imie}
Email: {$email}
Telefon: " . ($telefon ?: '(nie podano)') . "

--- WYLICZENIE ---
{$itemsList}

SUMA: " . number_format($totalPrice, 2, ',', ' ') . " zł

--- DANE TECHNICZNE ---
IP: " . getUserIP() . "
Data: " . date('Y-m-d H:i:s') . "

---
Skontaktuj się z klientem w ciągu 24h!
Wyliczenie zostało automatycznie wysłane na podany email.
    ";
    
    $headers = [
        "From: Kalkulator {$companyName} <noreply@maltechnik.pl>",
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    mail($notificationEmail, $subject, $message, implode("\r\n", $headers));
}