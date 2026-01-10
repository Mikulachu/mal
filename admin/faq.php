<?php
/**
 * FAQ.PHP - Zarządzanie FAQ (pytania i odpowiedzi)
  
 * 
 * LOKALIZACJA: /admin/faq.php
 * ZAKTUALIZOWANE: Kategorie z polskimi znakami + filtrowanie
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'FAQ';
$currentPage = 'faq';
$admin = getAdminData();

$success = '';
$errors = [];

// ============================================
// KATEGORIE (z polskimi znakami!)
// ============================================

$categories = [
    'ogólne' => 'Ogólne pytania',
    'wycena' => 'Wycena i koszty',
    'realizacja' => 'Realizacja projektu',
    'gwarancja' => 'Gwarancja i serwis',
    'materiały' => 'Materiały i techniki',
    'konsultacje' => 'Konsultacje i doradztwo'
];

// ============================================
// AKCJE
// ============================================

// Dodawanie nowego pytania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $category = trim($_POST['category'] ?? 'ogólne');
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;
    
    if (empty($question) || empty($answer)) {
        $errors[] = 'Pytanie i odpowiedź są wymagane';
    } else {
        // Pobierz maksymalny order_index dla kategorii
        $stmt = $conn->prepare("SELECT MAX(order_index) as max FROM faq WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $maxOrder = $stmt->get_result()->fetch_assoc()['max'] ?? 0;
        
        $stmt = $conn->prepare("INSERT INTO faq (question, answer, category, is_visible, order_index) VALUES (?, ?, ?, ?, ?)");
        $newOrder = $maxOrder + 1;
        $stmt->bind_param("sssii", $question, $answer, $category, $isVisible, $newOrder);
        
        if ($stmt->execute()) {
            logActivity($admin['id'], 'faq_add', 'faq', $stmt->insert_id, "Dodano pytanie FAQ: {$question}");
            header("Location: faq.php?success=add");
            exit;
        } else {
            $errors[] = 'Błąd podczas dodawania';
        }
    }
}

// Edycja pytania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $faqId = intval($_POST['faq_id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $category = trim($_POST['category'] ?? 'ogólne');
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;
    
    if (empty($question) || empty($answer)) {
        $errors[] = 'Pytanie i odpowiedź są wymagane';
    } else {
        $stmt = $conn->prepare("UPDATE faq SET question = ?, answer = ?, category = ?, is_visible = ? WHERE id = ?");
        $stmt->bind_param("sssii", $question, $answer, $category, $isVisible, $faqId);
        
        if ($stmt->execute()) {
            logActivity($admin['id'], 'faq_edit', 'faq', $faqId, "Edytowano pytanie FAQ");
            header("Location: faq.php?success=edit");
            exit;
        }
    }
}

// Usuwanie
if (isset($_GET['delete'])) {
    $faqId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM faq WHERE id = ?");
    $stmt->bind_param("i", $faqId);
    
    if ($stmt->execute()) {
        logActivity($admin['id'], 'faq_delete', 'faq', $faqId, "Usunięto pytanie FAQ");
        header("Location: faq.php?success=delete");
        exit;
    }
}

// Zmiana widoczności
if (isset($_GET['toggle'])) {
    $faqId = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE faq SET is_visible = NOT is_visible WHERE id = ?");
    $stmt->bind_param("i", $faqId);
    $stmt->execute();
    header("Location: faq.php?success=toggle");
    exit;
}

// Zmiana kolejności (up/down)
if (isset($_GET['move']) && isset($_GET['id'])) {
    $faqId = intval($_GET['id']);
    $direction = $_GET['move'];
    
    $stmt = $conn->prepare("SELECT order_index, category FROM faq WHERE id = ?");
    $stmt->bind_param("i", $faqId);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $currentOrder = $current['order_index'];
    $currentCategory = $current['category'];
    
    if ($direction === 'up') {
        // Zamień z poprzednim w tej samej kategorii
        $stmt = $conn->prepare("
            SELECT id, order_index FROM faq 
            WHERE category = ? AND order_index < ? 
            ORDER BY order_index DESC LIMIT 1
        ");
        $stmt->bind_param("si", $currentCategory, $currentOrder);
        $stmt->execute();
        $swap = $stmt->get_result()->fetch_assoc();
        
        if ($swap) {
            $conn->query("UPDATE faq SET order_index = {$swap['order_index']} WHERE id = {$faqId}");
            $conn->query("UPDATE faq SET order_index = {$currentOrder} WHERE id = {$swap['id']}");
        }
    } else {
        // Zamień z następnym w tej samej kategorii
        $stmt = $conn->prepare("
            SELECT id, order_index FROM faq 
            WHERE category = ? AND order_index > ? 
            ORDER BY order_index ASC LIMIT 1
        ");
        $stmt->bind_param("si", $currentCategory, $currentOrder);
        $stmt->execute();
        $swap = $stmt->get_result()->fetch_assoc();
        
        if ($swap) {
            $conn->query("UPDATE faq SET order_index = {$swap['order_index']} WHERE id = {$faqId}");
            $conn->query("UPDATE faq SET order_index = {$currentOrder} WHERE id = {$swap['id']}");
        }
    }
    
    header("Location: faq.php");
    exit;
}

// ============================================
// FILTROWANIE
// ============================================

$filterCategory = $_GET['category'] ?? '';
$filterVisible = $_GET['visible'] ?? '';
$search = trim($_GET['search'] ?? '');

// Buduj zapytanie SQL
$sql = "SELECT * FROM faq WHERE 1=1";
$params = [];
$types = '';

if ($filterCategory && $filterCategory !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
    $types .= 's';
}

if ($filterVisible !== '') {
    $sql .= " AND is_visible = ?";
    $params[] = intval($filterVisible);
    $types .= 'i';
}

if ($search) {
    $sql .= " AND (question LIKE ? OR answer LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

$sql .= " ORDER BY category, order_index ASC, id DESC";

// Wykonaj zapytanie
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $faqs = $stmt->get_result();
} else {
    $faqs = $conn->query($sql);
}

// Pogrupuj po kategoriach
$faqsByCategory = [];
while ($faq = $faqs->fetch_assoc()) {
    $faqsByCategory[$faq['category']][] = $faq;
}

// Statystyki
$totalFaqs = $conn->query("SELECT COUNT(*) as count FROM faq")->fetch_assoc()['count'];
$visibleFaqs = $conn->query("SELECT COUNT(*) as count FROM faq WHERE is_visible = 1")->fetch_assoc()['count'];
$hiddenFaqs = $totalFaqs - $visibleFaqs;

// Success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'add': $success = 'Pytanie zostało dodane'; break;
        case 'edit': $success = 'Pytanie zostało zaktualizowane'; break;
        case 'delete': $success = 'Pytanie zostało usunięte'; break;
        case 'toggle': $success = 'Widoczność zmieniona'; break;
    }
}

// Header
include 'includes/admin-header.php';
?>

<div class="content-header">
    <h1><?php echo $pageTitle; ?></h1>
    <p>Zarządzaj pytaniami i odpowiedziami na stronie FAQ</p>
</div>

<!-- STATYSTYKI -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $totalFaqs; ?></div>
                <div class="stat-card-label">Wszystkie pytania</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $visibleFaqs; ?></div>
                <div class="stat-card-label">Widoczne</div>
            </div>
            <div class="stat-card-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $hiddenFaqs; ?></div>
                <div class="stat-card-label">Ukryte</div>
            </div>
            <div class="stat-card-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- SUCCESS/ERROR -->
<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom: 24px;">
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 24px;">
    <?php foreach ($errors as $error): ?>
        <div><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- FILTRY + DODAJ NOWE -->
<div class="content-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
            
            <!-- Kategoria -->
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label>Kategoria</label>
                <select name="category" class="form-control">
                    <option value="">Wszystkie kategorie</option>
                    <?php foreach ($categories as $catKey => $catName): ?>
                    <option value="<?php echo htmlspecialchars($catKey); ?>" 
                            <?php echo $filterCategory === $catKey ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($catName); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Widoczność -->
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label>Widoczność</label>
                <select name="visible" class="form-control">
                    <option value="">Wszystkie</option>
                    <option value="1" <?php echo $filterVisible === '1' ? 'selected' : ''; ?>>Widoczne</option>
                    <option value="0" <?php echo $filterVisible === '0' ? 'selected' : ''; ?>>Ukryte</option>
                </select>
            </div>
            
            <!-- Szukaj -->
            <div class="form-group" style="margin: 0; flex: 2; min-width: 250px;">
                <label>Szukaj</label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Szukaj w pytaniach i odpowiedziach..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <!-- Przyciski -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    Filtruj
                </button>
                <a href="faq.php" class="btn btn-secondary">Wyczyść</a>
                <button type="button" class="btn btn-success" onclick="document.getElementById('addModal').style.display='block'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Dodaj pytanie
                </button>
            </div>
            
        </form>
    </div>
</div>

<!-- LISTA FAQ -->
<?php if (empty($faqsByCategory)): ?>
<div class="content-card">
    <div class="card-body" style="text-align: center; padding: 60px 20px;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 20px; opacity: 0.3;">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <p style="color: var(--text-secondary); margin-bottom: 20px;">
            <?php if ($search || $filterCategory || $filterVisible !== ''): ?>
                Nie znaleziono pytań pasujących do filtrów
            <?php else: ?>
                Nie masz jeszcze żadnych pytań FAQ
            <?php endif; ?>
        </p>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            Dodaj pierwsze pytanie
        </button>
    </div>
</div>
<?php else: ?>

<?php foreach ($categories as $catKey => $catName): ?>
    <?php if (isset($faqsByCategory[$catKey]) && !empty($faqsByCategory[$catKey])): ?>
    
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2><?php echo htmlspecialchars($catName); ?></h2>
            <span class="badge badge-secondary"><?php echo count($faqsByCategory[$catKey]); ?> pytań</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Pytanie</th>
                        <th style="width: 40%;">Odpowiedź</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                        <th style="width: 10%; text-align: right;">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faqsByCategory[$catKey] as $faq): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($faq['question']); ?></strong>
                        </td>
                        <td>
                            <div style="max-height: 60px; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo nl2br(htmlspecialchars(mb_substr($faq['answer'], 0, 150))); ?>
                                <?php if (strlen($faq['answer']) > 150): ?>...<?php endif; ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge <?php echo $faq['is_visible'] ? 'badge-success' : 'badge-secondary'; ?>">
                                <?php echo $faq['is_visible'] ? 'Widoczne' : 'Ukryte'; ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div class="btn-group">
                                <!-- Edytuj -->
                                <button class="btn btn-sm btn-primary" 
                                        onclick="editFaq(<?php echo htmlspecialchars(json_encode($faq)); ?>)"
                                        title="Edytuj">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                
                                <!-- Toggle widoczność -->
                                <a href="?toggle=<?php echo $faq['id']; ?>" 
                                   class="btn btn-sm btn-secondary"
                                   title="Zmień widoczność"
                                   onclick="return confirm('Zmienić widoczność?')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                
                                <!-- Przesuń w górę -->
                                <a href="?move=up&id=<?php echo $faq['id']; ?>" 
                                   class="btn btn-sm btn-secondary"
                                   title="Przesuń w górę">
                                    ↑
                                </a>
                                
                                <!-- Przesuń w dół -->
                                <a href="?move=down&id=<?php echo $faq['id']; ?>" 
                                   class="btn btn-sm btn-secondary"
                                   title="Przesuń w dół">
                                    ↓
                                </a>
                                
                                <!-- Usuń -->
                                <a href="?delete=<?php echo $faq['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   title="Usuń"
                                   onclick="return confirm('Czy na pewno usunąć to pytanie?')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php endif; ?>
<?php endforeach; ?>

<?php endif; ?>

<!-- MODAL: DODAJ PYTANIE -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2>Dodaj nowe pytanie FAQ</h2>
            <button class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Kategoria *</label>
                    <select name="category" class="form-control" required>
                        <?php foreach ($categories as $catKey => $catName): ?>
                        <option value="<?php echo htmlspecialchars($catKey); ?>">
                            <?php echo htmlspecialchars($catName); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pytanie *</label>
                    <input type="text" name="question" class="form-control" required 
                           placeholder="np. Ile kosztuje wycena?">
                </div>
                
                <div class="form-group">
                    <label>Odpowiedź *</label>
                    <textarea name="answer" class="form-control" rows="6" required 
                              placeholder="Wpisz szczegółową odpowiedź..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-group-checkbox">
                        <input type="checkbox" name="is_visible" checked>
                        <span>Widoczne na stronie</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').style.display='none'">
                    Anuluj
                </button>
                <button type="submit" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Dodaj pytanie
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDYTUJ PYTANIE -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2>Edytuj pytanie FAQ</h2>
            <button class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="faq_id" id="edit_faq_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Kategoria *</label>
                    <select name="category" id="edit_category" class="form-control" required>
                        <?php foreach ($categories as $catKey => $catName): ?>
                        <option value="<?php echo htmlspecialchars($catKey); ?>">
                            <?php echo htmlspecialchars($catName); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pytanie *</label>
                    <input type="text" name="question" id="edit_question" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Odpowiedź *</label>
                    <textarea name="answer" id="edit_answer" class="form-control" rows="6" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-group-checkbox">
                        <input type="checkbox" name="is_visible" id="edit_is_visible">
                        <span>Widoczne na stronie</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">
                    Anuluj
                </button>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background: white;
    margin: 50px auto;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    animation: slideDown 0.3s;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid #e0e6ed;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #7f8c8d;
    cursor: pointer;
    line-height: 1;
}

.modal-close:hover {
    color: #2c3e50;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e0e6ed;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-group {
    display: flex;
    gap: 4px;
}
</style>

<script>
// Funkcja edycji
function editFaq(faq) {
    document.getElementById('edit_faq_id').value = faq.id;
    document.getElementById('edit_category').value = faq.category;
    document.getElementById('edit_question').value = faq.question;
    document.getElementById('edit_answer').value = faq.answer;
    document.getElementById('edit_is_visible').checked = faq.is_visible == 1;
    
    document.getElementById('editModal').style.display = 'block';
}

// Zamknij modal przy kliknięciu poza
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// ESC zamyka modaly
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>