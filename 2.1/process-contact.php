<?php
/**
 * PROCESS-CONTACT.PHP - Obsługa nowego formularza kontaktowego
 * Zapisuje do leads z załącznikami
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

$temat_wiadomosci = isset($_POST['temat_wiadomosci']) ? trim($_POST['temat_wiadomosci']) : '';
$imie_nazwisko = isset($_POST['imie_nazwisko']) ? trim($_POST['imie_nazwisko']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
$lokalizacja = isset($_POST['lokalizacja']) ? trim($_POST['lokalizacja']) : '';
$opis = isset($_POST['opis']) ? trim($_POST['opis']) : '';
$termin = isset($_POST['termin']) ? trim($_POST['termin']) : '';
$zgoda_rodo = isset($_POST['zgoda_rodo']) ? 1 : 0;
$zgoda_marketing = isset($_POST['zgoda_marketing']) ? 1 : 0;

// Log dla debugowania
error_log("=== CONTACT FORM START ===");
error_log("Temat: $temat_wiadomosci, Imie: $imie_nazwisko, Email: $email");

// Walidacja
$errors = [];

$dozwolone_tematy = ['remont', 'elewacja', 'agregat', 'instytucje', 'prowadzenie_budowy', 'deweloper', 'konsultacja', 'wspolpraca', 'inne'];
if (empty($temat_wiadomosci) || !in_array($temat_wiadomosci, $dozwolone_tematy)) {
    $errors[] = 'Wybierz temat wiadomości';
}

if (empty($imie_nazwisko)) {
    $errors[] = 'Imię i nazwisko są wymagane';
}

if (empty($email)) {
    $errors[] = 'Email jest wymagany';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Podaj prawidłowy adres email';
}

if (empty($opis)) {
    $errors[] = 'Opis jest wymagany';
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
// OBSŁUGA ZAŁĄCZNIKÓW
// ============================================

$uploadedFiles = [];
$uploadDir = __DIR__ . '/uploads/contact/';

// Utwórz katalog jeśli nie istnieje
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['zdjecia']) && !empty($_FILES['zdjecia']['name'][0])) {
    $fileCount = count($_FILES['zdjecia']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['zdjecia']['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['zdjecia']['name'][$i];
            $fileTmpName = $_FILES['zdjecia']['tmp_name'][$i];
            $fileSize = $_FILES['zdjecia']['size'][$i];
            $fileType = $_FILES['zdjecia']['type'][$i];

            // Walidacja rozmiaru (max 10MB)
            if ($fileSize > 10 * 1024 * 1024) {
                error_log("Plik $fileName jest za duży");
                continue;
            }

            // Walidacja typu pliku
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
            if (!in_array($fileType, $allowedTypes)) {
                error_log("Nieprawidłowy typ pliku: $fileType");
                continue;
            }

            // Generuj bezpieczną nazwę
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $safeFileName = uniqid('contact_', true) . '.' . $fileExt;
            $targetPath = $uploadDir . $safeFileName;

            if (move_uploaded_file($fileTmpName, $targetPath)) {
                $uploadedFiles[] = [
                    'original_name' => $fileName,
                    'saved_name' => $safeFileName,
                    'path' => 'uploads/contact/' . $safeFileName,
                    'size' => $fileSize,
                    'type' => $fileType
                ];
                error_log("✓ Zapisano plik: $safeFileName");
            } else {
                error_log("❌ Nie udało się zapisać pliku: $fileName");
            }
        }
    }
}

// ============================================
// ZAPIS DO BAZY - leads
// ============================================

try {
    // Sprawdź czy $pdo istnieje
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Brak połączenia z bazą danych');
    }

    error_log("Rozpoczynam zapis do bazy leads");
    $pdo->beginTransaction();

    // Mapowanie tematów
    $temat_map = [
        'remont' => 'Remont / wykończenie wnętrz',
        'elewacja' => 'Elewacja (malowanie / naprawy)',
        'agregat' => 'Malowanie wielkopowierzchniowe agregatem',
        'instytucje' => 'Instytucje i firmy (kosztorys / harmonogram)',
        'prowadzenie_budowy' => 'Prowadzenie budowy / organizacja ekip',
        'deweloper' => 'Realizacja projektu deweloperskiego',
        'konsultacja' => 'Konsultacja online (45 min / 200 zł)',
        'wspolpraca' => 'Współpraca medialna',
        'inne' => 'Inne'
    ];

    $temat_pelny = $temat_map[$temat_wiadomosci] ?? $temat_wiadomosci;

    // Przygotuj wiadomość
    $message = "Temat: $temat_pelny\n\n";
    $message .= "Opis:\n$opis\n\n";
    if (!empty($lokalizacja)) {
        $message .= "Lokalizacja: $lokalizacja\n";
    }
    if (!empty($termin)) {
        $message .= "Termin: $termin\n";
    }
    if (!empty($uploadedFiles)) {
        $message .= "\nZałączniki: " . count($uploadedFiles) . " plik(ów)\n";
    }

    // Przygotuj dodatkowe dane JSON
    $additionalData = [
        'temat' => $temat_wiadomosci,
        'temat_pelny' => $temat_pelny,
        'lokalizacja' => $lokalizacja,
        'termin' => $termin,
        'attachments' => $uploadedFiles
    ];

    // Zapisz do leads
    $stmt = $pdo->prepare("
        INSERT INTO leads (
            name, email, phone, message,
            source, status, additional_data, created_at
        ) VALUES (?, ?, ?, ?, 'website', 'new', ?, NOW())
    ");

    $stmt->execute([
        $imie_nazwisko,
        $email,
        $telefon,
        $message,
        json_encode($additionalData, JSON_UNESCAPED_UNICODE)
    ]);

    $insertId = $pdo->lastInsertId();
    error_log("✓ Zapisano lead, ID: $insertId");

    // Zapisz zgodę marketingową
    if ($zgoda_marketing) {
        saveMarketingConsent($pdo, $email, $imie_nazwisko, $temat_wiadomosci, [
            'phone' => $telefon,
            'temat' => $temat_pelny
        ]);
    }

    // Commit PRZED wysyłką emaili
    $pdo->commit();

    // Email do klienta
    sendContactEmailToClient($email, $imie_nazwisko, $temat_pelny, $opis);

    // Email do admina
    sendContactEmailToAdmin($imie_nazwisko, $email, $telefon, $temat_pelny, $opis, $lokalizacja, $termin, $uploadedFiles);

    sendJsonResponse([
        'success' => true,
        'message' => 'Dziękujemy! Otrzymaliśmy Twoją wiadomość. Odpowiemy w godzinach pracy (pn-pt 8:00-16:00). Jeśli wysłałeś formularz po 16:00, odpowiemy następnego dnia roboczego.',
        'id' => $insertId
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("❌ Błąd PDO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    sendJsonResponse([
        'success' => false,
        'message' => 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie lub zadzwoń w godzinach 8:00-16:00 (pn-pt).'
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
 * Email HTML do klienta
 */
function sendContactEmailToClient($email, $name, $subject, $description) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $companyPhone = $settings['company_phone'] ?? '+48 784 607 452';

    $firstName = explode(' ', $name)[0];

    $content = '
    <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 24px;">Cześć ' . htmlspecialchars($firstName) . '!</h2>
    <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px; line-height: 1.6;">
        Dziękujemy za wiadomość!
    </p>

    <div style="background: #eff6ff; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0 0 8px 0; color: #1e40af; font-size: 14px; font-weight: 600;">Temat:</p>
        <p style="margin: 0; color: #1e3a8a; font-size: 16px; font-weight: 500;">' . htmlspecialchars($subject) . '</p>
    </div>

    <p style="margin: 0 0 20px 0; color: #374151; font-size: 15px; line-height: 1.6;">
        Otrzymaliśmy Twoją wiadomość i <strong>odpowiemy w godzinach pracy (pn-pt 8:00-16:00)</strong>. Jeśli wysłałeś formularz po 16:00, odpowiemy następnego dnia roboczego.
    </p>

    <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px;">📞 Kontakt telefoniczny:</strong>
            Jeśli chcesz szybciej, zadzwoń:<br>
            <strong style="font-size: 16px; color: #2B59A6;">' . htmlspecialchars($companyPhone) . '</strong><br>
            <span style="font-size: 13px;">pn-pt: 8:00-16:00</span>
        </p>
    </div>

    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px;">⚠️ Po godzinach:</strong>
            Po 16:00 i w weekendy nie oddzwaniamy i nie prowadzimy rozmów. Odpowiadamy pisemnie (mail/WhatsApp).
        </p>
    </div>

    <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
        Pozdrawiamy serdecznie,<br>
        <strong style="color: #2B59A6;">Zespół ' . htmlspecialchars($companyName) . '</strong>
    </p>
    ';

    $htmlEmail = getEmailTemplate($content, 'Otrzymaliśmy Twoją wiadomość');

    return sendHTMLEmail(
        $email,
        $name,
        "Potwierdzenie - {$companyName}",
        $htmlEmail
    );
}

/**
 * Email HTML do admina
 */
function sendContactEmailToAdmin($name, $email, $phone, $subject, $description, $location, $deadline, $attachments) {
    $settings = getSettings();
    $companyName = $settings['company_name'] ?? 'Maltechnik';
    $notificationEmail = $settings['notification_email'] ?? 'info@maltechnik.pl';

    $attachmentsHtml = '';
    if (!empty($attachments)) {
        $attachmentsHtml = '<h3 style="margin: 25px 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Załączniki (' . count($attachments) . '):</h3>';
        $attachmentsHtml .= '<ul style="margin: 0; padding-left: 20px;">';
        foreach ($attachments as $file) {
            $fileUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $file['path'];
            $attachmentsHtml .= '<li style="margin: 5px 0;"><a href="' . htmlspecialchars($fileUrl) . '" style="color: #2B59A6;">' . htmlspecialchars($file['original_name']) . '</a> (' . round($file['size'] / 1024, 2) . ' KB)</li>';
        }
        $attachmentsHtml .= '</ul>';
    }

    $content = '
    <div style="background: #fef3cd; border-left: 4px solid #f59e0b; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
        <p style="margin: 0; font-size: 16px; color: #92400e; font-weight: 600;">
            📧 NOWA WIADOMOŚĆ Z FORMULARZA
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
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Telefon/WhatsApp:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;"><strong style="color: #2B59A6;">' . htmlspecialchars($phone ?: 'Nie podano') . '</strong></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong style="color: #111827;">Temat:</strong></td>
            <td style="padding: 10px 0; color: #374151; font-size: 14px;">' . htmlspecialchars($subject) . '</td>
        </tr>
    </table>

    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;">Opis:</h3>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <p style="margin: 0; color: #374151; font-size: 15px; line-height: 1.6;">' . nl2br(htmlspecialchars($description)) . '</p>
    </div>

    ' . (!empty($location) ? '<p style="margin: 10px 0; color: #374151;"><strong>Lokalizacja:</strong> ' . htmlspecialchars($location) . '</p>' : '') . '
    ' . (!empty($deadline) ? '<p style="margin: 10px 0; color: #374151;"><strong>Termin:</strong> ' . htmlspecialchars($deadline) . '</p>' : '') . '

    ' . $attachmentsHtml . '

    <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 20px; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 8px; font-size: 15px;">✅ AKCJA:</strong>
            Odpowiedz klientowi w godzinach pracy (pn-pt 8:00-16:00). Po godzinach odpowiadamy pisemnie (mail/WhatsApp).
        </p>
    </div>

    <p style="margin: 25px 0 0 0; font-size: 12px; color: #9ca3af;">
        Data zgłoszenia: ' . date('Y-m-d H:i:s') . '<br>
        IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '
    </p>
    ';

    $htmlEmail = getEmailTemplate($content, '📧 Nowa wiadomość z formularza');

    return sendHTMLEmail(
        $notificationEmail,
        $companyName,
        "📧 Nowa wiadomość: $subject",
        $htmlEmail,
        $email
    );
}
