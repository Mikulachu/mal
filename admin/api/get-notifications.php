<?php
/**
 * GET-NOTIFICATIONS.PHP - API zwracające powiadomienia
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

header('Content-Type: application/json');

try {
    $notifications = [];
    
    // ============================================
    // NOWE KONSULTACJE
    // ============================================
    $newConsultations = $pdo->query("
        SELECT COUNT(*) as count 
        FROM consultations 
        WHERE status = 'new'
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($newConsultations['count'] > 0) {
        $notifications[] = [
            'type' => 'consultation',
            'count' => $newConsultations['count'],
            'message' => $newConsultations['count'] . ' ' . 
                        ($newConsultations['count'] == 1 ? 'nowa konsultacja' : 'nowe konsultacje'),
            'link' => '/admin/consultations.php?status=new',
            'icon' => '📞'
        ];
    }
    
    // ============================================
    // NOWE ZAPYTANIA (LEADS)
    // ============================================
    $newLeads = $pdo->query("
        SELECT COUNT(*) as count 
        FROM leads 
        WHERE status = 'new'
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($newLeads['count'] > 0) {
        $notifications[] = [
            'type' => 'lead',
            'count' => $newLeads['count'],
            'message' => $newLeads['count'] . ' ' . 
                        ($newLeads['count'] == 1 ? 'nowe zapytanie' : 'nowe zapytania'),
            'link' => '/admin/leads.php?status=new',
            'icon' => '❓'
        ];
    }
    
    // ============================================
    // PRIORYTETOWE LEADY
    // ============================================
    $highPriorityLeads = $pdo->query("
        SELECT COUNT(*) as count 
        FROM leads 
        WHERE priority = 'high' 
        AND status NOT IN ('won', 'lost')
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($highPriorityLeads['count'] > 0) {
        $notifications[] = [
            'type' => 'priority',
            'count' => $highPriorityLeads['count'],
            'message' => $highPriorityLeads['count'] . ' pilne ' . 
                        ($highPriorityLeads['count'] == 1 ? 'zapytanie' : 'zapytań'),
            'link' => '/admin/leads.php?status=new',
            'icon' => '🔥',
            'urgent' => true
        ];
    }
    
    // ============================================
    // ZAPLANOWANE KONSULTACJE (DZISIAJ)
    // ============================================
    $todayConsultations = $pdo->query("
        SELECT COUNT(*) as count 
        FROM consultations 
        WHERE status = 'scheduled' 
        AND DATE(preferred_date) = CURDATE()
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($todayConsultations['count'] > 0) {
        $notifications[] = [
            'type' => 'today',
            'count' => $todayConsultations['count'],
            'message' => 'Dzisiaj: ' . $todayConsultations['count'] . ' ' . 
                        ($todayConsultations['count'] == 1 ? 'konsultacja' : 'konsultacje'),
            'link' => '/admin/consultations.php?status=scheduled',
            'icon' => '📅'
        ];
    }
    
    // ============================================
    // SUMA WSZYSTKICH
    // ============================================
    $totalCount = array_sum(array_column($notifications, 'count'));
    
    echo json_encode([
        'success' => true,
        'total' => $totalCount,
        'notifications' => $notifications
    ]);
    
} catch (PDOException $e) {
    error_log("Błąd pobierania powiadomień: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'total' => 0,
        'notifications' => []
    ]);
}
?>
