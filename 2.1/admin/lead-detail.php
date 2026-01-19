<?php
/**
 * LEAD-DETAIL.PHP - Szczegóły zapytania + zarządzanie (PDO)
  
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$leadId = $_GET['id'] ?? 0;
$admin = getAdminData();

// POBIERZ LEADA
$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$leadId]);
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lead) {
    header('Location: leads.php');
    exit;
}

$pageTitle = 'Zapytanie #' . $leadId;
$currentPage = 'leads';

// POBIERZ NOTATKI (jeśli tabela istnieje)
try {
    $notes = $pdo->query("
        SELECT 
            ln.*,
            au.full_name as admin_name
        FROM lead_notes ln
        LEFT JOIN admin_users au ON ln.admin_id = au.id
        WHERE ln.lead_id = {$leadId}
        ORDER BY ln.created_at DESC
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
        
        $stmt = $pdo->prepare("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $leadId]);
        
        header("Location: lead-detail.php?id={$leadId}&success=status");
        exit;
    }
    
    // Dodanie notatki
    if ($_POST['action'] === 'add_note') {
        $noteText = trim($_POST['note_text'] ?? '');
        
        if ($noteText) {
            // Sprawdź czy tabela lead_notes istnieje
            try {
                $stmt = $pdo->prepare("INSERT INTO lead_notes (lead_id, admin_id, note_text, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$leadId, $admin['id'], $noteText]);
            } catch (PDOException $e) {
                // Jeśli tabela nie istnieje, zapisz w polu notes
                $stmt = $pdo->prepare("UPDATE leads SET notes = CONCAT(COALESCE(notes, ''), ?, '\n') WHERE id = ?");
                $noteWithDate = '[' . date('Y-m-d H:i') . '] ' . $admin['full_name'] . ': ' . $noteText;
                $stmt->execute([$noteWithDate, $leadId]);
            }
            
            header("Location: lead-detail.php?id={$leadId}&success=note");
            exit;
        }
    }
    
    // Zmiana priorytetu
    if ($_POST['action'] === 'change_priority') {
        $newPriority = $_POST['priority'] ?? 'medium';
        
        $stmt = $pdo->prepare("UPDATE leads SET priority = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newPriority, $leadId]);
        
        header("Location: lead-detail.php?id={$leadId}&success=priority");
        exit;
    }
    
    // Usunięcie
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
        $stmt->execute([$leadId]);
        
        header("Location: leads.php?success=delete");
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
        'note' => '✓ Notatka została dodana',
        'priority' => '✓ Priorytet został zmieniony'
    ];
    echo $messages[$_GET['success']] ?? '✓ Zapisano';
    ?>
</div>
<?php endif; ?>

<div style="display: flex; gap: 24px; align-items: center; margin-bottom: 24px;">
    <a href="leads.php" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5"/>
            <path d="M12 19l-7-7 7-7"/>
        </svg>
        Powrót
    </a>
    <div>
        <h1 style="margin-bottom: 4px;">Zapytanie #<?php echo $leadId; ?></h1>
        <p style="color: var(--text-secondary); font-size: 14px;">
            Otrzymane: <?php echo date('d.m.Y H:i', strtotime($lead['created_at'])); ?>
            <?php 
            $sources = [
                'website' => 'z formularza kontaktowego',
                'calculator' => 'z kalkulatora cennikowego'
            ];
            $sourceText = $sources[$lead['source'] ?? 'website'] ?? 'ze strony';
            ?>
            • <?php echo $sourceText; ?>
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
                        'contacted' => 'badge-info',
                        'quoted' => 'badge-warning',
                        'won' => 'badge-success',
                        'lost' => 'badge-danger'
                    ];
                    $statusLabel = [
                        'new' => 'Nowy',
                        'contacted' => 'Kontakt',
                        'quoted' => 'Wycena',
                        'won' => 'Wygrana',
                        'lost' => 'Przegrana'
                    ];
                    ?>
                    <span class="badge <?php echo $statusClass[$lead['status']] ?? ''; ?>">
                        <?php echo $statusLabel[$lead['status']] ?? $lead['status']; ?>
                    </span>
                </div>
            </div>
            
            <div style="padding: 24px;">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Imię i nazwisko</div>
                        <div class="info-value">
                            <strong><?php echo htmlspecialchars($lead['name']); ?></strong>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Telefon</div>
                        <div class="info-value">
                            <?php if ($lead['phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($lead['phone']); ?>" class="contact-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <?php echo htmlspecialchars($lead['phone']); ?>
                            </a>
                            <?php else: ?>
                            <span style="color: var(--text-secondary);">Brak</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <?php if ($lead['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>" class="contact-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <?php echo htmlspecialchars($lead['email']); ?>
                            </a>
                            <?php else: ?>
                            <span style="color: var(--text-secondary);">Brak</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Źródło</div>
                        <div class="info-value">
                            <?php 
                            $sources = [
                                'website' => '📝 Formularz kontaktowy',
                                'calculator' => '🧮 Kalkulator cennikowy'
                            ];
                            echo $sources[$lead['source'] ?? 'website'] ?? 'Strona internetowa';
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Zgoda marketingowa</div>
                        <div class="info-value">
                            <?php if (isset($lead['marketing_consent']) && $lead['marketing_consent']): ?>
                                ✅ Tak
                            <?php else: ?>
                                ❌ Nie
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php
                // Parsuj additional_data (JSON)
                $additionalData = [];
                if (!empty($lead['additional_data'])) {
                    $additionalData = json_decode($lead['additional_data'], true) ?? [];
                }
                ?>

                <?php if (!empty($additionalData['temat_pelny'])): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <div class="info-label" style="margin-bottom: 12px;">Temat wiadomości</div>
                    <div style="background: #e3f2fd; padding: 16px; border-radius: 8px; border-left: 4px solid #2196F3;">
                        <strong><?php echo htmlspecialchars($additionalData['temat_pelny']); ?></strong>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($additionalData['lokalizacja'])): ?>
                <div style="margin-top: 16px;">
                    <div class="info-label" style="margin-bottom: 8px;">📍 Lokalizacja</div>
                    <div style="padding: 12px; background: var(--bg-body); border-radius: 8px;">
                        <?php echo htmlspecialchars($additionalData['lokalizacja']); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($additionalData['termin'])): ?>
                <div style="margin-top: 16px;">
                    <div class="info-label" style="margin-bottom: 8px;">📅 Termin / kiedy chce startować</div>
                    <div style="padding: 12px; background: var(--bg-body); border-radius: 8px;">
                        <?php echo htmlspecialchars($additionalData['termin']); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($lead['message']): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <div class="info-label" style="margin-bottom: 12px;">Opis / Pytanie</div>
                    <div style="background: var(--bg-body); padding: 16px; border-radius: 8px; line-height: 1.6; white-space: pre-wrap;">
                        <?php echo htmlspecialchars($lead['message']); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($additionalData['attachments']) && is_array($additionalData['attachments'])): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <div class="info-label" style="margin-bottom: 12px;">📎 Załączniki (<?php echo count($additionalData['attachments']); ?>)</div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                        <?php foreach ($additionalData['attachments'] as $file): ?>
                        <a href="/2.1/<?php echo htmlspecialchars($file['path']); ?>"
                           target="_blank"
                           style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--bg-body); border-radius: 8px; text-decoration: none; color: var(--text-primary); border: 1px solid var(--border); transition: all 0.2s;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                                <polyline points="13 2 13 9 20 9"/>
                            </svg>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($file['original_name']); ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    <?php echo round($file['size'] / 1024, 1); ?> KB
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($lead['notes']) && $lead['notes']): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <div class="info-label" style="margin-bottom: 12px;">Historia notatek</div>
                    <div style="background: #fff3e0; padding: 16px; border-radius: 8px; line-height: 1.6; white-space: pre-wrap; border: 1px solid #f57c00;">
                        <?php echo nl2br(htmlspecialchars($lead['notes'])); ?>
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
                        <option value="new" <?php echo $lead['status'] === 'new' ? 'selected' : ''; ?>>🆕 Nowy</option>
                        <option value="contacted" <?php echo $lead['status'] === 'contacted' ? 'selected' : ''; ?>>📞 Kontakt</option>
                        <option value="quoted" <?php echo $lead['status'] === 'quoted' ? 'selected' : ''; ?>>💰 Wycena</option>
                        <option value="won" <?php echo $lead['status'] === 'won' ? 'selected' : ''; ?>>✅ Wygrana</option>
                        <option value="lost" <?php echo $lead['status'] === 'lost' ? 'selected' : ''; ?>>❌ Przegrana</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-block">Zapisz</button>
                </form>
            </div>
        </div>
        
        <!-- PRIORYTET -->
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2>Priorytet</h2>
            </div>
            <div style="padding: 20px;">
                <form method="POST">
                    <input type="hidden" name="action" value="change_priority">
                    <select name="priority" class="form-control" style="margin-bottom: 12px;">
                        <option value="low" <?php echo ($lead['priority'] ?? 'medium') === 'low' ? 'selected' : ''; ?>>Niski</option>
                        <option value="medium" <?php echo ($lead['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Średni</option>
                        <option value="high" <?php echo ($lead['priority'] ?? 'medium') === 'high' ? 'selected' : ''; ?>>Wysoki</option>
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
                <?php if ($lead['phone']): ?>
                <a href="tel:<?php echo htmlspecialchars($lead['phone']); ?>" class="btn btn-secondary btn-block">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    Zadzwoń
                </a>
                <?php endif; ?>
                
                <?php if ($lead['email']): ?>
                <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>?subject=Odpowiedź na pytanie&body=Dzień dobry <?php echo htmlspecialchars(explode(' ', $lead['name'])[0]); ?>,%0D%0A%0D%0ADziękujemy za pytanie.%0D%0A%0D%0APozdrawiamy," class="btn btn-secondary btn-block">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Wyślij email
                </a>
                <?php endif; ?>
                
                <form method="POST" onsubmit="return confirm('Czy na pewno usunąć to zapytanie?');" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger btn-block" style="background: var(--danger);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Usuń zapytanie
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