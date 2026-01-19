<?php
/**
 * SETTINGS.PHP - Ustawienia ogólne strony (PDO)
 * LOKALIZACJA: /admin/settings.php
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Ustawienia';
$currentPage = 'settings';
$admin = getAdminData();

$success = '';
$errors = [];

// ============================================
// AKTUALIZACJA USTAWIEŃ
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_company') {
        $companyName = trim($_POST['company_name'] ?? '');
        $companyPhone = trim($_POST['company_phone'] ?? '');
        $companyEmail = trim($_POST['company_email'] ?? '');
        $companyAddress = trim($_POST['company_address'] ?? '');
        $companyDescription = trim($_POST['company_description'] ?? '');
        $companyNip = trim($_POST['company_nip'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$companyName, 'company_name']);
            $stmt->execute([$companyPhone, 'company_phone']);
            $stmt->execute([$companyEmail, 'company_email']);
            $stmt->execute([$companyAddress, 'company_address']);
            $stmt->execute([$companyDescription, 'company_description']);
            $stmt->execute([$companyNip, 'company_nip']);
            
            logActivity($admin['id'], 'settings_company_update', 'settings', 0, "Zaktualizowano dane firmy");
            $success = 'company';
        } catch (PDOException $e) {
            $errors[] = 'Błąd podczas zapisywania danych firmy';
        }
    }
    
    if ($_POST['action'] === 'update_notifications') {
        $notificationEmail = trim($_POST['notification_email'] ?? '');
        $notificationsEnabled = isset($_POST['notifications_enabled']) ? '1' : '0';
        $emailOnLead = isset($_POST['email_on_lead']) ? '1' : '0';
        $emailOnConsultation = isset($_POST['email_on_consultation']) ? '1' : '0';
        $emailOnCalculation = isset($_POST['email_on_calculation']) ? '1' : '0';
        
        try {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$notificationEmail, 'notification_email']);
            $stmt->execute([$notificationsEnabled, 'notifications_enabled']);
            $stmt->execute([$emailOnLead, 'email_on_lead']);
            $stmt->execute([$emailOnConsultation, 'email_on_consultation']);
            $stmt->execute([$emailOnCalculation, 'email_on_calculation']);
            
            logActivity($admin['id'], 'settings_notifications_update', 'settings', 0, "Zaktualizowano powiadomienia");
            $success = 'notifications';
        } catch (PDOException $e) {
            $errors[] = 'Błąd podczas zapisywania powiadomień';
        }
    }
    
    if ($_POST['action'] === 'update_system') {
        $maintenanceMode = isset($_POST['maintenance_mode']) ? '1' : '0';
        $googleAnalytics = trim($_POST['google_analytics'] ?? '');
        $facebookPixel = trim($_POST['facebook_pixel'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$maintenanceMode, 'maintenance_mode']);
            $stmt->execute([$googleAnalytics, 'google_analytics']);
            $stmt->execute([$facebookPixel, 'facebook_pixel']);
            
            logActivity($admin['id'], 'settings_system_update', 'settings', 0, "Zaktualizowano ustawienia systemu");
            $success = 'system';
        } catch (PDOException $e) {
            $errors[] = 'Błąd podczas zapisywania ustawień systemu';
        }
    }
    
    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        try {
            // Pobierz obecne hasło
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$admin['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($currentPassword, $result['password_hash'])) {
                $errors[] = 'Obecne hasło jest nieprawidłowe';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'Nowe hasła nie są identyczne';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'Hasło musi mieć minimum 6 znaków';
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $admin['id']]);
                
                logActivity($admin['id'], 'password_change', 'admin', $admin['id'], "Zmieniono hasło");
                $success = 'password';
            }
        } catch (PDOException $e) {
            $errors[] = 'Błąd podczas zmiany hasła';
        }
    }
}

// ============================================
// POBIERZ USTAWIENIA
// ============================================

$settings = $pdo->query("SELECT * FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
$settingsData = [];
foreach ($settings as $setting) {
    $settingsData[$setting['setting_key']] = $setting['setting_value'];
}

// Jeśli brak niektórych ustawień, dodaj domyślne
$defaultSettings = [
    'company_name' => 'Maltechnik',
    'company_phone' => '+48 784 607 452',
    'company_email' => 'maltechnik.chojnice@gmail.com',
    'company_address' => '89-600 Chojnice, Ul. Tischnera 8',
    'company_description' => 'Profesjonalne usługi remontowe premium. Zdejmiemy Ci stres z remontu.',
    'company_nip' => '5552130861',
    'notification_email' => 'maltechnik.chojnice@gmail.com',
    'notifications_enabled' => '1',
    'email_on_lead' => '1',
    'email_on_consultation' => '1',
    'email_on_calculation' => '0',
    'maintenance_mode' => '0',
    'google_analytics' => '',
    'facebook_pixel' => ''
];

foreach ($defaultSettings as $key => $value) {
    if (!isset($settingsData[$key])) {
        $settingsData[$key] = $value;
    }
}

// Dla admin-header.php
$companyName = $settingsData['company_name'] ?? 'Maltechnik';

?>
<?php include 'includes/admin-header.php'; ?>

<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom: 24px;">
    <?php 
    $messages = [
        'company' => 'Dane firmy zostały zaktualizowane',
        'notifications' => 'Ustawienia powiadomień zostały zapisane',
        'system' => 'Ustawienia systemu zostały zapisane',
        'password' => 'Hasło zostało zmienione'
    ];
    echo $messages[$success] ?? 'Zapisano';
    ?>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 24px;">
    <ul style="margin: 0; padding-left: 20px;">
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="content-header">
    <div>
        <h1>Ustawienia</h1>
        <p style="color: var(--text-secondary); margin-top: 8px;">
            Konfiguracja strony i konta administratora
        </p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    
    <!-- LEWA KOLUMNA: USTAWIENIA -->
    <div>
        
        <!-- DANE FIRMY -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Dane firmy</h2>
            </div>
            <div style="padding: 24px;">
                <form method="POST">
                    <input type="hidden" name="action" value="update_company">
                    
                    <div class="form-group">
                        <label for="company_name">Nazwa firmy</label>
                        <input type="text" 
                               id="company_name" 
                               name="company_name" 
                               class="form-control"
                               value="<?php echo htmlspecialchars($settingsData['company_name']); ?>">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="company_phone">Telefon</label>
                            <input type="tel" 
                                   id="company_phone" 
                                   name="company_phone" 
                                   class="form-control"
                                   placeholder="+48 784 607 452"
                                   value="<?php echo htmlspecialchars($settingsData['company_phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="company_email">Email</label>
                            <input type="email" 
                                   id="company_email" 
                                   name="company_email" 
                                   class="form-control"
                                   placeholder="maltechnik.chojnice@gmail.com"
                                   value="<?php echo htmlspecialchars($settingsData['company_email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_address">Adres</label>
                        <input type="text" 
                               id="company_address" 
                               name="company_address" 
                               class="form-control"
                               placeholder="89-600 Chojnice, Ul. Tischnera 8"
                               value="<?php echo htmlspecialchars($settingsData['company_address']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="company_description">Opis (footer, meta)</label>
                        <textarea id="company_description" 
                                  name="company_description" 
                                  class="form-control"
                                  rows="3"
                                  placeholder="Profesjonalne usługi remontowe premium..."><?php echo htmlspecialchars($settingsData['company_description']); ?></textarea>
                        <small style="color: var(--text-secondary); font-size: 11px; display: block; margin-top: 4px;">
                            Krótki opis wyświetlany w stopce strony
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_nip">NIP</label>
                        <input type="text" 
                               id="company_nip" 
                               name="company_nip" 
                               class="form-control"
                               placeholder="5552130861"
                               value="<?php echo htmlspecialchars($settingsData['company_nip']); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Zapisz dane firmy
                    </button>
                </form>
            </div>
        </div>
        
        <!-- POWIADOMIENIA EMAIL -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Powiadomienia email</h2>
            </div>
            <div style="padding: 24px;">
                <form method="POST">
                    <input type="hidden" name="action" value="update_notifications">
                    
                    <div class="form-group">
                        <label for="notification_email">Email do powiadomień</label>
                        <input type="email" 
                               id="notification_email" 
                               name="notification_email" 
                               class="form-control"
                               placeholder="maltechnik.chojnice@gmail.com"
                               value="<?php echo htmlspecialchars($settingsData['notification_email']); ?>">
                        <small style="color: var(--text-secondary); font-size: 11px; display: block; margin-top: 4px;">
                            Na ten adres będą wysyłane powiadomienia
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-group-checkbox">
                            <label>
                                <input type="checkbox" 
                                       name="notifications_enabled" 
                                       value="1"
                                       <?php echo $settingsData['notifications_enabled'] == '1' ? 'checked' : ''; ?>>
                                <span>Włącz powiadomienia email</span>
                            </label>
                        </div>
                    </div>
                    
                    <div style="padding: 16px; background: var(--bg-body); border-radius: 8px; margin-bottom: 16px;">
                        <strong style="font-size: 13px; display: block; margin-bottom: 12px;">Wysyłaj powiadomienie gdy:</strong>
                        
                        <div class="form-group-checkbox">
                            <label>
                                <input type="checkbox" 
                                       name="email_on_lead" 
                                       value="1"
                                       <?php echo $settingsData['email_on_lead'] == '1' ? 'checked' : ''; ?>>
                                <span>Nowe zapytanie</span>
                            </label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <label>
                                <input type="checkbox" 
                                       name="email_on_consultation" 
                                       value="1"
                                       <?php echo $settingsData['email_on_consultation'] == '1' ? 'checked' : ''; ?>>
                                <span>Zgłoszenie na konsultację</span>
                            </label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <label>
                                <input type="checkbox" 
                                       name="email_on_calculation" 
                                       value="1"
                                       <?php echo $settingsData['email_on_calculation'] == '1' ? 'checked' : ''; ?>>
                                <span>Nowe wyliczenie w kalkulatorze (z emailem)</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Zapisz powiadomienia
                    </button>
                </form>
            </div>
        </div>
        
        <!-- USTAWIENIA SYSTEMU -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Ustawienia systemu</h2>
            </div>
            <div style="padding: 24px;">
                <form method="POST">
                    <input type="hidden" name="action" value="update_system">
                    
                    <div class="form-group">
                        <div class="form-group-checkbox">
                            <label>
                                <input type="checkbox" 
                                       name="maintenance_mode" 
                                       value="1"
                                       <?php echo $settingsData['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                <span>Tryb konserwacji</span>
                            </label>
                            <small style="color: var(--text-secondary); font-size: 11px; display: block; margin-top: 4px;">
                                Wyłącza stronę dla użytkowników (admin ma dostęp)
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="google_analytics">Google Analytics ID</label>
                        <input type="text" 
                               id="google_analytics" 
                               name="google_analytics" 
                               class="form-control"
                               placeholder="G-XXXXXXXXXX lub UA-XXXXXXXXX-X"
                               value="<?php echo htmlspecialchars($settingsData['google_analytics']); ?>">
                        <small style="color: var(--text-secondary); font-size: 11px; display: block; margin-top: 4px;">
                            ID śledzenia Google Analytics
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="facebook_pixel">Facebook Pixel ID</label>
                        <input type="text" 
                               id="facebook_pixel" 
                               name="facebook_pixel" 
                               class="form-control"
                               placeholder="000000000000000"
                               value="<?php echo htmlspecialchars($settingsData['facebook_pixel']); ?>">
                        <small style="color: var(--text-secondary); font-size: 11px; display: block; margin-top: 4px;">
                            ID piksela Facebook
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Zapisz ustawienia systemu
                    </button>
                </form>
            </div>
        </div>
        
    </div>
    
    <!-- PRAWA KOLUMNA: KONTO ADMINA -->
    <div>
        
        <!-- INFORMACJE O KONCIE -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Twoje konto</h2>
            </div>
            <div style="padding: 20px;">
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Login</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin['username']); ?></div>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Email</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin['email']); ?></div>
                </div>
                
                <div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Imię i nazwisko</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin['full_name'] ?? $admin['name']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- ZMIANA HASŁA -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Zmiana hasła</h2>
            </div>
            <div style="padding: 20px;">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Obecne hasło</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="form-control"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nowe hasło</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-control"
                               minlength="6"
                               required>
                        <small style="color: var(--text-secondary); font-size: 11px; display: block; margin-top: 4px;">
                            Minimum 6 znaków
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Powtórz hasło</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control"
                               minlength="6"
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Zmień hasło
                    </button>
                </form>
            </div>
        </div>
        
        <!-- POMOC -->
        <div class="content-card">
            <div style="padding: 20px;">
                <h3 style="font-size: 14px; margin-bottom: 12px;">💡 Wskazówki</h3>
                <ul style="font-size: 12px; line-height: 1.8; color: var(--text-secondary); padding-left: 20px; margin: 0;">
                    <li>Dane firmy wyświetlają się na stronie</li>
                    <li>Powiadomienia pomagają nie przegapić leadów</li>
                    <li>Tryb konserwacji wyłącza stronę</li>
                    <li>Google Analytics śledzi ruch na stronie</li>
                    <li>Regularnie zmieniaj hasło</li>
                </ul>
            </div>
        </div>
        
    </div>
    
</div>

<style>
.alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #4caf50;
    padding: 16px 20px;
    border-radius: 8px;
}
.alert-error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ef5350;
    padding: 16px 20px;
    border-radius: 8px;
}
</style>

<?php include 'includes/admin-footer.php'; ?>