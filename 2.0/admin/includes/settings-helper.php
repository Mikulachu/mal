<?php
/**
 * SETTINGS-HELPER.PHP - Funkcje pomocnicze dla ustawień
  
 * 
 * LOKALIZACJA: /admin/includes/settings-helper.php
 * Użycie: require_once w settings.php
 */

/**
 * Zapisz/zaktualizuj ustawienie (INSERT or UPDATE)
 * 
 * @param string $key Klucz ustawienia
 * @param string $value Wartość
 * @param string $type Typ (company, notifications, system)
 * @return bool
 */
function saveSetting($key, $value, $type = 'general') {
    global $conn;
    
    try {
        // Sprawdź czy ustawienie istnieje
        $check = $conn->prepare("SELECT id FROM site_settings WHERE setting_key = ? LIMIT 1");
        $check->bind_param("s", $key);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("sss", $key, $value, $type);
        }
        
        return $stmt->execute();
        
    } catch (Exception $e) {
        error_log("saveSetting error: " . $e->getMessage());
        return false;
    }
}

/**
 * Pobierz ustawienie
 * 
 * @param string $key Klucz ustawienia
 * @param mixed $default Wartość domyślna
 * @return string
 */
function getSetting($key, $default = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['setting_value'];
        }
        
        return $default;
        
    } catch (Exception $e) {
        error_log("getSetting error: " . $e->getMessage());
        return $default;
    }
}

/**
 * Pobierz wszystkie ustawienia jako array
 * 
 * @return array [key => value]
 */
function getAllSettings() {
    global $conn;
    
    $settings = [];
    
    try {
        $result = $conn->query("SELECT setting_key, setting_value FROM site_settings");
        
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
    } catch (Exception $e) {
        error_log("getAllSettings error: " . $e->getMessage());
    }
    
    return $settings;
}
?>
