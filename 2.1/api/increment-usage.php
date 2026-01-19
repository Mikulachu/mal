<?php
/**
 * increment-usage.php - Zwiększanie licznika użyć
 * Lokalizacja: /api/increment-usage.php
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda niedozwolona']);
    exit;
}

try {
    // Pobierz dane JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $fingerprint = isset($data['fingerprint']) ? trim($data['fingerprint']) : '';
    
    if (empty($fingerprint)) {
        echo json_encode([
            'success' => false,
            'message' => 'Brak fingerprint'
        ]);
        exit;
    }
    
    // Zwiększ licznik
    $sql = "UPDATE calculator_device_limits 
            SET usage_count = usage_count + 1,
                is_blocked = CASE 
                    WHEN usage_count + 1 >= 3 THEN 1 
                    ELSE 0 
                END,
                last_usage = NOW()
            WHERE device_fingerprint = :fingerprint";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['fingerprint' => $fingerprint]);
    
    // Pobierz aktualny stan
    $selectSql = "SELECT usage_count, is_blocked 
                  FROM calculator_device_limits 
                  WHERE device_fingerprint = :fingerprint 
                  LIMIT 1";
    
    $selectStmt = $pdo->prepare($selectSql);
    $selectStmt->execute(['fingerprint' => $fingerprint]);
    $device = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($device) {
        echo json_encode([
            'success' => true,
            'data' => [
                'usage_count' => (int)$device['usage_count'],
                'is_blocked' => (bool)$device['is_blocked'],
                'limit_reached' => (int)$device['usage_count'] >= 3
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Urządzenie nie znalezione'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Increment usage error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd bazy danych'
    ]);
}
?>