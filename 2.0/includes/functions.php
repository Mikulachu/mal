<?php
/**
 * Funkcje pomocnicze dla całej aplikacji
 */

// Start sesji jeśli nie została rozpoczęta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Zabezpieczenie output przed XSS
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanityzacja inputu tekstowego
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Walidacja emaila
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Walidacja telefonu (polski format)
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^(\+48)?[0-9]{9}$/', $phone);
}

/**
 * Pobierz IP użytkownika
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

/**
 * Pobierz User Agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}

/**
 * Generuj unikalny ID sesji kalkulatora
 */
function getCalculatorSessionId() {
    if (!isset($_SESSION['calculator_session_id'])) {
        $_SESSION['calculator_session_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['calculator_session_id'];
}

/**
 * Pobierz liczbę użyć kalkulatora (dla NOWEGO kalkulatora tabelarycznego - SESJA)
 */
function getCalculatorUsageCount() {
    if (!isset($_SESSION)) {
        session_start();
    }
    return isset($_SESSION['calculator_usage']) ? (int)$_SESSION['calculator_usage'] : 0;
}

/**
 * Zwiększ licznik użycia kalkulatora (dla NOWEGO kalkulatora tabelarycznego)
 */
function incrementCalculatorUsage() {
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['calculator_usage'])) {
        $_SESSION['calculator_usage'] = 0;
    }
    $_SESSION['calculator_usage']++;
}

/**
 * Resetuj licznik użycia kalkulatora (dla NOWEGO kalkulatora tabelarycznego)
 */
function resetCalculatorUsage() {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['calculator_usage'] = 0;
}

/**
 * Sprawdź czy użytkownik może użyć kalkulatora (dla STAREGO kalkulatora - BAZA)
 */
function canUseCalculator() {
    require_once 'db.php';
    
    $sessionId = getCalculatorSessionId();
    $sql = "SELECT COUNT(*) as count FROM kalkulator_logs WHERE session_id = ?";
    $result = dbFetchOne($sql, [$sessionId]);
    
    $count = $result ? (int)$result['count'] : 0;
    return $count < 3;
}

/**
 * Sprawdź czy użytkownik podał już email dla kalkulatora
 */
function hasProvidedCalculatorEmail() {
    require_once 'db.php';
    
    $sessionId = getCalculatorSessionId();
    $sql = "SELECT email FROM kalkulator_logs WHERE session_id = ? AND email IS NOT NULL LIMIT 1";
    $result = dbFetchOne($sql, [$sessionId]);
    
    return $result ? true : false;
}

/**
 * Zapisz wyliczenie kalkulatora do bazy (dla STAREGO kalkulatora)
 */
function saveCalculatorLog($data) {
    require_once 'db.php';
    
    $sql = "INSERT INTO kalkulator_logs 
            (session_id, email, typ_uslugi, metraz, standard, dodatkowe_uslugi, 
             cena_od, cena_do, ip_address, user_agent, czy_podal_email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        getCalculatorSessionId(),
        $data['email'] ?? null,
        $data['typ_uslugi'] ?? null,
        $data['metraz'] ?? 0,
        $data['standard'] ?? null,
        json_encode($data['dodatkowe_uslugi'] ?? []),
        $data['cena_od'] ?? null,
        $data['cena_do'] ?? null,
        getUserIP(),
        getUserAgent(),
        isset($data['email']) ? 1 : 0
    ];
    
    return dbInsert($sql, $params);
}

/**
 * Zapisz lead z formularza kontaktowego
 */
function saveLead($data) {
    require_once 'db.php';
    
    $sql = "INSERT INTO leads 
            (imie, nazwisko, email, telefon, typ_uslugi, wiadomosc, 
             zgoda_marketing, zgoda_rodo, zrodlo, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $data['imie'] ?? '',
        $data['nazwisko'] ?? null,
        $data['email'] ?? '',
        $data['telefon'] ?? null,
        $data['typ_uslugi'] ?? null,
        $data['wiadomosc'] ?? null,
        isset($data['zgoda_marketing']) ? 1 : 0,
        isset($data['zgoda_rodo']) ? 1 : 0,
        $data['zrodlo'] ?? 'formularz_kontaktowy',
        getUserIP(),
        getUserAgent()
    ];
    
    return dbInsert($sql, $params);
}

/**
 * Zapisz zapytanie o konsultację
 */
function saveConsultation($data) {
    require_once 'db.php';
    
    $sql = "INSERT INTO konsultacje 
            (imie, nazwisko, email, telefon, temat, preferowany_termin, 
             opis_problemu, typ_konsultacji, zgoda_rodo, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $data['imie'] ?? '',
        $data['nazwisko'] ?? null,
        $data['email'] ?? '',
        $data['telefon'] ?? null,
        $data['temat'] ?? '',
        $data['preferowany_termin'] ?? null,
        $data['opis_problemu'] ?? null,
        $data['typ_konsultacji'] ?? 'online',
        isset($data['zgoda_rodo']) ? 1 : 0,
        getUserIP()
    ];
    
    return dbInsert($sql, $params);
}

/**
 * Zapisz zainteresowanie kursem
 */
function saveCourseInterest($data) {
    require_once 'db.php';
    
    $sql = "INSERT INTO kursy_zapisy 
            (imie, nazwisko, email, telefon, typ_kursu, doswiadczenie, 
             wiadomosc, zgoda_marketing, zgoda_rodo, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $data['imie'] ?? '',
        $data['nazwisko'] ?? null,
        $data['email'] ?? '',
        $data['telefon'] ?? null,
        $data['typ_kursu'] ?? null,
        $data['doswiadczenie'] ?? null,
        $data['wiadomosc'] ?? null,
        isset($data['zgoda_marketing']) ? 1 : 0,
        isset($data['zgoda_rodo']) ? 1 : 0,
        getUserIP()
    ];
    
    return dbInsert($sql, $params);
}

/**
 * Format ceny (z PLN)
 */
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' PLN';
}

/**
 * Format metrażu
 */
function formatMetraz($metraz) {
    return number_format($metraz, 2, ',', ' ') . ' m²';
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


/**
 * Pobierz ustawienia z bazy (cache)
 */
function getSettings() {
    static $settings = null;
    
    if ($settings === null) {
        global $pdo;
        
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            // Domyślne wartości jeśli nie ma w bazie
            $defaults = [
                'company_name' => 'Maltechnik',
                'company_phone' => '+48 784 607 452',
                'company_email' => 'maltechnik.chojnice@gmail.com',
                'company_address' => '89-600 Chojnice, Ul. Tischnera 8',
                'company_description' => 'Malowanie oraz remonty Chojnice.',
                'company_nip' => '5552130861',
                'notification_email' => 'kontakt@example.pl',
                'notifications_enabled' => '1',
                'email_on_lead' => '1',
                'email_on_consultation' => '1',
                'email_on_calculation' => '0'
            ];
            
            foreach ($defaults as $key => $value) {
                if (!isset($settings[$key])) {
                    $settings[$key] = $value;
                }
            }
            
        } catch (PDOException $e) {
            error_log("Błąd pobierania ustawień: " . $e->getMessage());
            $settings = [];
        }
    }
    
    return $settings;
}

/**
 * Pobierz pojedyncze ustawienie
 */
function getSetting($key, $default = '') {
    $settings = getSettings();
    return $settings[$key] ?? $default;
}
?>