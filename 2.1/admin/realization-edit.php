<?php
/**
 * REALIZATION-EDIT.PHP - Edycja realizacji (POPRAWIONY!)
  
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Edytuj realizację';
$currentPage = 'realizations';
$admin = getAdminData();

$errors = [];
$realizationId = intval($_GET['id'] ?? 0);

// Pobierz realizację
$stmt = $conn->prepare("SELECT * FROM realizations WHERE id = ?");
$stmt->bind_param("i", $realizationId);
$stmt->execute();
$realization = $stmt->get_result()->fetch_assoc();

if (!$realization) {
    header("Location: realizations.php");
    exit;
}

// ============================================
// AKTUALIZACJA
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'elewacje';
    $description = trim($_POST['description'] ?? '');
    $fullDescription = trim($_POST['full_description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $year = intval($_POST['year'] ?? date('Y'));
    $area = trim($_POST['area'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $clientName = trim($_POST['client_name'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($title)) {
        $errors[] = 'Tytuł jest wymagany';
    }
    
    // Upload nowych zdjęć
    $uploadDir = '../uploads/realizations/';
    
    $imageBefore = $realization['image_before'] ?? '';
    $imageAfter = $realization['image_after'] ?? '';
    $thumbnail = $realization['thumbnail'] ?? '';
    $gallery = json_decode($realization['gallery'] ?? '[]', true) ?: [];
    
    // Nowe zdjęcie PRZED
    if (isset($_FILES['image_before']) && $_FILES['image_before']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image_before']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if ($imageBefore && file_exists('../' . $imageBefore)) {
                @unlink('../' . $imageBefore);
            }
            $filename = 'before_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image_before']['tmp_name'], $uploadDir . $filename)) {
                $imageBefore = 'uploads/realizations/' . $filename;
            }
        }
    }
    
    // Nowe zdjęcie PO
    if (isset($_FILES['image_after']) && $_FILES['image_after']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image_after']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if ($imageAfter && file_exists('../' . $imageAfter)) {
                @unlink('../' . $imageAfter);
            }
            $filename = 'after_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image_after']['tmp_name'], $uploadDir . $filename)) {
                $imageAfter = 'uploads/realizations/' . $filename;
            }
        }
    }
    
    // Nowa miniatura
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if ($thumbnail && file_exists('../' . $thumbnail)) {
                @unlink('../' . $thumbnail);
            }
            $filename = 'thumb_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $filename)) {
                $thumbnail = 'uploads/realizations/' . $filename;
            }
        }
    }
    
    // Nowe zdjęcia do galerii
    if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                        $gallery[] = 'uploads/realizations/' . $filename;
                    }
                }
            }
        }
    }
    
    // Zapisz
    if (empty($errors)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $galleryJson = !empty($gallery) ? json_encode($gallery) : '';
        
        $stmt = $conn->prepare("
            UPDATE realizations SET
                title = ?, 
                slug = ?, 
                category = ?, 
                description = ?, 
                full_description = ?,
                location = ?, 
                year = ?, 
                area = ?, 
                duration = ?, 
                client_name = ?,
                image_before = ?, 
                image_after = ?, 
                thumbnail = ?, 
                gallery = ?,
                status = ?, 
                is_featured = ?
            WHERE id = ?
        ");
        
        if (!$stmt) {
            $errors[] = 'Błąd prepare: ' . $conn->error;
        } else {
            
            // DOKŁADNIE JAK W DEBUG! ✅
            $stmt->bind_param(
                "ssssssissssssssii",  // ← TO DZIAŁA! (z debug-edit.php)
                $title,               // 1  s
                $slug,                // 2  s
                $category,            // 3  s
                $description,         // 4  s
                $fullDescription,     // 5  s
                $location,            // 6  s
                $year,                // 7  i ← INT
                $area,                // 8  s
                $duration,            // 9  s
                $clientName,          // 10 s
                $imageBefore,         // 11 s
                $imageAfter,          // 12 s
                $thumbnail,           // 13 s
                $galleryJson,         // 14 s
                $status,              // 15 s
                $isFeatured,          // 16 i ← INT
                $realizationId        // 17 i ← INT
            );
            
            if ($stmt->execute()) {
                if (function_exists('logActivity')) {
                    logActivity($admin['id'], 'realization_edit', 'realization', $realizationId, "Edytowano realizację");
                }
                header("Location: realizations.php?success=edit");
                exit;
            } else {
                $errors[] = 'Błąd execute: ' . $stmt->error;
            }
        }
    }
}

include 'includes/admin-header.php';
?>

<div class="content-header">
    <h1><?php echo $pageTitle; ?></h1>
    <p>ID: <?php echo $realizationId; ?></p>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 24px;">
    <strong>Błędy:</strong>
    <?php foreach ($errors as $error): ?>
        <div>• <?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    
    <!-- PODSTAWOWE INFO -->
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Podstawowe informacje</h2>
        </div>
        <div class="card-body">
            
            <div class="form-group">
                <label>Tytuł realizacji *</label>
                <input type="text" name="title" class="form-control" required
                       value="<?php echo htmlspecialchars($realization['title']); ?>">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Kategoria *</label>
                    <select name="category" class="form-control" required>
                        <option value="elewacje" <?php echo $realization['category'] === 'elewacje' ? 'selected' : ''; ?>>Elewacje</option>
                        <option value="wnetrza" <?php echo $realization['category'] === 'wnetrza' ? 'selected' : ''; ?>>Wnętrza</option>
                        <option value="remonty" <?php echo $realization['category'] === 'remonty' ? 'selected' : ''; ?>>Remonty</option>
                        <option value="instytucje" <?php echo $realization['category'] === 'instytucje' ? 'selected' : ''; ?>>Instytucje</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?php echo $realization['status'] === 'draft' ? 'selected' : ''; ?>>Wersja robocza</option>
                        <option value="published" <?php echo $realization['status'] === 'published' ? 'selected' : ''; ?>>Opublikowane</option>
                        <option value="archived" <?php echo $realization['status'] === 'archived' ? 'selected' : ''; ?>>Archiwum</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Krótki opis *</label>
                <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($realization['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Pełny opis</label>
                <textarea name="full_description" class="form-control" rows="6"><?php echo htmlspecialchars($realization['full_description'] ?? ''); ?></textarea>
            </div>
            
        </div>
    </div>
    
    <!-- SZCZEGÓŁY -->
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Szczegóły projektu</h2>
        </div>
        <div class="card-body">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Lokalizacja</label>
                    <input type="text" name="location" class="form-control"
                           value="<?php echo htmlspecialchars($realization['location'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Rok realizacji</label>
                    <input type="number" name="year" class="form-control"
                           value="<?php echo $realization['year'] ?: date('Y'); ?>" min="2000" max="2099">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Powierzchnia</label>
                    <input type="text" name="area" class="form-control"
                           value="<?php echo htmlspecialchars($realization['area'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Czas realizacji</label>
                    <input type="text" name="duration" class="form-control"
                           value="<?php echo htmlspecialchars($realization['duration'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Nazwa klienta</label>
                <input type="text" name="client_name" class="form-control"
                       value="<?php echo htmlspecialchars($realization['client_name'] ?? ''); ?>">
            </div>
            
        </div>
    </div>
    
    <!-- ZDJĘCIA -->
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>📸 Zdjęcia</h2>
        </div>
        <div class="card-body">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- PRZED -->
                <div>
                    <label>Zdjęcie PRZED 🏚️</label>
                    <?php if (!empty($realization['image_before'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo htmlspecialchars($realization['image_before']); ?>" 
                             style="max-width: 100%; height: auto; border-radius: 8px; border: 2px solid #e0e6ed;">
                        <p style="font-size: 12px; color: #7f8c8d; margin-top: 5px;">Obecne zdjęcie</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image_before" class="form-control" accept="image/*">
                    <small>Wybierz nowe zdjęcie aby zastąpić</small>
                </div>
                
                <!-- PO -->
                <div>
                    <label>Zdjęcie PO ✨</label>
                    <?php if (!empty($realization['image_after'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo htmlspecialchars($realization['image_after']); ?>" 
                             style="max-width: 100%; height: auto; border-radius: 8px; border: 2px solid #e0e6ed;">
                        <p style="font-size: 12px; color: #7f8c8d; margin-top: 5px;">Obecne zdjęcie</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image_after" class="form-control" accept="image/*">
                    <small>Wybierz nowe zdjęcie aby zastąpić</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Miniatura</label>
                <?php if (!empty($realization['thumbnail'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo htmlspecialchars($realization['thumbnail']); ?>" 
                         style="max-width: 200px; border-radius: 8px; border: 2px solid #e0e6ed;">
                </div>
                <?php endif; ?>
                <input type="file" name="thumbnail" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Dodaj zdjęcia do galerii</label>
                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                <small>Nowe zdjęcia zostaną dodane do istniejącej galerii</small>
                
                <?php 
                $existingGallery = json_decode($realization['gallery'] ?? '[]', true);
                if (!empty($existingGallery) && is_array($existingGallery)): 
                ?>
                <div style="margin-top: 10px;">
                    <strong>Obecna galeria (<?php echo count($existingGallery); ?> zdjęć):</strong>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                        <?php foreach ($existingGallery as $img): ?>
                        <img src="../<?php echo htmlspecialchars($img); ?>" 
                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; border: 2px solid #e0e6ed;">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
    
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-body">
            <div class="form-group">
                <label class="form-group-checkbox">
                    <input type="checkbox" name="is_featured" <?php echo $realization['is_featured'] ? 'checked' : ''; ?>>
                    <span>⭐ Wyróżniona realizacja</span>
                </label>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px;">
        <button type="submit" class="btn btn-primary btn-lg">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            </svg>
            Zapisz zmiany
        </button>
        <a href="realizations.php" class="btn btn-secondary btn-lg">Anuluj</a>
    </div>
    
</form>

<?php include 'includes/admin-footer.php'; ?>