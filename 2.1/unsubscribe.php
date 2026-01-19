<?php
/**
 * UNSUBSCRIBE.PHP - Wypisywanie się z newslettera
 */

session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$success = false;
$error = '';

// Jeśli email w URL, automatycznie wypisz
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    try {
        $stmt = $pdo->prepare("
            UPDATE marketing_consents 
            SET status = 'unsubscribed', 
                unsubscribed_at = NOW() 
            WHERE email = ? 
            AND status = 'active'
        ");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $success = true;
        } else {
            // Sprawdź czy użytkownik jest już wypisany
            $stmt = $pdo->prepare("SELECT status FROM marketing_consents WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['status'] === 'unsubscribed') {
                $error = 'Ten adres email jest już wypisany z newslettera.';
            } else {
                $error = 'Nie znaleziono aktywnej subskrypcji dla tego adresu email.';
            }
        }
    } catch (PDOException $e) {
        $error = 'Wystąpił błąd. Spróbuj ponownie później.';
        error_log('Unsubscribe error: ' . $e->getMessage());
    }
}

// Obsługa formularza (jeśli ktoś wejdzie bez parametru email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $emailPost = trim($_POST['email']);
    
    if (empty($emailPost)) {
        $error = 'Podaj adres email.';
    } elseif (!filter_var($emailPost, FILTER_VALIDATE_EMAIL)) {
        $error = 'Podaj prawidłowy adres email.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE marketing_consents 
                SET status = 'unsubscribed', 
                    unsubscribed_at = NOW() 
                WHERE email = ? 
                AND status = 'active'
            ");
            $stmt->execute([$emailPost]);
            
            if ($stmt->rowCount() > 0) {
                $success = true;
                $email = $emailPost;
            } else {
                $stmt = $pdo->prepare("SELECT status FROM marketing_consents WHERE email = ?");
                $stmt->execute([$emailPost]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && $result['status'] === 'unsubscribed') {
                    $error = 'Ten adres email jest już wypisany z newslettera.';
                } else {
                    $error = 'Nie znaleziono aktywnej subskrypcji dla tego adresu email.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Wystąpił błąd. Spróbuj ponownie później.';
            error_log('Unsubscribe error: ' . $e->getMessage());
        }
    }
}

// Pobierz ustawienia
$settings = getSettings();
$companyName = $settings['company_name'] ?? 'Maltechnik';

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wypisz się z newslettera - <?php echo htmlspecialchars($companyName); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2B59A6 0%, #1e3a8a 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #D1FAE5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: #FEE2E2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }
        
        .success-message {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-message h2 {
            font-size: 22px;
            color: #10B981;
            margin-bottom: 12px;
        }
        
        .success-message p {
            font-size: 15px;
            color: #6B7280;
            line-height: 1.6;
        }
        
        .success-message .email {
            display: inline-block;
            background: #F3F4F6;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            color: #374151;
            margin: 16px 0;
        }
        
        .error-message {
            background: #FEE2E2;
            border: 1px solid #FCA5A5;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            color: #991B1B;
            font-size: 14px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2B59A6;
        }
        
        .btn {
            width: 100%;
            padding: 14px 24px;
            background: #2B59A6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn:hover {
            background: #1e3a8a;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6B7280;
            margin-top: 12px;
        }
        
        .btn-secondary:hover {
            background: #4B5563;
        }
        
        .info-box {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
            font-size: 13px;
            color: #1E40AF;
            text-align: center;
        }
        
        .footer {
            background: #F9FAFB;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        
        .footer p {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 8px;
        }
        
        .footer a {
            color: #2B59A6;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($companyName); ?></h1>
            <p>Wypisz się z newslettera</p>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="success-icon">✓</div>
                <div class="success-message">
                    <h2>Pomyślnie wypisano</h2>
                    <p>Adres email:</p>
                    <span class="email"><?php echo htmlspecialchars($email); ?></span>
                    <p style="margin-top: 16px;">został wypisany z naszego newslettera.</p>
                    <p style="margin-top: 12px;">Nie będziesz już otrzymywać od nas wiadomości marketingowych.</p>
                </div>
                
                <a href="/" class="btn btn-secondary">
                    ← Powrót do strony głównej
                </a>
                
                <div class="info-box">
                    💡 Zmiana zdania? Możesz ponownie zapisać się do newslettera w stopce naszej strony.
                </div>
                
            <?php elseif (!empty($error)): ?>
                <div class="error-icon">✕</div>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Adres email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="twoj@email.pl" 
                            value="<?php echo htmlspecialchars($email); ?>"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn">
                        Wypisz się z newslettera
                    </button>
                </form>
                
                <a href="/" class="btn btn-secondary">
                    ← Powrót do strony głównej
                </a>
                
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 24px; color: #6B7280; font-size: 15px;">
                    Szkoda, że chcesz odejść! Jeśli na pewno chcesz wypisać się z naszego newslettera, podaj swój adres email poniżej.
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Adres email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="twoj@email.pl"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn">
                        Wypisz się z newslettera
                    </button>
                </form>
                
                <a href="/" class="btn btn-secondary">
                    ← Powrót do strony głównej
                </a>
                
                <div class="info-box">
                    ℹ️ Po wypisaniu nie będziesz już otrzymywać od nas wiadomości marketingowych.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. Wszelkie prawa zastrzeżone.</p>
            <p>
                <a href="/">Strona główna</a> | 
                <a href="/polityka-prywatnosci.php">Polityka prywatności</a>
            </p>
        </div>
    </div>
</body>
</html>