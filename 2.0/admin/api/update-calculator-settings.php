<?php
/**
 * CALCULATOR-SETTINGS.PHP - API endpoint dla ustawień kalkulatora
  
 * 
 * LOKALIZACJA: /api/calculator-settings.php
 */

header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../admin/includes/admin-auth.php';

// Sprawdź czy zalogowany
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Nieautoryzowany dostęp']);
    exit;
}

// Pobierz dane JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane']);
    exit;
}

$calculatorActive = intval($data['calculator_active'] ?? 1);
$freeCalculations = intval($data['free_calculations'] ?? 5);
$laborPercentage = intval($data['labor_percentage'] ?? 40);

try {
    // Używamy PDO
    $settings = [
        'calculator_active' => $calculatorActive,
        'free_calculations_limit' => $freeCalculations,
        'default_labor_percentage' => $laborPercentage
    ];
    
    foreach ($settings as $key => $value) {
        // Update or insert
        $sql = "INSERT INTO site_settings (setting_key, setting_value, setting_type, updated_at) 
                VALUES (:key, :value, 'calculator', NOW()) 
                ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':key', $key);
        $stmt->bindValue(':value', $value);
        $stmt->execute();
    }
    
    // Logowanie
    try {
        $admin = getAdminData();
        logActivity($admin['id'], 'settings_update', 'calculator', 0, "Zaktualizowano ustawienia kalkulatora");
    } catch (Exception $e) {
        // Ignoruj
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ustawienia zapisane',
        'settings' => $settings
    ]);
    
} catch (PDOException $e) {
    error_log("Błąd aktualizacji ustawień: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Błąd bazy danych']);
}
?>
