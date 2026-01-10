<?php
/**
 * LOGIN.PHP - Strona logowania do panelu admina
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/admin-auth.php';

// Pobierz ustawienia
$settings = getSettings();
$companyName = $settings['company_name'] ?? 'Maltechnik';

// Jeśli już zalogowany → dashboard
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Wpisz login i hasło';
    } else {
        if (loginAdmin($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Nieprawidłowy login lub hasło';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Panel Admina | <?php echo htmlspecialchars($companyName); ?></title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div style="margin-bottom: 30px;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#2c3e50" stroke-width="2" style="margin: 0 auto;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            
            <h1>Panel Admina</h1>
            <p style="color: #7f8c8d; margin-bottom: 30px;"><?php echo htmlspecialchars($companyName); ?></p>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="username" style="display: block; margin-bottom: 6px; font-weight: 600;">Login</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required 
                           autofocus
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 24px; text-align: left;">
                    <label for="password" style="display: block; margin-bottom: 6px; font-weight: 600;">Hasło</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Zaloguj się
                </button>
            </form>
            
            <p style="margin-top: 24px; font-size: 12px; color: #95a5a6;">
                Domyślne konto: <strong>admin</strong> / <strong>admin123</strong>
            </p>
        </div>
    </div>
</body>
</html>
