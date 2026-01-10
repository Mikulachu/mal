<?php
/**
 * PRICING-HANDLER.PHP - API dla zarządzania cennikiem
 * UŻYWA TABELI: price_list (nie prices!)
 */

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $action = $_POST['action'] ?? 'save';
    
    switch ($action) {
        case 'save':
            saveService();
            break;
        case 'delete':
            deleteService();
            break;
        case 'add_category':
            addCategory();
            break;
        default:
            throw new Exception('Unknown action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Zapisz lub zaktualizuj usługę
 */
function saveService() {
    global $pdo;
    
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priceStandard = floatval($_POST['price_standard'] ?? 0);
    $laborCost = floatval($_POST['labor_cost'] ?? 0);
    
    // Walidacja
    if (empty($name)) {
        throw new Exception('Nazwa usługi jest wymagana');
    }
    
    if (empty($category)) {
        throw new Exception('Kategoria jest wymagana');
    }
    
    if ($priceStandard < 0) {
        throw new Exception('Cena nie może być ujemna');
    }
    
    if ($id) {
        // UPDATE w price_list
        $sql = "UPDATE price_list 
                SET name = :name,
                    description = :description,
                    category = :category,
                    price_standard = :price_standard,
                    price_premium = :price_premium,
                    labor_cost = :labor_cost,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'price_standard' => $priceStandard,
            'price_premium' => $priceStandard,
            'labor_cost' => $laborCost
        ]);
        
        $message = 'Usługa została zaktualizowana';
        
    } else {
        // INSERT do price_list
        $sql = "INSERT INTO price_list 
                (name, description, category, price_standard, price_premium, labor_cost, created_at, updated_at) 
                VALUES 
                (:name, :description, :category, :price_standard, :price_premium, :labor_cost, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'price_standard' => $priceStandard,
            'price_premium' => $priceStandard,
            'labor_cost' => $laborCost
        ]);
        
        $id = $pdo->lastInsertId();
        $message = 'Usługa została dodana';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'id' => $id
    ]);
}

/**
 * Usuń usługę
 */
function deleteService() {
    global $pdo;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('Nieprawidłowe ID');
    }
    
    $sql = "DELETE FROM price_list WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Usługa została usunięta'
        ]);
    } else {
        throw new Exception('Nie znaleziono usługi');
    }
}

/**
 * Dodaj nową kategorię
 */
function addCategory() {
    global $pdo;
    
    $categoryName = trim($_POST['category_name'] ?? '');
    
    if (empty($categoryName)) {
        throw new Exception('Nazwa kategorii jest wymagana');
    }
    
    // Walidacja długości
    if (strlen($categoryName) > 50) {
        throw new Exception('Nazwa kategorii za długa (max 50 znaków)');
    }
    
    // Usuń polskie znaki dla bezpieczeństwa
    $categoryNameSafe = strtolower($categoryName);
    $categoryNameSafe = str_replace(
        ['ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż'],
        ['a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z'],
        $categoryNameSafe
    );
    
    // Tylko litery i myślniki
    $categoryNameSafe = preg_replace('/[^a-z\-]/', '', $categoryNameSafe);
    
    if (empty($categoryNameSafe)) {
        throw new Exception('Nazwa kategorii zawiera nieprawidłowe znaki. Użyj tylko liter bez polskich znaków.');
    }
    
    // Sprawdź czy kategoria już istnieje
    $checkSql = "SELECT COUNT(*) FROM price_list WHERE category = :category";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['category' => $categoryNameSafe]);
    
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception('Kategoria "' . $categoryNameSafe . '" już istnieje');
    }
    
    // Dodaj placeholder
    $sql = "INSERT INTO price_list 
            (name, description, category, price_standard, price_premium, labor_cost, created_at, updated_at) 
            VALUES 
            ('Placeholder - usuń lub edytuj', 'Usługa tymczasowa', :category, 0, 0, 0, NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category' => $categoryNameSafe]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Kategoria "' . $categoryNameSafe . '" została utworzona',
        'category' => $categoryNameSafe
    ]);
}
?>