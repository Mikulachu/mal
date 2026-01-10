<?php
/**
 * CONSULTATION-DETAIL.PHP - Szczegóły konsultacji + zarządzanie
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$consultId = $_GET['id'] ?? 0;
$admin = getAdminData();

// POBIERZ KONSULTACJĘ
$stmt = $pdo->prepare("SELECT * FROM consultations WHERE id = ?");
$stmt->execute([$consultId]);
$consult = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$consult) {
    header('Location: consultations.php');
    exit;
}

$pageTitle = 'Konsultacja #' . $consultId;
$currentPage = 'consultations';

// POBIERZ NOTATKI (jeśli tabela istnieje)
try {
    $notes = $pdo->query("
        SELECT 
            cn.*,
            au.full_name as admin_name
        FROM consultation_notes cn
        LEFT JOIN admin_users au ON cn.admin_id = au.id
        WHERE cn.consultation_id = {$consultId}
        ORDER BY cn.created_at DESC
    ");
} catch (PDOException $e) {
    $notes = null; // Tabela nie istnieje
}

// ============================================
// AKCJE
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Zmiana statusu
    if ($_POST['action'] === 'change_status') {
        $newStatus = $_POST['status'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE consultations SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $consultId]);
        
        header("Location: consultation-detail.php?id={$consultId}&success=status");
        exit;
    }
    
    // Dodanie notatki
    if ($_POST['action'] === 'add_note') {
        $noteText = trim($_POST['note_text'] ?? '');
        
        if ($noteText) {
            // Sprawdź czy tabela consultation_notes istnieje
            try {
                $stmt = $pdo->prepare("INSERT INTO consultation_notes (consultation_id, admin_id, note_text, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$consultId, $admin['id'], $noteText]);
            } catch (PDOException $e) {
                // Jeśli tabela nie istnieje, zapisz w polu notes
                $stmt = $pdo->prepare("UPDATE consultations SET notes = CONCAT(COALESCE(notes, ''), ?, '\n') WHERE id = ?");
                $noteWithDate = '[' . date('Y-m-d H:i') . '] ' . $admin['full_name'] . ': ' . $noteText;
                $stmt->execute([$noteWithDate, $consultId]);
            }
            
            header("Location: consultation-detail.php?id={$consultId}&success=note");
            exit;
        }
    }
    
    // Usunięcie
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM consultations WHERE id = ?");
        $stmt->execute([$consultId]);
        
        header("Location: consultations.php?success=delete");
        exit;
    }
}

?>
<?php include 'includes/admin-header.php'; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success" style="margin-bottom: 24px;">
    <?php 
    $messages = [
        'status' => '✓ Status został zmieniony',
        'note' => '✓ Notatka została dodana'
    ];
    echo $messages[$_GET['success']] ?? '✓ Zapisano';
    ?>
</div>
<?php endif; ?>

<div style="display: flex; gap: 24px; align-items: center; margin-bottom: 24px;">
    <a href="consultations.php" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5"/>
            <path d="M12 19l-7-7 7-7"/>
        </svg>
        Powrót
    </a>
    <div>
        <h1 style="margin-bottom: 4px;">Konsultacja #<?php echo $consultId; ?></h1>
        <p style="color: var(--text-secondary); font-size: 14px;">
            Otrzymana: <?php echo date('d.m.Y H:i', strtotime($consult['created_at'])); ?>
        </p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    
    <!-- LEWA KOLUMNA: SZCZEGÓŁY -->
    <div>
        
        <!-- INFORMACJE O KLIENCIE -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Informacje o kliencie</h2>
                <div style="display: flex; gap: 8px;">
                    <?php 
                    $statusClass = [
                        'new' => 'badge-new',
                        'scheduled' => 'badge-info',
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-danger'
                    ];
                    $statusLabel = [
                        'new' => 'Nowa',
                        'scheduled' => 'Zaplanowana',
                        'completed' => 'Zakończona',
                        'cancelled' => 'Anulowana'
                    ];
                    ?>
                    <span class="badge <?php echo $statusClass[$consult['status']] ?? ''; ?>">
                        <?php echo $statusLabel[$consult['status']] ?? $consult['status']; ?>
                    </span>
                </div>
            </div>
            
            <div style="padding: 24px;">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Imię i nazwisko</div>
                        <div class="info-value">
                            <strong><?php echo htmlspecialchars($consult['name']); ?></strong>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Telefon</div>
                        <div class="info-value">
                            <?php if ($consult['phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($consult['phone']); ?>" class="contact-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <?php echo htmlspecialchars($consult['phone']); ?>
                            </a>
                            <?php else: ?>
                            <span style="color: var(--text-secondary);">Brak</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <?php if ($consult['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($consult['email']); ?>" class="contact-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <?php echo htmlspecialchars($consult['email']); ?>
                            </a>
                            <?php else: ?>
                            <span style="color: var(--text-secondary);">Brak</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Zgoda marketingowa</div>
                        <div class="info-value">
                            <?php if (isset($consult['marketing_consent']) && $consult['marketing_consent']): ?>
                                ✅ Tak
                            <?php else: ?>
                                ❌ Nie
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($consult['topic']): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <div class="info-label" style="margin-bottom: 12px;">Temat konsultacji</div>
                    <div style="background: var(--bg-body); padding: 16px; border-radius: 8px; line-height: 1.6; white-space: pre-wrap;">
                        <?php echo htmlspecialchars($consult['topic']); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($consult['notes']) && $consult['notes']): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <div class="info-label" style="margin-bottom: 12px;">Historia notatek</div>
                    <div style="background: #fff3e0; padding: 16px; border-radius: 8px; line-height: 1.6; white-space: pre-wrap; border: 1px solid #f57c00;">
                        <?php echo nl2br(htmlspecialchars($consult['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- NOTATKI -->
        <div class="content-card">
            <div class="card-header">
                <h2>Notatki</h2>
            </div>
            
            <div style="padding: 24px;">
                <!-- DODAJ NOTATKĘ -->
                <form method="POST" class="add-note-form">
                    <input type="hidden" name="action" value="add_note">
                    <textarea name="note_text" 
                              placeholder="Dodaj notatkę..." 
                              rows="3" 
                              class="form-control"
                              style="margin-bottom: 12px;"
                              required></textarea>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14"/>
                            <path d="M5 12h14"/>
                        </svg>
                        Dodaj notatkę
                    </button>
                </form>
                
                <!-- LISTA NOTATEK -->
                <?php if ($notes && $notes->rowCount() > 0): ?>
                <div style="margin-top: 24px;">
                    <?php while ($note = $notes->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="note-item">
                        <div class="note-header">
                            <strong><?php echo htmlspecialchars($note['admin_name'] ?? 'Admin'); ?></strong>
                            <span><?php echo date('d.m.Y H:i', strtotime($note['created_at'])); ?></span>
                        </div>
                        <div class="note-text">
                            <?php echo nl2br(htmlspecialchars($note['note_text'])); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 20px 0; margin-top: 24px;">
                    Brak notatek
                </p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <!-- PRAWA KOLUMNA: AKCJE -->
    <div>
        
        <!-- ZMIEŃ STATUS -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Zmień status</h2>
            </div>
            <div style="padding: 20px;">
                <form method="POST">
                    <input type="hidden" name="action" value="change_status">
                    <select name="status" class="form-control" style="margin-bottom: 12px;">
                        <option value="new" <?php echo $consult['status'] === 'new' ? 'selected' : ''; ?>>🆕 Nowa</option>
                        <option value="scheduled" <?php echo $consult['status'] === 'scheduled' ? 'selected' : ''; ?>>📅 Zaplanowana</option>
                        <option value="completed" <?php echo $consult['status'] === 'completed' ? 'selected' : ''; ?>>✅ Zakończona</option>
                        <option value="cancelled" <?php echo $consult['status'] === 'cancelled' ? 'selected' : ''; ?>>❌ Anulowana</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-block">Zapisz</button>
                </form>
            </div>
        </div>
        
        <!-- SZYBKIE AKCJE -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Akcje</h2>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
                <?php if ($consult['phone']): ?>
                <a href="tel:<?php echo htmlspecialchars($consult['phone']); ?>" class="btn btn-secondary btn-block">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    Zadzwoń
                </a>
                <?php endif; ?>
                
                <?php if ($consult['email']): ?>
                <a href="mailto:<?php echo htmlspecialchars($consult['email']); ?>?subject=Odpowiedź na konsultację&body=Dzień dobry <?php echo htmlspecialchars(explode(' ', $consult['name'])[0]); ?>,%0D%0A%0D%0ADziękujemy za zgłoszenie konsultacji online.%0D%0A%0D%0APozdrawiamy," class="btn btn-secondary btn-block">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Wyślij email
                </a>
                <?php endif; ?>
                
                <form method="POST" onsubmit="return confirm('Czy na pewno usunąć tę konsultację?');" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger btn-block" style="background: var(--danger);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Usuń konsultację
                    </button>
                </form>
            </div>
        </div>
        
    </div>
    
</div>

<style>
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: white;
    border: 2px solid var(--border);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 600;
    transition: all 0.3s;
}
.btn-back:hover {
    border-color: var(--secondary);
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
.info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.info-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-secondary);
}
.info-value {
    font-size: 14px;
    color: var(--text-primary);
}
.contact-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--secondary);
    text-decoration: none;
}
.contact-link:hover {
    text-decoration: underline;
}
.note-item {
    padding: 16px;
    background: var(--bg-body);
    border-radius: 8px;
    margin-bottom: 12px;
}
.note-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
}
.note-header span {
    color: var(--text-secondary);
    font-size: 12px;
}
.note-text {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-primary);
}
.alert-success {
    padding: 16px 20px;
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #4caf50;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.btn-danger {
    background: #e74c3c;
    color: white;
    border: none;
}
.btn-danger:hover {
    background: #c0392b;
}
.btn-block {
    width: 100%;
    justify-content: center;
}
</style>

<?php include 'includes/admin-footer.php'; ?>
