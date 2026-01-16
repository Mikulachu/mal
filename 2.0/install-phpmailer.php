<?php
/**
 * INSTALL-PHPMAILER.PHP - Prosty instalator PHPMailer
 * Uruchom w przeglądarce: https://www.maltechnik.pl/install-phpmailer.php
 */

// Wyłącz wyświetlanie błędów inline (mogą psuć HTML)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalacja PHPMailer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 16px; }
        
        .content { padding: 30px; }
        
        .step { 
            background: #f8f9fa;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .step h2 { 
            color: #333;
            margin-bottom: 15px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success { 
            background: #d4edda;
            padding: 15px;
            border-radius: 6px;
            color: #155724;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error { 
            background: #f8d7da;
            padding: 15px;
            border-radius: 6px;
            color: #721c24;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .warning { 
            background: #fff3cd;
            padding: 15px;
            border-radius: 6px;
            color: #856404;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info { 
            background: #d1ecf1;
            padding: 15px;
            border-radius: 6px;
            color: #0c5460;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        
        .code { 
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
        }
        .btn:hover { 
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        
        .form-group { margin: 20px 0; }
        .form-group label { 
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
            transition: border 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 13px;
        }
        
        .file-list { 
            list-style: none;
            padding: 0;
        }
        .file-list li {
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e0e0e0;
        }
        .file-list li.exists { border-color: #28a745; }
        .file-list li.missing { border-color: #dc3545; }
        
        .icon { 
            font-size: 20px;
            width: 24px;
            text-align: center;
        }
        
        ol { padding-left: 20px; }
        ol li { margin: 10px 0; line-height: 1.6; }
        
        a { color: #667eea; text-decoration: none; font-weight: 600; }
        a:hover { text-decoration: underline; }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Instalator PHPMailer</h1>
            <p>Skonfiguruj wysyłkę emaili w 5 minut</p>
        </div>
        
        <div class="content">
            <?php
            // ============================================
            // KROK 1: Sprawdź pliki
            // ============================================
            echo '<div class="step">';
            echo '<h2><span class="icon">📁</span> Krok 1: Sprawdzanie plików</h2>';
            
            $files = [
                'includes/db.php' => 'Połączenie z bazą',
                'includes/functions.php' => 'Funkcje pomocnicze',
                'process-contact.php' => 'Formularz kontaktowy',
            ];
            
            echo '<ul class="file-list">';
            $allFilesExist = true;
            foreach ($files as $file => $desc) {
                $exists = file_exists(__DIR__ . '/' . $file);
                $class = $exists ? 'exists' : 'missing';
                $icon = $exists ? '✅' : '❌';
                echo "<li class='$class'><span class='icon'>$icon</span> <strong>$file</strong> - $desc</li>";
                if (!$exists) $allFilesExist = false;
            }
            echo '</ul>';
            
            if (!$allFilesExist) {
                echo '<div class="error">⚠️ Brakuje niektórych plików! Upewnij się że wgrałeś wszystkie pliki.</div>';
            }
            echo '</div>';
            
            // ============================================
            // KROK 2: Sprawdź instalację PHPMailer
            // ============================================
            echo '<div class="step">';
            echo '<h2><span class="icon">📦</span> Krok 2: Instalacja PHPMailer</h2>';
            
            $phpmailerPaths = [
                'vendor/autoload.php' => 'Instalacja przez Composer',
                'vendor/phpmailer/phpmailer/src/PHPMailer.php' => 'PHPMailer (Composer)',
                'lib/phpmailer/src/PHPMailer.php' => 'PHPMailer (ręczna instalacja)',
            ];
            
            $phpmailerInstalled = false;
            $installType = '';
            
            echo '<ul class="file-list">';
            foreach ($phpmailerPaths as $path => $desc) {
                $exists = file_exists(__DIR__ . '/' . $path);
                $class = $exists ? 'exists' : 'missing';
                $icon = $exists ? '✅' : '❌';
                echo "<li class='$class'><span class='icon'>$icon</span> <strong>$path</strong> - $desc</li>";
                
                if ($exists && strpos($path, 'PHPMailer.php') !== false) {
                    $phpmailerInstalled = true;
                    $installType = strpos($path, 'vendor') !== false ? 'composer' : 'manual';
                }
            }
            echo '</ul>';
            
            if ($phpmailerInstalled) {
                echo '<div class="success">✅ <strong>PHPMailer jest zainstalowany!</strong> (Typ: ' . $installType . ')</div>';
            } else {
                echo '<div class="warning">⚠️ <strong>PHPMailer NIE jest zainstalowany</strong></div>';
                echo '<div class="info">';
                echo '<p><strong>Wybierz metodę instalacji:</strong></p>';
                echo '<h3 style="margin-top:20px;">Metoda 1: Ręczna instalacja (ZALECANE)</h3>';
                echo '<ol>';
                echo '<li>Pobierz: <a href="https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip" target="_blank">PHPMailer ZIP</a></li>';
                echo '<li>Rozpakuj archiwum</li>';
                echo '<li>Na serwerze utwórz folder: <code>lib/phpmailer/</code></li>';
                echo '<li>Z rozpakowanego folderu <code>PHPMailer-master/src/</code> wgraj wszystkie pliki do <code>lib/phpmailer/src/</code></li>';
                echo '<li>Odśwież tę stronę</li>';
                echo '</ol>';
                
                echo '<h3 style="margin-top:20px;">Metoda 2: Przez Composer (jeśli masz SSH)</h3>';
                echo '<div class="code">cd ' . __DIR__ . '<br>composer require phpmailer/phpmailer</div>';
                echo '</div>';
            }
            echo '</div>';
            
            // ============================================
            // KROK 3: Konfiguracja (tylko jeśli PHPMailer jest zainstalowany)
            // ============================================
            if ($phpmailerInstalled) {
                $configFile = __DIR__ . '/includes/phpmailer-config.php';
                $configExists = file_exists($configFile);
                
                echo '<div class="step">';
                echo '<h2><span class="icon">⚙️</span> Krok 3: Konfiguracja SMTP</h2>';
                
                // Obsługa zapisu konfiguracji
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
                    $config = "<?php
/**
 * PHPMAILER-CONFIG.PHP - Konfiguracja PHPMailer
 */

return [
    'smtp_host' => '" . addslashes($_POST['smtp_host']) . "',
    'smtp_port' => " . (int)$_POST['smtp_port'] . ",
    'smtp_username' => '" . addslashes($_POST['smtp_username']) . "',
    'smtp_password' => '" . addslashes($_POST['smtp_password']) . "',
    'smtp_encryption' => '" . addslashes($_POST['smtp_encryption']) . "',
    'from_email' => '" . addslashes($_POST['from_email']) . "',
    'from_name' => '" . addslashes($_POST['from_name']) . "',
];
";
                    
                    if (!is_dir(__DIR__ . '/includes')) {
                        mkdir(__DIR__ . '/includes', 0755, true);
                    }
                    
                    if (file_put_contents($configFile, $config)) {
                        echo '<div class="success">✅ Konfiguracja zapisana pomyślnie!</div>';
                        $configExists = true;
                    } else {
                        echo '<div class="error">❌ Nie udało się zapisać konfiguracji. Sprawdź uprawnienia do katalogu includes/</div>';
                    }
                }
                
                if ($configExists) {
                    echo '<div class="success">✅ Plik konfiguracyjny istnieje: <code>includes/phpmailer-config.php</code></div>';
                    echo '<p style="margin:15px 0;">Możesz edytować konfigurację ponownie lub przejść do testowania.</p>';
                    echo '<a href="?test" class="btn btn-success">🧪 Testuj wysyłkę emaila</a>';
                    echo ' <a href="?edit" class="btn">✏️ Edytuj konfigurację</a>';
                }
                
                if (!$configExists || isset($_GET['edit'])) {
                    // Formularz konfiguracji
                    echo '<form method="POST">';
                    
                    echo '<div class="form-group">';
                    echo '<label>Host SMTP</label>';
                    echo '<input type="text" name="smtp_host" value="smtp.gmail.com" required>';
                    echo '<small>Gmail: smtp.gmail.com | Outlook: smtp-mail.outlook.com | Własny: mail.twoja-domena.pl</small>';
                    echo '</div>';
                    
                    echo '<div class="form-group">';
                    echo '<label>Port</label>';
                    echo '<select name="smtp_port" required>';
                    echo '<option value="587" selected>587 (TLS/STARTTLS)</option>';
                    echo '<option value="465">465 (SSL)</option>';
                    echo '<option value="25">25 (Niezabezpieczony)</option>';
                    echo '</select>';
                    echo '</div>';
                    
                    echo '<div class="form-group">';
                    echo '<label>Szyfrowanie</label>';
                    echo '<select name="smtp_encryption" required>';
                    echo '<option value="tls" selected>TLS/STARTTLS</option>';
                    echo '<option value="ssl">SSL</option>';
                    echo '</select>';
                    echo '</div>';
                    
                    echo '<div class="form-group">';
                    echo '<label>Username (email)</label>';
                    echo '<input type="email" name="smtp_username" value="maltechnik.chojnice@gmail.com" required>';
                    echo '</div>';
                    
                    echo '<div class="form-group">';
                    echo '<label>Hasło SMTP</label>';
                    echo '<input type="password" name="smtp_password" required>';
                    echo '<small>⚠️ Dla Gmail użyj <a href="https://myaccount.google.com/apppasswords" target="_blank">hasła aplikacji</a>, nie zwykłego hasła!</small>';
                    echo '</div>';
                    
                    echo '<div class="form-group">';
                    echo '<label>Od (email)</label>';
                    echo '<input type="email" name="from_email" value="maltechnik.chojnice@gmail.com" required>';
                    echo '</div>';
                    
                    echo '<div class="form-group">';
                    echo '<label>Od (nazwa)</label>';
                    echo '<input type="text" name="from_name" value="Maltechnik" required>';
                    echo '</div>';
                    
                    echo '<button type="submit" name="save_config" class="btn" style="width:100%;">💾 Zapisz konfigurację</button>';
                    echo '</form>';
                    
                    echo '<div class="info" style="margin-top:20px;">';
                    echo '<strong>💡 Wskazówki dla Gmail:</strong>';
                    echo '<ol>';
                    echo '<li>Wejdź na: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a></li>';
                    echo '<li>Wybierz: Aplikacja → Mail, Urządzenie → Inne (wpisz "Maltechnik")</li>';
                    echo '<li>Kliknij "Wygeneruj"</li>';
                    echo '<li>Skopiuj 16-znakowe hasło (np. <code>abcd efgh ijkl mnop</code>)</li>';
                    echo '<li>Wklej w pole "Hasło SMTP" powyżej</li>';
                    echo '</ol>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                // ============================================
                // KROK 4: Test wysyłki
                // ============================================
                if ($configExists && isset($_GET['test'])) {
                    echo '<div class="step">';
                    echo '<h2><span class="icon">🧪</span> Krok 4: Test wysyłki</h2>';
                    
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
                        $testEmail = $_POST['test_email'];
                        
                        try {
                            // Załaduj konfigurację
                            $config = include $configFile;
                            
                            // Załaduj PHPMailer
                            if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                                require __DIR__ . '/vendor/autoload.php';
                            } else {
                                require __DIR__ . '/lib/phpmailer/src/PHPMailer.php';
                                require __DIR__ . '/lib/phpmailer/src/SMTP.php';
                                require __DIR__ . '/lib/phpmailer/src/Exception.php';
                            }
                            
                            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                            
                            // Konfiguracja
                            $mail->isSMTP();
                            $mail->Host = $config['smtp_host'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $config['smtp_username'];
                            $mail->Password = $config['smtp_password'];
                            $mail->SMTPSecure = $config['smtp_encryption'];
                            $mail->Port = $config['smtp_port'];
                            $mail->CharSet = 'UTF-8';
                            
                            // Email
                            $mail->setFrom($config['from_email'], $config['from_name']);
                            $mail->addAddress($testEmail);
                            $mail->Subject = 'Test PHPMailer - Maltechnik';
                            $mail->Body = "Gratulacje!\n\nPHPMailer działa poprawnie!\n\nFormularz kontaktowy jest teraz w pełni funkcjonalny.\n\nPozdrawiamy,\nZespół Maltechnik";
                            
                            $mail->send();
                            
                            echo '<div class="success">';
                            echo '<strong>✅ Email wysłany pomyślnie!</strong><br>';
                            echo 'Sprawdź skrzynkę: <strong>' . htmlspecialchars($testEmail) . '</strong><br><br>';
                            echo '🎉 <strong>Instalacja zakończona!</strong><br>';
                            echo 'Formularz kontaktowy jest gotowy do użycia.';
                            echo '</div>';
                            
                            echo '<div class="warning" style="margin-top:20px;">';
                            echo '<strong>⚠️ Ważne - Bezpieczeństwo:</strong>';
                            echo '<ol>';
                            echo '<li><strong>USUŃ</strong> ten plik (install-phpmailer.php) ze serwera</li>';
                            echo '<li>Plik <code>includes/phpmailer-config.php</code> zawiera hasło SMTP!</li>';
                            echo '<li>Zabezpiecz go: <code>chmod 600 includes/phpmailer-config.php</code></li>';
                            echo '</ol>';
                            echo '</div>';
                            
                        } catch (Exception $e) {
                            echo '<div class="error">';
                            echo '<strong>❌ Błąd wysyłki:</strong><br>';
                            echo htmlspecialchars($e->getMessage());
                            if (isset($mail)) {
                                echo '<br><br><strong>PHPMailer Info:</strong><br>' . htmlspecialchars($mail->ErrorInfo);
                            }
                            echo '</div>';
                            
                            echo '<div class="info" style="margin-top:20px;">';
                            echo '<strong>Typowe przyczyny błędów:</strong>';
                            echo '<ul>';
                            echo '<li><strong>"SMTP connect() failed"</strong> - sprawdź host i port</li>';
                            echo '<li><strong>"Authentication failed"</strong> - dla Gmail użyj hasła aplikacji!</li>';
                            echo '<li><strong>"Could not authenticate"</strong> - zły login lub hasło</li>';
                            echo '</ul>';
                            echo '<a href="?edit" class="btn">✏️ Popraw konfigurację</a>';
                            echo '</div>';
                        }
                    }
                    
                    // Formularz testowy
                    echo '<form method="POST">';
                    echo '<div class="form-group">';
                    echo '<label>Email testowy (twój adres)</label>';
                    echo '<input type="email" name="test_email" placeholder="twoj@email.pl" required>';
                    echo '<small>Wyślemy testowy email na ten adres</small>';
                    echo '</div>';
                    echo '<button type="submit" name="send_test" class="btn btn-success" style="width:100%;">📧 Wyślij testowy email</button>';
                    echo '</form>';
                    
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <div class="footer">
            <p>Po zakończeniu instalacji <strong>USUŃ</strong> ten plik (install-phpmailer.php) ze względów bezpieczeństwa.</p>
        </div>
    </div>
</body>
</html>