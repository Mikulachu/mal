<?php
/**
 * GET-PRICES.PHP - API endpoint do pobierania cen cennika
 * 
 * Zwraca ceny z bazy danych w formacie JSON
 * Te same ceny są używane w cenniku i kalkulatorze
 */

// Ustaw nagłówki CORS i JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include plików - sprawdź różne możliwe ścieżki
$includesPath = __DIR__ . '/../includes/';

if (!file_exists($includesPath . 'functions.php')) {
    // Jeśli nie ma w ../includes, spróbuj bezpośrednio
    $includesPath = __DIR__ . '/../../includes/';
}

if (!file_exists($includesPath . 'functions.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd konfiguracji - nie można znaleźć plików includes',
        'debug' => [
            'current_dir' => __DIR__,
            'tried_path' => $includesPath
        ]
    ]);
    exit;
}

try {
    require_once $includesPath . 'functions.php';
    require_once $includesPath . 'db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd ładowania plików: ' . $e->getMessage()
    ]);
    exit;
}

try {
    $sql = "SELECT 
                id,
                name,
                category,
                price_standard,
                price_premium,
                labor_cost,
                description,
                active
            FROM price_list 
            WHERE active = 1 
            ORDER BY id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dla kalkulatora
    $formatted = [];
    foreach ($prices as $price) {
        $formatted[] = [
            'id' => (int)$price['id'],
            'name' => $price['name'],
            'category' => $price['category'],
            'price_standard' => (float)$price['price_standard'],
            'price_premium' => (float)($price['price_premium'] ?? 0),
            'labor_cost' => (float)$price['labor_cost'],
            'description' => $price['description'] ?? ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted,
        'count' => count($formatted)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd pobierania cen z bazy danych'
    ]);
    error_log('Get prices error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Nieoczekiwany błąd: ' . $e->getMessage()
    ]);
    error_log('Get prices unexpected error: ' . $e->getMessage());
}