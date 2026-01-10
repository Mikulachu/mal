<?php
/**
 * GET-PRICES.PHP - API endpoint do pobierania cen cennika
  
 * 
 * Zwraca ceny z bazy danych w formacie JSON
 * Te same ceny są używane w cenniku i kalkulatorze
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                id,
                name,
                category,
                price_standard,
                price_premium,
                labor_cost,
                description,
                active,
                sort_order
            FROM price_list 
            WHERE active = 1 
            ORDER BY sort_order ASC, id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dla kalkulatora
    $formatted = [];
    foreach ($prices as $price) {
        $formatted[] = [
            'id' => $price['id'],
            'name' => $price['name'],
            'category' => $price['category'],
            'price_standard' => (float)$price['price_standard'],
            'price_premium' => (float)$price['price_premium'],
            'labor_cost' => (float)$price['labor_cost'],
            'description' => $price['description'],
            'sort_order' => (int)$price['sort_order']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd pobierania cen'
    ]);
    error_log('Get prices error: ' . $e->getMessage());
}