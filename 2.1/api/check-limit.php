<?php
/**
 * check-limit.php - Sprawdzanie limitu kalkulatora
 * Lokalizacja: /api/check-limit.php
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Tylko GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda niedozwolona']);
    exit;
}

try {
    // Pobierz fingerprint z query string
    $fingerprint = isset($_GET['fingerprint']) ? trim($_GET['fingerprint']) : '';
    
    if (empty($fingerprint)) {
        echo json_encode([
            'success' => false,
            'message' => 'Brak fingerprint'
        ]);
        exit;
    }
    
    // Sprawdź w bazie
    $sql = "SELECT 
                usage_count, 
                is_blocked,
                form_submitted,
                unlocked_at
            FROM calculator_device_limits 
            WHERE device_fingerprint = :fingerprint
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['fingerprint' => $fingerprint]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$device) {
        // Nowe urządzenie - utwórz rekord
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $insertSql = "INSERT INTO calculator_device_limits 
                      (device_fingerprint, ip_address, user_agent, usage_count, is_blocked) 
                      VALUES (:fingerprint, :ip, :ua, 0, 0)";
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            'fingerprint' => $fingerprint,
            'ip' => $ip,
            'ua' => $userAgent
        ]);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'usage_count' => 0,
                'is_blocked' => false,
                'can_use' => true,
                'form_submitted' => false
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [
                'usage_count' => (int)$device['usage_count'],
                'is_blocked' => (bool)$device['is_blocked'],
                'can_use' => !$device['is_blocked'],
                'form_submitted' => (bool)$device['form_submitted']
            ]
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Check limit error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd bazy danych'
    ]);
}
?>