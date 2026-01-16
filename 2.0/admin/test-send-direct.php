<?php
/**
 * TEST-SEND-DIRECT.PHP - Bezpośredni test wysyłki
 * Umieść w /admin/ i wywołaj: https://www.maltechnik.pl/admin/test-send-direct.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test wysyłki</title>";
echo "<style>body{font-family:Arial;max-width:900px;margin:20px auto;padding:20px;}";
echo ".ok{background:#dcfce7;border-left:4px solid #16a34a;padding:12px;margin:10px 0;}";
echo ".err{background:#fee2e2;border-left:4px solid #dc2626;padding:12px;margin:10px 0;}";
echo ".info{background:#dbeafe;border-left:4px solid #3b82f6;padding:12px;margin:10px 0;}";
echo "pre{background:#f9fafb;padding:12px;border-radius:6px;overflow-x:auto;}";
echo "h2{border-bottom:2px solid #e5e7eb;padding-bottom:10px;}</style></head><body>";

echo "<h1>🧪 Test bezpośredniej wysyłki newslettera</h1>";

// ==============================================
// TEST 1: Sprawdź pliki
// ==============================================
echo "<h2>1️⃣ Sprawdzenie plików</h2>";

$files = [
    __DIR__ . '/../includes/db.php',
    __DIR__ . '/../includes/functions.php', 
    __DIR__ . '/../includes/email-helpers.php',
    __DIR__ . '/api/send-campaign.php',
];

$allFilesOk = true;
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<div class='ok'>✅ $file</div>";
    } else {
        echo "<div class='err'>❌ BRAK: $file</div>";
        $allFilesOk = false;
    }
}

if (!$allFilesOk) {
    echo "<div class='err'><strong>STOP!</strong> Brakuje plików. Wgraj wszystkie pliki przed kontynuacją.</div>";
    die("</body></html>");
}

// ==============================================
// TEST 2: Załaduj zależności
// ==============================================
echo "<h2>2️⃣ Ładowanie zależności</h2>";

try {
    require_once __DIR__ . '/../includes/db.php';
    echo "<div class='ok'>✅ db.php załadowany</div>";
    
    require_once __DIR__ . '/../includes/functions.php';
    echo "<div class='ok'>✅ functions.php załadowany</div>";
    
    require_once __DIR__ . '/../includes/email-helpers.php';
    echo "<div class='ok'>✅ email-helpers.php załadowany</div>";
    
} catch (Exception $e) {
    echo "<div class='err'>❌ Błąd: " . $e->getMessage() . "</div>";
    die("</body></html>");
}

// ==============================================
// TEST 3: Połączenie z bazą
// ==============================================
echo "<h2>3️⃣ Połączenie z bazą</h2>";

try {
    if (isset($pdo)) {
        echo "<div class='ok'>✅ Połączenie z bazą: OK</div>";
        
        // Sprawdź subskrybentów
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT email) as count 
            FROM marketing_consents 
            WHERE status = 'active' AND consent_marketing = 1
        ");
        $subsCount = $stmt->fetchColumn();
        
        echo "<div class='info'>📊 Aktywnych subskrybentów: <strong>$subsCount</strong></div>";
        
        if ($subsCount === 0) {
            echo "<div class='err'>⚠️ Brak subskrybentów! Dodaj testowego:</div>";
            echo "<pre>INSERT INTO marketing_consents (email, source, consent_marketing, status, subscribed_at) 
VALUES ('twoj-email@example.com', 'test', 1, 'active', NOW());</pre>";
        }
        
        // Sprawdź kampanie
        $stmt = $pdo->query("
            SELECT id, name, status, created_at 
            FROM newsletter_campaigns 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($campaigns)) {
            echo "<div class='err'>⚠️ Brak kampanii! Utwórz kampanię w panelu admina.</div>";
        } else {
            echo "<div class='ok'>✅ Znaleziono " . count($campaigns) . " kampanii</div>";
            echo "<table border='1' cellpadding='8' style='border-collapse:collapse;width:100%;'>";
            echo "<tr><th>ID</th><th>Nazwa</th><th>Status</th><th>Data</th></tr>";
            foreach ($campaigns as $c) {
                echo "<tr>";
                echo "<td><strong>#{$c['id']}</strong></td>";
                echo "<td>" . htmlspecialchars($c['name']) . "</td>";
                echo "<td><strong>{$c['status']}</strong></td>";
                echo "<td>" . date('Y-m-d H:i', strtotime($c['created_at'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<div class='err'>❌ Brak obiektu \$pdo</div>";
        die("</body></html>");
    }
} catch (Exception $e) {
    echo "<div class='err'>❌ Błąd bazy: " . $e->getMessage() . "</div>";
    die("</body></html>");
}

// ==============================================
// TEST 4: PHPMailer
// ==============================================
echo "<h2>4️⃣ Test PHPMailer</h2>";

try {
    $mail = initPHPMailer();
    
    if ($mail) {
        echo "<div class='ok'>✅ PHPMailer zainicjalizowany</div>";
        echo "<div class='info'>Host: {$mail->Host} | Port: {$mail->Port} | Username: {$mail->Username}</div>";
    } else {
        echo "<div class='err'>❌ initPHPMailer() zwróciło null</div>";
        die("</body></html>");
    }
} catch (Exception $e) {
    echo "<div class='err'>❌ Błąd PHPMailer: " . $e->getMessage() . "</div>";
    die("</body></html>");
}

// ==============================================
// TEST 5: Wysyłka testowa
// ==============================================
echo "<h2>5️⃣ Test wysyłki emaila</h2>";

if (isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    
    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='info'>📧 Wysyłam testowy email na: <strong>$testEmail</strong></div>";
        
        try {
            $content = "
                <h2>Test wysyłki newslettera</h2>
                <p>To jest testowy email z systemu newslettera Maltechnik.</p>
                <p><strong>Data testu:</strong> " . date('Y-m-d H:i:s') . "</p>
                <hr>
                <p>Jeśli widzisz ten email, oznacza to że PHPMailer działa poprawnie! ✅</p>
            ";
            
            $htmlEmail = getEmailTemplate($content, 'Test newslettera');
            
            echo "<div class='info'>HTML email wygenerowany. Wysyłam...</div>";
            
            $sent = sendHTMLEmail($testEmail, '', 'Test newslettera - Maltechnik', $htmlEmail);
            
            if ($sent) {
                echo "<div class='ok'><strong>✅ EMAIL WYSŁANY!</strong><br>Sprawdź skrzynkę: $testEmail<br>(Sprawdź też SPAM)</div>";
            } else {
                echo "<div class='err'>❌ sendHTMLEmail() zwróciło false. Sprawdź logi PHP.</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='err'>❌ Wyjątek: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    } else {
        echo "<div class='err'>❌ Nieprawidłowy email</div>";
    }
}

echo "<form method='POST' style='margin:20px 0;padding:20px;background:#f9fafb;border-radius:8px;'>";
echo "<label style='display:block;margin-bottom:10px;font-weight:600;'>Wyślij testowy email:</label>";
echo "<input type='email' name='test_email' placeholder='twoj-email@example.com' required style='padding:10px;width:300px;border:1px solid #d1d5db;border-radius:6px;'>";
echo " <button type='submit' style='padding:10px 20px;background:#2B59A6;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;'>📧 Wyślij test</button>";
echo "</form>";

// ==============================================
// TEST 6: Wywołanie skryptu wysyłkowego
// ==============================================
echo "<h2>6️⃣ Test skryptu send-campaign.php</h2>";

$scriptPath = __DIR__ . '/api/send-campaign.php';

if (!file_exists($scriptPath)) {
    echo "<div class='err'>❌ Plik nie istnieje: $scriptPath</div>";
} else {
    echo "<div class='ok'>✅ Plik istnieje: $scriptPath</div>";
    
    if (isset($_POST['test_campaign_id'])) {
        $campaignId = (int)$_POST['test_campaign_id'];
        
        echo "<div class='info'>🚀 Wywołuję skrypt dla kampanii #$campaignId</div>";
        
        // Zmień status na 'sending'
        try {
            $stmt = $pdo->prepare("UPDATE newsletter_campaigns SET status = 'sending' WHERE id = ?");
            $stmt->execute([$campaignId]);
            echo "<div class='ok'>✅ Status zmieniony na 'sending'</div>";
        } catch (Exception $e) {
            echo "<div class='err'>❌ Błąd zmiany statusu: " . $e->getMessage() . "</div>";
        }
        
        // Wywołaj skrypt
        echo "<h3>Output skryptu:</h3>";
        echo "<pre style='background:#1f2937;color:#10b981;padding:15px;border-radius:8px;max-height:400px;overflow-y:auto;'>";
        
        ob_start();
        passthru("php " . escapeshellarg($scriptPath) . " " . escapeshellarg($campaignId) . " 2>&1", $returnCode);
        $output = ob_get_clean();
        
        echo htmlspecialchars($output);
        echo "</pre>";
        
        echo "<div class='info'>Return code: <strong>$returnCode</strong></div>";
        
        if ($returnCode === 0) {
            echo "<div class='ok'>✅ Skrypt zakończony sukcesem</div>";
            
            // Sprawdź status
            $stmt = $pdo->prepare("SELECT status, sent_count, recipients_count FROM newsletter_campaigns WHERE id = ?");
            $stmt->execute([$campaignId]);
            $camp = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<div class='info'>";
            echo "Status: <strong>{$camp['status']}</strong><br>";
            echo "Wysłano: <strong>{$camp['sent_count']} / {$camp['recipients_count']}</strong>";
            echo "</div>";
            
        } else {
            echo "<div class='err'>❌ Skrypt zwrócił błąd (kod: $returnCode)</div>";
        }
    }
    
    // Formularz wyboru kampanii
    try {
        $stmt = $pdo->query("SELECT id, name, status FROM newsletter_campaigns ORDER BY created_at DESC LIMIT 10");
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($campaigns)) {
            echo "<form method='POST' style='margin:20px 0;padding:20px;background:#fef3c7;border:2px solid #fde68a;border-radius:8px;'>";
            echo "<p style='margin:0 0 10px 0;font-weight:600;color:#92400e;'>⚠️ UWAGA: To wywoła RZECZYWISTĄ wysyłkę do wszystkich subskrybentów!</p>";
            echo "<label style='display:block;margin-bottom:10px;font-weight:600;'>Wybierz kampanię:</label>";
            echo "<select name='test_campaign_id' style='padding:10px;width:300px;border:1px solid #d1d5db;border-radius:6px;'>";
            foreach ($campaigns as $c) {
                echo "<option value='{$c['id']}'>#{$c['id']} - {$c['name']} ({$c['status']})</option>";
            }
            echo "</select>";
            echo " <button type='submit' style='padding:10px 20px;background:#dc2626;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;'>🚀 Wywołaj skrypt</button>";
            echo "</form>";
        }
    } catch (Exception $e) {
        echo "<div class='err'>❌ Błąd: " . $e->getMessage() . "</div>";
    }
}

// ==============================================
// LOGI
// ==============================================
echo "<h2>7️⃣ Ostatnie logi PHP</h2>";

$logFile = __DIR__ . '/../php-error.log';

if (file_exists($logFile)) {
    $logs = file($logFile);
    $lastLogs = array_slice($logs, -30);
    
    $newsletterLogs = array_filter($lastLogs, function($line) {
        return stripos($line, 'newsletter') !== false || 
               stripos($line, 'campaign') !== false ||
               stripos($line, 'phpmailer') !== false;
    });
    
    if (!empty($newsletterLogs)) {
        echo "<pre style='max-height:300px;overflow-y:auto;'>";
        echo htmlspecialchars(implode('', $newsletterLogs));
        echo "</pre>";
    } else {
        echo "<div class='info'>ℹ️ Brak logów związanych z newsletterem w ostatnich 30 liniach</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Plik php-error.log nie znaleziony w: $logFile</div>";
}

echo "<hr><p style='text-align:center;color:#6b7280;'>Test wykonany: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";