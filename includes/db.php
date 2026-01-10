<?php
/**
 * Połączenie z bazą danych
 * Plik konfiguracyjny - dostosuj dane do swojego hostingu
 */

// Konfiguracja bazy danych
define('DB_HOST', 'localhost');
define('DB_USER', 'mikulachu');
define('DB_PASS', 'Miku.lachu18');
define('DB_NAME', 'nauka2');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// PDO - DLA STRONY PUBLICZNEJ
// ============================================

// Opcje PDO dla bezpieczeństwa
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Połączenie z bazą
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // W produkcji NIE pokazuj szczegółów błędu
    error_log("Błąd połączenia z bazą: " . $e->getMessage());
    die("Przepraszamy, wystąpił problem z połączeniem. Spróbuj ponownie później.");
}

/**
 * Funkcja pomocnicza do wykonywania zapytań
 */
function dbQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Błąd zapytania SQL: " . $e->getMessage());
        return false;
    }
}

/**
 * Funkcja pomocnicza do pobierania jednego wiersza
 */
function dbFetchOne($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Funkcja pomocnicza do pobierania wszystkich wierszy
 */
function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

/**
 * Funkcja pomocnicza do insertu
 */
function dbInsert($sql, $params = []) {
    global $pdo;
    $stmt = dbQuery($sql, $params);
    return $stmt ? $pdo->lastInsertId() : false;
}

// ============================================
// MYSQLI - DLA PANELU ADMINA
// ============================================

// Utworz połączenie MySQLi dla panelu admina
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Sprawdź połączenie
if ($conn->connect_error) {
    error_log("MySQLi connection error: " . $conn->connect_error);
    // Nie przerywaj działania - PDO może nadal działać
}

// Ustaw charset
if ($conn) {
    $conn->set_charset(DB_CHARSET);
}

?>