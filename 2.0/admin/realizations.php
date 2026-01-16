<?php
/**
 * REALIZATIONS.PHP - Panel admina - lista realizacji
  
 * Z UPLOADEM ZDJĘĆ!
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Realizacje';
$currentPage = 'realizations';
$admin = getAdminData();

$success = '';
$errors = [];

// ============================================
// USUWANIE
// ============================================

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Pobierz zdjęcia przed usunięciem
    $stmt = $conn->prepare("SELECT image_before, image_after, thumbnail, gallery FROM realizations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $real = $stmt->get_result()->fetch_assoc();
    
    if ($real) {
        // Usuń pliki
        $files = [$real['image_before'], $real['image_after'], $real['thumbnail']];
        
        if ($real['gallery']) {
            $gallery = json_decode($real['gallery'], true);
            if (is_array($gallery)) {
                $files = array_merge($files, $gallery);
            }
        }
        
        foreach ($files as $file) {
            if ($file && file_exists('../' . $file)) {
                unlink('../' . $file);
            }
        }
        
        // Usuń z bazy
        $stmt = $conn->prepare("DELETE FROM realizations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        logActivity($admin['id'], 'realization_delete', 'realization', $id, "Usunięto realizację");
        header("Location: realizations.php?success=delete");
        exit;
    }
}

// ============================================
// TOGGLE STATUS
// ============================================

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE realizations SET status = IF(status='published', 'draft', 'published') WHERE id = {$id}");
    header("Location: realizations.php?success=toggle");
    exit;
}

// ============================================
// FILTRY
// ============================================

$filterCategory = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$sql = "SELECT * FROM realizations WHERE 1=1";
$params = [];
$types = '';

if ($filterCategory) {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
    $types .= 's';
}

if ($filterStatus) {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
    $types .= 's';
}

$sql .= " ORDER BY is_featured DESC, created_at DESC";

// Wykonaj
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $realizations = $stmt->get_result();
} else {
    $realizations = $conn->query($sql);
}

// Statystyki
$totalRealizations = $conn->query("SELECT COUNT(*) as count FROM realizations")->fetch_assoc()['count'];
$publishedRealizations = $conn->query("SELECT COUNT(*) as count FROM realizations WHERE status = 'published'")->fetch_assoc()['count'];
$draftRealizations = $totalRealizations - $publishedRealizations;

// Success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'add': $success = 'Realizacja dodana'; break;
        case 'edit': $success = 'Realizacja zaktualizowana'; break;
        case 'delete': $success = 'Realizacja usunięta'; break;
        case 'toggle': $success = 'Status zmieniony'; break;
    }
}

include 'includes/admin-header.php';
?>

<div class="content-header">
    <h1><?php echo $pageTitle; ?></h1>
    <p>Zarządzaj portfolio realizacji na stronie</p>
</div>

<!-- STATYSTYKI -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $totalRealizations; ?></div>
                <div class="stat-card-label">Wszystkie</div>
            </div>
            <div class="stat-card-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $publishedRealizations; ?></div>
                <div class="stat-card-label">Opublikowane</div>
            </div>
            <div class="stat-card-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?php echo $draftRealizations; ?></div>
                <div class="stat-card-label">Wersje robocze</div>
            </div>
            <div class="stat-card-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- SUCCESS -->
<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom: 24px;">
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<!-- FILTRY + DODAJ -->
<div class="content-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
            
            <div class="form-group" style="margin: 0; flex: 1; min-width: 180px;">
                <label>Kategoria</label>
                <select name="category" class="form-control">
                    <option value="">Wszystkie</option>
                    <option value="elewacje" <?php echo $filterCategory === 'elewacje' ? 'selected' : ''; ?>>Elewacje</option>
                    <option value="wnetrza" <?php echo $filterCategory === 'wnetrza' ? 'selected' : ''; ?>>Wnętrza</option>
                    <option value="remonty" <?php echo $filterCategory === 'remonty' ? 'selected' : ''; ?>>Remonty</option>
                    <option value="instytucje" <?php echo $filterCategory === 'instytucje' ? 'selected' : ''; ?>>Instytucje</option>
                </select>
            </div>
            
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Wszystkie</option>
                    <option value="published" <?php echo $filterStatus === 'published' ? 'selected' : ''; ?>>Opublikowane</option>
                    <option value="draft" <?php echo $filterStatus === 'draft' ? 'selected' : ''; ?>>Wersje robocze</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary">Filtruj</button>
                <a href="realizations.php" class="btn btn-secondary">Wyczyść</a>
                <a href="realization-add.php" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Dodaj realizację
                </a>
            </div>
        </form>
    </div>
</div>

<!-- LISTA -->
<?php if ($realizations->num_rows === 0): ?>
<div class="content-card">
    <div class="card-body" style="text-align: center; padding: 60px 20px;">
        <p style="color: var(--text-secondary);">Brak realizacji</p>
        <a href="realization-add.php" class="btn btn-primary" style="margin-top: 20px;">Dodaj pierwszą realizację</a>
    </div>
</div>
<?php else: ?>

<div class="content-card">
    <div class="card-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Zdjęcie</th>
                    <th style="width: 30%;">Tytuł</th>
                    <th style="width: 15%;">Kategoria</th>
                    <th style="width: 15%;">Lokalizacja</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                    <th style="width: 15%; text-align: right;">Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($real = $realizations->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($real['thumbnail'] || $real['image_after'] || $real['image_before']): ?>
                        <img src="<?php echo htmlspecialchars($real['thumbnail'] ?: $real['image_after'] ?: $real['image_before']); ?>" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"
                             alt="">
                        <?php else: ?>
                        <div style="width: 60px; height: 60px; background: #ecf0f1; border-radius: 4px;"></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($real['title']); ?></strong>
                        <?php if ($real['is_featured']): ?>
                        <span class="badge badge-warning" style="margin-left: 8px;">⭐ Wyróżnione</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $cats = [
                            'elewacje' => 'Elewacje',
                            'wnetrza' => 'Wnętrza',
                            'remonty' => 'Remonty',
                            'instytucje' => 'Instytucje'
                        ];
                        echo $cats[$real['category']] ?? $real['category'];
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($real['location'] ?? '—'); ?></td>
                    <td style="text-align: center;">
                        <span class="badge <?php echo $real['status'] === 'published' ? 'badge-success' : 'badge-secondary'; ?>">
                            <?php echo $real['status'] === 'published' ? 'Opublikowane' : 'Wersja robocza'; ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div class="btn-group">
                            <a href="realization-edit.php?id=<?php echo $real['id']; ?>" class="btn btn-sm btn-primary" title="Edytuj">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <a href="?toggle=<?php echo $real['id']; ?>" class="btn btn-sm btn-secondary" title="Zmień status">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <a href="?delete=<?php echo $real['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Czy na pewno usunąć tę realizację?')" title="Usuń">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>