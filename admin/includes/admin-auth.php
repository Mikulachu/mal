<?php
/**
 * ADMIN-AUTH.PHP - System autoryzacji administratora (FIXED)
  
 * 
 * LOKALIZACJA: /admin/includes/admin-auth.php
 */

// Start sesji jeśli nie została rozpoczęta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sprawdza czy admin jest zalogowany
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Wymaga logowania - przekierowuje do login.php jeśli nie zalogowany
 */
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Logowanie admina
 */
function loginAdmin($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, password_hash, email, full_name FROM admin_users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password_hash'])) {
            // Ustaw sesję
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_logged_in'] = true;
            
            // Aktualizuj ostatnie logowanie
            $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            // Log aktywności (z obsługą błędów)
            try {
                logActivity($user['id'], 'login', 'admin', $user['id'], 'Zalogowano do panelu');
            } catch (Exception $e) {
                // Ignoruj błędy logowania aktywności
            }
            
            return true;
        }
    }
    
    return false;
}

/**
 * Wylogowanie admina
 */
function logoutAdmin() {
    if (isset($_SESSION['admin_id'])) {
        $adminId = $_SESSION['admin_id'];
        
        // Log aktywności (z obsługą błędów)
        try {
            logActivity($adminId, 'logout', 'admin', $adminId, 'Wylogowano z panelu');
        } catch (Exception $e) {
            // Ignoruj błędy logowania aktywności
        }
    }
    
    // Zniszcz sesję
    session_unset();
    session_destroy();
    
    return true;
}

/**
 * Pobiera dane zalogowanego admina
 */
function getAdminData() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'name' => $_SESSION['admin_name'] ?? 'Admin'
    ];
}

/**
 * Pobierz IP użytkownika
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

/**
 * Loguje aktywność admina
 */
function logActivity($adminId, $action, $entityType = null, $entityId = null, $details = null) {
    global $conn;
    
    // Sprawdź czy połączenie istnieje
    if (!$conn) {
        return false;
    }
    
    // Sprawdź czy tabela istnieje
    try {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_activity_log'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            return false; // Tabela nie istnieje, nie loguj
        }
    } catch (Exception $e) {
        return false;
    }
    
    $ip = getUserIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO admin_activity_log 
            (admin_id, action_type, target_type, target_id, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt) {
            $stmt->bind_param("issssss", $adminId, $action, $entityType, $entityId, $details, $ip, $userAgent);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    } catch (Exception $e) {
        // Ignoruj błędy - logowanie aktywności nie jest krytyczne
        return false;
    }
    
    return false;
}