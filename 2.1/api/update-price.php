<?php
/**
 * UPDATE-PRICE.PHP - API endpoint do aktualizacji cen
  
 * 
 * Dla panelu admina
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Sprawdź czy admin
// if (!isAdmin()) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Brak uprawnień']);
//     exit;
// }

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda niedozwolona']);
    exit;
}

// Pobierz dane JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane']);
    exit;
}

$id = $data['id'] ?? '';
$field = $data['field'] ?? '';
$value = isset($data['value']) ? floatval($data['value']) : null;

if (empty($id) || empty($field) || $value === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Brak wymaganych danych']);
    exit;
}

// Dozwolone pola do edycji
$allowedFields = ['price_standard', 'price_premium', 'labor_cost'];
if (!in_array($field, $allowedFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe pole']);
    exit;
}

try {
    $sql = "UPDATE price_list 
            SET $field = :value,
                updated_at = NOW()
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $id,
        'value' => $value
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Cena zaktualizowana',
            'data' => [
                'id' => $id,
                'field' => $field,
                'value' => $value
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Nie znaleziono usługi lub brak zmian'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Update price error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd bazy danych'
    ]);
}