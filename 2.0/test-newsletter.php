<?php
/**
 * TEST-NEWSLETTER.PHP - Kompletna diagnostyka systemu newslettera
 * Umieść w głównym katalogu i odpal: https://www.maltechnik.pl/test-newsletter.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Diagnostyka Newslettera</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        h1 { color: #2B59A6; }
        h2 { color: #374151; border-bottom: 2px solid #E5E7EB; padding-bottom: 10px; margin-top: 30px; }
        .success { background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px; margin: 10px 0; }
        .error { background: #fee2e2; border-left: 4px solid #dc2626; padding: 12px; margin: 10px 0; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 10px 0; }
        .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px; margin: 10px 0; }
        pre { background: #f9fafb; padding: 12px; border-radius: 6px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th { background: #f3f4f6; padding: 10px; text-align: left; font-weight: 600; }
        table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        button { background: #2B59A6; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; margin: 5px; }
        button:hover { background: #1e3a8a; }
        button.secondary { background: #6b7280; }
        button.secondary:hover { background: #4b5563; }
    </style>
</head>
<body>";

echo "<h1>🔍 Diagnostyka Systemu Newslettera</h1>";

// ============================================
// 1. SPRAWDŹ PLIKI
// ============================================
echo "<h2>1️⃣ Pliki</h2>";

$files = [
    'admin/api/send-campaign.php' => 'Skrypt wysyłkowy',
    'includes/db.php' => 'Konfiguracja bazy',
    'includes/email-helpers.php' => 'Funkcje PHPMailer',
    'includes/functions.php' => 'Funkcje pomocnicze',
    'lib/phpmailer/src/PHPMailer.php' => 'PHPMailer (Composer)',
    'vendor/autoload.php' => 'Composer autoload',
];

echo "<table>";
echo "<tr><th>Plik</th><th>Opis</th><th>Status</th></tr>";

foreach ($files as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $badge = $exists ? 
        "<span class='badge badge-success'>✅ Istnieje</span>" : 
        "<span class='badge badge-error'>❌ Brak</span>";
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td>$desc</td>";
    echo "<td>$badge</td>";
    echo "</tr>";
}

echo "</table>";

// ============================================
// 2. SPRAWDŹ FUNKCJE PHP
// ============================================
echo "<h2>2️⃣ Funkcje PHP</h2>";

$functions = [
    'exec' => 'Wywołanie skryptu w tle (najlepsze)',
    'curl_init' => 'HTTP request (fallback)',
    'mail' => 'Funkcja mail() (nie używana)',
];

echo "<table>";
echo "<tr><th>Funkcja</th><th>Opis</th><th>Status</th></tr>";

foreach ($functions as $func => $desc) {
    $available = function_exists($func);
    $badge = $available ? 
        "<span class='badge badge-success'>✅ Dostępna</span>" : 
        "<span class='badge badge-warning'>⚠️ Niedostępna</span>";
    
    echo "<tr>";
    echo "<td><code>$func()</code></td>";
    echo "<td>$desc</td>";
    echo "<td>$badge</td>";
    echo "</tr>";
}

echo "</table>";

// ============================================
// 3. SPRAWDŹ BAZĘ DANYCH
// ============================================
echo "<h2>3️⃣ Baza danych</h2>";

try {
    require_once __DIR__ . '/includes/db.php';
    echo "<div class='success'>✅ Połączenie z bazą danych: <strong>OK</strong></div>";
    
    // Sprawdź tabele
    $tables = [
        'newsletter_campaigns' => 'Kampanie newslettera',
        'newsletter_blocks' => 'Bloki kampanii',
        'newsletter_sends' => 'Logi wysyłek (opcjonalna)',
        'marketing_consents' => 'Subskrybenci',
    ];
    
    echo "<table>";
    echo "<tr><th>Tabela</th><th>Opis</th><th>Status</th><th>Rekordów</th></tr>";
    
    foreach ($tables as $table => $desc) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<tr>";
            echo "<td><code>$table</code></td>";
            echo "<td>$desc</td>";
            echo "<td><span class='badge badge-success'>✅ Istnieje</span></td>";
            echo "<td><strong>$count</strong></td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><code>$table</code></td>";
            echo "<td>$desc</td>";
            echo "<td><span class='badge badge-error'>❌ Brak</span></td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    // Sprawdź subskrybentów
    echo "<h3>Aktywni subskrybenci</h3>";
    try {
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT email) as count 
            FROM marketing_consents 
            WHERE status = 'active' AND consent_marketing = 1
        ");
        $subscribersCount = $stmt->fetchColumn();
        
        if ($subscribersCount > 0) {
            echo "<div class='success'>✅ Znaleziono <strong>$subscribersCount</strong> aktywnych subskrybentów</div>";
            
            // Pokaż przykładowych
            $stmt = $pdo->query("
                SELECT email, source, subscribed_at 
                FROM marketing_consents 
                WHERE status = 'active' AND consent_marketing = 1 
                LIMIT 5
            ");
            $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Email</th><th>Źródło</th><th>Data</th></tr>";
            foreach ($subscribers as $sub) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($sub['email']) . "</td>";
                echo "<td>" . htmlspecialchars($sub['source']) . "</td>";
                echo "<td>" . htmlspecialchars($sub['subscribed_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div class='warning'>⚠️ Brak aktywnych subskrybentów. Dodaj testowego:</div>";
            echo "<pre>INSERT INTO marketing_consents 
(email, source, consent_marketing, status, subscribed_at) 
VALUES ('twoj-email@example.com', 'test', 1, 'active', NOW());</pre>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Błąd: " . $e->getMessage() . "</div>";
    }
    
    // Sprawdź kampanie
    echo "<h3>Kampanie</h3>";
    try {
        $stmt = $pdo->query("
            SELECT id, name, subject, status, recipients_count, sent_count, created_at 
            FROM newsletter_campaigns 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($campaigns)) {
            echo "<div class='info'>ℹ️ Brak kampanii. Utwórz pierwszą w panelu admina.</div>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nazwa</th><th>Status</th><th>Wysłano</th><th>Data</th></tr>";
            foreach ($campaigns as $camp) {
                $statusBadge = [
                    'draft' => 'badge-warning',
                    'sending' => 'badge-warning',
                    'sent' => 'badge-success',
                    'failed' => 'badge-error'
                ];
                $badge = $statusBadge[$camp['status']] ?? 'badge-warning';
                
                echo "<tr>";
                echo "<td><strong>#{$camp['id']}</strong></td>";
                echo "<td>" . htmlspecialchars($camp['name']) . "</td>";
                echo "<td><span class='badge $badge'>" . $camp['status'] . "</span></td>";
                echo "<td>{$camp['sent_count']} / {$camp['recipients_count']}</td>";
                echo "<td>" . date('d.m.Y H:i', strtotime($camp['created_at'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Błąd: " . $e->getMessage() . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Błąd połączenia z bazą: " . $e->getMessage() . "</div>";
}

// ============================================
// 4. SPRAWDŹ KONFIGURACJĘ SMTP
// ============================================
echo "<h2>4️⃣ Konfiguracja SMTP</h2>";

try {
    if (defined('SMTP_HOST')) {
        echo "<table>";
        echo "<tr><th>Parametr</th><th>Wartość</th></tr>";
        echo "<tr><td>Host</td><td><code>" . SMTP_HOST . "</code></td></tr>";
        echo "<tr><td>Port</td><td><code>" . SMTP_PORT . "</code></td></tr>";
        echo "<tr><td>Szyfrowanie</td><td><code>" . SMTP_ENCRYPTION . "</code></td></tr>";
        echo "<tr><td>Username</td><td><code>" . SMTP_USERNAME . "</code></td></tr>";
        echo "<tr><td>Password</td><td><code>" . (strlen(SMTP_PASSWORD) > 0 ? str_repeat('*', 8) . ' (' . strlen(SMTP_PASSWORD) . ' znaków)' : '❌ BRAK') . "</code></td></tr>";
        echo "<tr><td>From Email</td><td><code>" . SMTP_FROM_EMAIL . "</code></td></tr>";
        echo "<tr><td>From Name</td><td><code>" . SMTP_FROM_NAME . "</code></td></tr>";
        echo "</table>";
        
        if (strlen(SMTP_PASSWORD) > 0) {
            echo "<div class='success'>✅ Konfiguracja SMTP wygląda poprawnie</div>";
        } else {
            echo "<div class='error'>❌ Brak hasła SMTP! Ustaw w includes/db.php</div>";
        }
    } else {
        echo "<div class='error'>❌ Konfiguracja SMTP nie znaleziona w includes/db.php</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Błąd: " . $e->getMessage() . "</div>";
}

// ============================================
// 5. TEST PHPMAILER
// ============================================
echo "<h2>5️⃣ Test PHPMailer</h2>";

try {
    require_once __DIR__ . '/includes/email-helpers.php';
    
    $mail = initPHPMailer();
    
    if ($mail) {
        echo "<div class='success'>✅ PHPMailer zainicjalizowany poprawnie</div>";
        
        echo "<table>";
        echo "<tr><th>Parametr</th><th>Wartość</th></tr>";
        echo "<tr><td>Mailer</td><td><code>{$mail->Mailer}</code></td></tr>";
        echo "<tr><td>Host</td><td><code>{$mail->Host}</code></td></tr>";
        echo "<tr><td>Port</td><td><code>{$mail->Port}</code></td></tr>";
        echo "<tr><td>Username</td><td><code>{$mail->Username}</code></td></tr>";
        echo "<tr><td>From</td><td><code>{$mail->From}</code></td></tr>";
        echo "</table>";
        
    } else {
        echo "<div class='error'>❌ Nie można zainicjalizować PHPMailer</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Błąd PHPMailer: " . $e->getMessage() . "</div>";
}

// ============================================
// 6. TEST WYSYŁKI
// ============================================
echo "<h2>6️⃣ Test wysyłki</h2>";

if (isset($_POST['test_send'])) {
    $testEmail = $_POST['test_email'] ?? '';
    
    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='info'>📧 Wysyłam testowy email na: <strong>$testEmail</strong></div>";
        
        try {
            require_once __DIR__ . '/includes/email-helpers.php';
            
            $content = "
                <h2>Test wysyłki newslettera</h2>
                <p>To jest testowy email z systemu newslettera.</p>
                <p>Jeśli widzisz ten email, oznacza to że PHPMailer działa poprawnie! ✅</p>
                <p><small>Data testu: " . date('Y-m-d H:i:s') . "</small></p>
            ";
            
            $htmlEmail = getEmailTemplate($content, 'Test newslettera');
            
            $sent = sendHTMLEmail($testEmail, '', 'Test newslettera - Maltechnik', $htmlEmail);
            
            if ($sent) {
                echo "<div class='success'>✅ <strong>Email wysłany!</strong> Sprawdź skrzynkę: $testEmail</div>";
            } else {
                echo "<div class='error'>❌ Błąd wysyłki. Sprawdź logi PHP.</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Błąd: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Nieprawidłowy adres email</div>";
    }
}

echo "<form method='POST'>";
echo "<p><strong>Wyślij testowy email:</strong></p>";
echo "<input type='email' name='test_email' placeholder='twoj-email@example.com' required style='padding: 10px; width: 300px; border: 1px solid #d1d5db; border-radius: 6px;'>";
echo "<button type='submit' name='test_send'>📧 Wyślij test</button>";
echo "</form>";

// ============================================
// 7. TEST SKRYPTU WYSYŁKOWEGO
// ============================================
echo "<h2>7️⃣ Test skryptu wysyłkowego</h2>";

if (isset($_POST['test_script'])) {
    $campaignId = (int)($_POST['campaign_id'] ?? 0);
    
    if ($campaignId > 0) {
        echo "<div class='info'>🚀 Wywołuję skrypt wysyłkowy dla kampanii #$campaignId</div>";
        
        $scriptPath = __DIR__ . 'admin/api/send-campaign.php';
        
        if (file_exists($scriptPath)) {
            
            // Próba 1: exec()
            if (function_exists('exec')) {
                echo "<pre>";
                echo "Metoda: exec()\n";
                echo "Komenda: php " . escapeshellarg($scriptPath) . " $campaignId\n\n";
                
                $output = [];
                $returnCode = 0;
                exec("php " . escapeshellarg($scriptPath) . " $campaignId 2>&1", $output, $returnCode);
                
                echo "Return code: $returnCode\n";
                echo "Output:\n" . implode("\n", $output);
                echo "</pre>";
                
                if ($returnCode === 0) {
                    echo "<div class='success'>✅ Skrypt wykonany pomyślnie!</div>";
                } else {
                    echo "<div class='error'>❌ Skrypt zwrócił błąd (kod: $returnCode)</div>";
                }
                
            } 
            // Próba 2: cURL
            elseif (function_exists('curl_init')) {
                $url = 'https://www.maltechnik.pl/admin/api/send-campaign.php?campaign_id=' . $campaignId;
                
                echo "<pre>";
                echo "Metoda: cURL\n";
                echo "URL: $url\n\n";
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                echo "HTTP Code: $httpCode\n";
                if ($curlError) {
                    echo "cURL Error: $curlError\n";
                }
                echo "Response:\n$response";
                echo "</pre>";
                
                if ($httpCode === 200) {
                    echo "<div class='success'>✅ Skrypt wywołany pomyślnie!</div>";
                } else {
                    echo "<div class='error'>❌ Błąd HTTP (kod: $httpCode)</div>";
                }
            }
            
        } else {
            echo "<div class='error'>❌ Plik admin/api/send-campaign.php nie istnieje!</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Podaj ID kampanii</div>";
    }
}

// Lista kampanii do testu
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("
            SELECT id, name, status 
            FROM newsletter_campaigns 
            WHERE status IN ('draft', 'sending')
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $testCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($testCampaigns)) {
            echo "<form method='POST'>";
            echo "<p><strong>Wybierz kampanię do testu:</strong></p>";
            echo "<select name='campaign_id' style='padding: 10px; width: 300px; border: 1px solid #d1d5db; border-radius: 6px;'>";
            foreach ($testCampaigns as $camp) {
                echo "<option value='{$camp['id']}'>#{$camp['id']} - {$camp['name']} ({$camp['status']})</option>";
            }
            echo "</select>";
            echo "<button type='submit' name='test_script'>🚀 Test skryptu</button>";
            echo "<p style='color: #6b7280; font-size: 13px;'>⚠️ To wywoła rzeczywistą wysyłkę do wszystkich subskrybentów!</p>";
            echo "</form>";
        } else {
            echo "<div class='info'>ℹ️ Brak kampanii do testu. Status musi być 'draft' lub 'sending'.</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Błąd: " . $e->getMessage() . "</div>";
}

// ============================================
// 8. LOGI
// ============================================
echo "<h2>8️⃣ Ostatnie logi</h2>";

$logFile = __DIR__ . '/php-error.log';

if (file_exists($logFile)) {
    $logs = file($logFile);
    $lastLogs = array_slice($logs, -50); // Ostatnie 50 linii
    
    $newsletterLogs = array_filter($lastLogs, function($line) {
        return strpos($line, 'NEWSLETTER') !== false || 
               strpos($line, 'send-campaign') !== false ||
               strpos($line, 'PHPMailer') !== false;
    });
    
    if (!empty($newsletterLogs)) {
        echo "<pre style='max-height: 400px; overflow-y: auto;'>";
        echo htmlspecialchars(implode('', $newsletterLogs));
        echo "</pre>";
    } else {
        echo "<div class='info'>ℹ️ Brak logów związanych z newsletterem</div>";
    }
    
    echo "<p><a href='?show_all_logs=1'><button class='secondary'>Pokaż wszystkie logi (50 ostatnich)</button></a></p>";
    
    if (isset($_GET['show_all_logs'])) {
        echo "<pre style='max-height: 600px; overflow-y: auto;'>";
        echo htmlspecialchars(implode('', $lastLogs));
        echo "</pre>";
    }
    
} else {
    echo "<div class='warning'>⚠️ Plik php-error.log nie został znaleziony</div>";
}

// ============================================
// PODSUMOWANIE
// ============================================
echo "<h2>✅ Podsumowanie</h2>";

$issues = [];
$warnings = [];

if (!file_exists(__DIR__ . 'admin/api/send-campaign.php')) {
    $issues[] = "Brak pliku admin/api/send-campaign.php";
}

if (!file_exists(__DIR__ . '/includes/email-helpers.php')) {
    $issues[] = "Brak pliku includes/email-helpers.php";
}

if (!function_exists('exec') && !function_exists('curl_init')) {
    $warnings[] = "Funkcje exec() i curl_init() są niedostępne - wysyłka może nie działać w tle";
}

if (defined('SMTP_PASSWORD') && strlen(SMTP_PASSWORD) === 0) {
    $issues[] = "Brak hasła SMTP w includes/db.php";
}

try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM marketing_consents WHERE status = 'active' AND consent_marketing = 1");
        $count = $stmt->fetchColumn();
        if ($count === 0) {
            $warnings[] = "Brak aktywnych subskrybentów";
        }
    }
} catch (Exception $e) {}

if (empty($issues) && empty($warnings)) {
    echo "<div class='success'>";
    echo "<h3 style='margin: 0 0 10px 0;'>🎉 Wszystko wygląda dobrze!</h3>";
    echo "<p>System newslettera jest gotowy do działania.</p>";
    echo "</div>";
} else {
    if (!empty($issues)) {
        echo "<div class='error'>";
        echo "<h3 style='margin: 0 0 10px 0;'>❌ Problemy wymagające naprawy:</h3>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    if (!empty($warnings)) {
        echo "<div class='warning'>";
        echo "<h3 style='margin: 0 0 10px 0;'>⚠️ Ostrzeżenia:</h3>";
        echo "<ul>";
        foreach ($warnings as $warning) {
            echo "<li>$warning</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
}

echo "<hr style='margin: 40px 0;'>";
echo "<p style='text-align: center; color: #6b7280; font-size: 13px;'>
    Test wykonany: " . date('Y-m-d H:i:s') . " | 
    <a href='?' style='color: #2B59A6;'>Odśwież</a> | 
    <strong style='color: #dc2626;'>USUŃ ten plik po zakończeniu testów!</strong>
</p>";

echo "</body></html>";