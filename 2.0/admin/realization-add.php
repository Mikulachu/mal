<?php
/**
 * REALIZATION-ADD.PHP - Dodawanie realizacji (FINAL)
  
 */

// DEBUG (usuń w produkcji)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Dodaj realizację';
$currentPage = 'realizations';
$admin = getAdminData();

$errors = [];

// ============================================
// DODAWANIE
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
    
    // Walidacja
    if (empty($title)) {
        $errors[] = 'Tytuł jest wymagany';
    }
    
    if (empty($description)) {
        $errors[] = 'Krótki opis jest wymagany';
    }
    
    // Upload zdjęć
    $uploadDir = '../uploads/realizations/';
    
    // Upewnij się że katalog istnieje
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $errors[] = 'Nie można utworzyć katalogu uploads/realizations/';
        }
    }
    
    $imageBefore = '';
    $imageAfter = '';
    $thumbnail = '';
    $gallery = [];
    
    // Tylko jeśli nie ma błędów
    if (empty($errors)) {
        
        // Zdjęcie PRZED
        if (isset($_FILES['image_before']) && $_FILES['image_before']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image_before'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($ext, $allowed)) {
                $errors[] = 'Zdjęcie PRZED: nieprawidłowy format (dozwolone: jpg, png, webp)';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Zdjęcie PRZED: plik za duży (max 5MB)';
            } else {
                $filename = 'before_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imageBefore = 'uploads/realizations/' . $filename;
                } else {
                    $errors[] = 'Błąd uploadu zdjęcia PRZED';
                }
            }
        }
        
        // Zdjęcie PO
        if (isset($_FILES['image_after']) && $_FILES['image_after']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image_after'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($ext, $allowed)) {
                $errors[] = 'Zdjęcie PO: nieprawidłowy format';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Zdjęcie PO: plik za duży (max 5MB)';
            } else {
                $filename = 'after_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imageAfter = 'uploads/realizations/' . $filename;
                } else {
                    $errors[] = 'Błąd uploadu zdjęcia PO';
                }
            }
        }
        
        // Miniatura
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['thumbnail'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $filename = 'thumb_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $thumbnail = 'uploads/realizations/' . $filename;
                }
            }
        }
        
        // Galeria
        if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = $_FILES['gallery'];
                    $ext = strtolower(pathinfo($file['name'][$key], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (in_array($ext, $allowed) && $file['size'][$key] <= 5 * 1024 * 1024) {
                        $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                            $gallery[] = 'uploads/realizations/' . $filename;
                        }
                    }
                }
            }
        }
    }
    
    // Zapisz do bazy
    if (empty($errors)) {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $galleryJson = !empty($gallery) ? json_encode($gallery) : '';
            
            $stmt = $conn->prepare("
                INSERT INTO realizations (
                    title, slug, category, description, full_description,
                    location, year, area, duration, client_name,
                    image_before, image_after, thumbnail, gallery,
                    status, is_featured
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                $errors[] = 'Błąd prepare: ' . $conn->error;
            } else {
                // POPRAWIONE typy: s=string, i=integer
                $stmt->bind_param(
                    "ssssssississsssi",  // 16 parametrów
                    $title,
                    $slug,
                    $category,
                    $description,
                    $fullDescription,
                    $location,
                    $year,              // int
                    $area,
                    $duration,
                    $clientName,
                    $imageBefore,
                    $imageAfter,
                    $thumbnail,
                    $galleryJson,
                    $status,
                    $isFeatured         // int
                );
                
                if ($stmt->execute()) {
                    $realizationId = $stmt->insert_id;
                    
                    // Funkcja logActivity może nie istnieć - zabezpieczenie
                    if (function_exists('logActivity')) {
                        logActivity($admin['id'], 'realization_add', 'realization', $realizationId, "Dodano realizację: {$title}");
                    }
                    
                    header("Location: realizations.php?success=add");
                    exit;
                } else {
                    $errors[] = 'Błąd execute: ' . $stmt->error;
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Wyjątek: ' . $e->getMessage();
        }
    }
}

include 'includes/admin-header.php';
?>

<div class="content-header">
    <h1><?php echo $pageTitle; ?></h1>
    <p>Dodaj nową realizację do portfolio</p>
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
    
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Podstawowe informacje</h2>
        </div>
        <div class="card-body">
            
            <div class="form-group">
                <label>Tytuł realizacji *</label>
                <input type="text" name="title" class="form-control" required
                       placeholder="np. Dom jednorodzinny w Chojnicach"
                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Kategoria *</label>
                    <select name="category" class="form-control" required>
                        <option value="elewacje">Elewacje</option>
                        <option value="wnetrza">Wnętrza</option>
                        <option value="remonty">Remonty</option>
                        <option value="instytucje">Instytucje</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Wersja robocza</option>
                        <option value="published">Opublikowane</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Krótki opis *</label>
                <textarea name="description" class="form-control" rows="3" required
                          placeholder="Krótki opis realizacji (max 150 znaków)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <small>Wyświetlany na liście realizacji</small>
            </div>
            
            <div class="form-group">
                <label>Pełny opis</label>
                <textarea name="full_description" class="form-control" rows="6"
                          placeholder="Szczegółowy opis realizacji"><?php echo htmlspecialchars($_POST['full_description'] ?? ''); ?></textarea>
            </div>
            
        </div>
    </div>
    
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Szczegóły projektu</h2>
        </div>
        <div class="card-body">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Lokalizacja</label>
                    <input type="text" name="location" class="form-control"
                           placeholder="np. Chojnice"
                           value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Rok realizacji</label>
                    <input type="number" name="year" class="form-control"
                           value="<?php echo $_POST['year'] ?? date('Y'); ?>" min="2000" max="2099">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Powierzchnia</label>
                    <input type="text" name="area" class="form-control"
                           placeholder="np. 240 m²"
                           value="<?php echo htmlspecialchars($_POST['area'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Czas realizacji</label>
                    <input type="text" name="duration" class="form-control"
                           placeholder="np. 3 tygodnie"
                           value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Nazwa klienta (opcjonalnie)</label>
                <input type="text" name="client_name" class="form-control"
                       placeholder="np. Pan Jan Kowalski"
                       value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>">
                <small>Nie będzie wyświetlane publicznie</small>
            </div>
            
        </div>
    </div>
    
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>📸 Zdjęcia</h2>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <strong>💡 Wskazówka:</strong> Dodaj zdjęcie PRZED i PO aby włączyć interaktywny suwak porównania na stronie!
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>Zdjęcie PRZED 🏚️</label>
                    <input type="file" name="image_before" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <small>JPG, PNG lub WEBP, max 5MB</small>
                </div>
                
                <div class="form-group">
                    <label>Zdjęcie PO ✨</label>
                    <input type="file" name="image_after" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <small>JPG, PNG lub WEBP, max 5MB</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Miniatura (opcjonalnie)</label>
                <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
                <small>Jeśli puste, użyje zdjęcia PO jako miniatury</small>
            </div>
            
            <div class="form-group">
                <label>Galeria dodatkowych zdjęć</label>
                <input type="file" name="gallery[]" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                <small>Możesz wybrać wiele zdjęć naraz (Ctrl+klik)</small>
            </div>
            
        </div>
    </div>
    
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-body">
            <div class="form-group">
                <label class="form-group-checkbox">
                    <input type="checkbox" name="is_featured">
                    <span>⭐ Wyróżniona realizacja (wyświetl na górze listy)</span>
                </label>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px;">
        <button type="submit" class="btn btn-success btn-lg">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Dodaj realizację
        </button>
        <a href="realizations.php" class="btn btn-secondary btn-lg">Anuluj</a>
    </div>
    
</form>

<?php include 'includes/admin-footer.php'; ?>