<?php
/**
 * reset-limit.php - Resetowanie limitu kalkulatora
 * Lokalizacja: /api/reset-limit.php
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
    
    // Resetuj limit
    $sql = "UPDATE calculator_device_limits 
            SET usage_count = 0,
                is_blocked = 0,
                unlocked_at = NOW(),
                form_submitted = 1,
                last_usage = NOW()
            WHERE device_fingerprint = :fingerprint";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['fingerprint' => $fingerprint]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Limit zresetowany',
            'data' => [
                'usage_count' => 0,
                'is_blocked' => false,
                'can_use' => true
            ]
        ]);
    } else {
        // Urządzenie nie istnieje - utwórz z flagą form_submitted
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $insertSql = "INSERT INTO calculator_device_limits 
                      (device_fingerprint, ip_address, user_agent, usage_count, is_blocked, form_submitted, unlocked_at) 
                      VALUES (:fingerprint, :ip, :ua, 0, 0, 1, NOW())";
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            'fingerprint' => $fingerprint,
            'ip' => $ip,
            'ua' => $userAgent
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Urządzenie odblokowane',
            'data' => [
                'usage_count' => 0,
                'is_blocked' => false,
                'can_use' => true
            ]
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Reset limit error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd bazy danych'
    ]);
}
?>