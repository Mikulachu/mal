<?php
/**
 * UPDATE-PRICE.PHP - API endpoint dla aktualizacji cen
  
 * 
 * LOKALIZACJA: /api/update-price.php
 * Używa tabeli: price_list (zgodnie z Twoim systemem)
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

$id = intval($data['id'] ?? 0);
$field = $data['field'] ?? '';
$value = $data['value'] ?? null;

if (!$id || !$field) {
    echo json_encode(['success' => false, 'message' => 'Brak wymaganych parametrów']);
    exit;
}

// Dozwolone pola do edycji
$allowedFields = ['price_standard', 'price_premium', 'labor_cost', 'active'];

if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe pole']);
    exit;
}

try {
    // Używamy PDO zgodnie z Twoim systemem
    $sql = "UPDATE price_list SET {$field} = :value, updated_at = NOW() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':value', $value);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Logowanie aktywności (opcjonalne)
        try {
            $admin = getAdminData();
            logActivity($admin['id'], 'pricing_update', 'price_list', $id, "Zaktualizowano {$field} = {$value}");
        } catch (Exception $e) {
            // Ignoruj błędy logowania
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cena zaktualizowana',
            'field' => $field,
            'value' => $value
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nie znaleziono usługi']);
    }
    
} catch (PDOException $e) {
    error_log("Błąd aktualizacji ceny: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Błąd bazy danych']);
}
?>
