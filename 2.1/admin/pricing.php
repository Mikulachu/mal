<?php
/**
 * PRICING.PHP - Zarządzanie cennikiem
 * Używa: admin-header.php (jak reszta systemu)
 */

require_once '../includes/db.php';
require_once 'includes/admin-auth.php';

requireLogin();

$pageTitle = 'Zarządzanie cennikiem';
$currentPage = 'pricing';
$admin = getAdminData();

$success = '';
$errors = [];

try {
    // Pobierz kategorie z price_list
    $categoriesQuery = "SELECT DISTINCT category FROM price_list WHERE 1=1 ORDER BY 
        CASE category 
            WHEN 'elewacje' THEN 1
            WHEN 'wnetrza' THEN 2
            WHEN 'remonty' THEN 3
            WHEN 'dodatkowe' THEN 4
            ELSE 5
        END";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($categories)) {
        $categories = ['elewacje', 'wnetrza', 'remonty', 'dodatkowe'];
    }

    // Pobierz usługi z price_list
    $servicesQuery = "SELECT * FROM price_list ORDER BY category, id";
    $servicesStmt = $pdo->query($servicesQuery);
    $allServices = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Grupuj po kategoriach
    $servicesByCategory = [];
    foreach ($allServices as $service) {
        $cat = $service['category'];
        if (!isset($servicesByCategory[$cat])) {
            $servicesByCategory[$cat] = [];
        }
        $servicesByCategory[$cat][] = $service;
    }
    
} catch (PDOException $e) {
    $errors[] = 'Błąd bazy danych: ' . $e->getMessage();
}

// Success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'add': $success = 'Usługa została dodana'; break;
        case 'edit': $success = 'Usługa została zaktualizowana'; break;
        case 'delete': $success = 'Usługa została usunięta'; break;
        case 'category': $success = 'Kategoria została dodana'; break;
    }
}

include 'includes/admin-header.php';
?>

<div class="content-header">
    <h1><?php echo $pageTitle; ?></h1>
    <p>Zarządzaj cennikiem usług</p>
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

<!-- PRZYCISK DODAJ KATEGORIĘ -->
<div style="margin-bottom: 24px;">
    <button class="btn btn-success" onclick="document.getElementById('categoryModal').style.display='block'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="16"/>
            <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
        Dodaj nową kategorię
    </button>
</div>

<!-- KATEGORIE Z USŁUGAMI -->
<?php foreach ($categories as $category): ?>
<div class="pricing-section active" data-category="<?php echo htmlspecialchars($category); ?>" style="margin-bottom: 24px; border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden; background: white;">
    <div class="pricing-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0; font-size: 20px;"><?php echo ucfirst($category); ?></h2>
        <span class="pricing-toggle" style="font-size: 28px; font-weight: 300; transition: transform 0.3s;">+</span>
    </div>
    
    <div class="pricing-body">
        <div style="padding: 20px;">
            
            <?php if (isset($servicesByCategory[$category]) && count($servicesByCategory[$category]) > 0): ?>
            <table class="data-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nazwa usługi</th>
                        <th>Opis</th>
                        <th style="width: 120px;">Cena materiału</th>
                        <th style="width: 120px;">Robocizna</th>
                        <th style="width: 120px;">Cena całkowita</th>
                        <th style="width: 150px; text-align: right;">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicesByCategory[$category] as $service): ?>
                    <tr>
                        <td><?php echo $service['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($service['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($service['description'] ?? ''); ?></td>
                        <td><?php echo number_format($service['price_standard'], 2); ?> zł</td>
                        <td><?php echo number_format($service['labor_cost'] ?? 0, 2); ?> zł</td>
                        <td><strong><?php echo number_format($service['price_standard'] + ($service['labor_cost'] ?? 0), 2); ?> zł</strong></td>
                        <td style="text-align: right;">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" 
                                        onclick='editService(<?php echo htmlspecialchars(json_encode($service)); ?>)'
                                        title="Edytuj">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="deleteService(<?php echo $service['id']; ?>)"
                                        title="Usuń">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #95a5a6; font-style: italic;">
                Brak usług w tej kategorii
            </div>
            <?php endif; ?>
            
            <button class="btn btn-success" onclick="openServiceModal('<?php echo htmlspecialchars($category); ?>')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Dodaj usługę do kategorii "<?php echo ucfirst($category); ?>"
            </button>
            
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- MODAL: DODAJ/EDYTUJ USŁUGĘ -->
<div id="serviceModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2 id="serviceModalTitle">Dodaj usługę</h2>
            <button class="modal-close" onclick="document.getElementById('serviceModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="api/pricing-handler.php" id="serviceForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="serviceId">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Nazwa usługi *</label>
                    <input type="text" name="name" id="serviceName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Opis</label>
                    <textarea name="description" id="serviceDescription" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Kategoria *</label>
                    <select name="category" id="serviceCategory" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo ucfirst($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Cena materiału (zł/m²) *</label>
                    <input type="number" name="price_standard" id="servicePriceStandard" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Koszt robocizny (zł/m²)</label>
                    <input type="number" name="labor_cost" id="serviceLaborCost" class="form-control" step="0.01" min="0" value="0">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('serviceModal').style.display='none'">Anuluj</button>
                <button type="submit" class="btn btn-primary">Zapisz</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: DODAJ KATEGORIĘ -->
<div id="categoryModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Dodaj nową kategorię</h2>
            <button class="modal-close" onclick="document.getElementById('categoryModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="api/pricing-handler.php" id="categoryForm">
            <input type="hidden" name="action" value="add_category">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Nazwa kategorii *</label>
                    <input type="text" name="category_name" class="form-control" required placeholder="np. elewacje, wnetrza">
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                        Używaj małych liter bez polskich znaków
                    </small>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('categoryModal').style.display='none'">Anuluj</button>
                <button type="submit" class="btn btn-success">Utwórz kategorię</button>
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
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
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

.pricing-header:hover {
    background: linear-gradient(135deg, #2980b9 0%, #21618c 100%) !important;
}

.pricing-section .pricing-toggle {
    transition: transform 0.3s ease;
}

.pricing-section.active .pricing-toggle {
    transform: rotate(45deg);
}

.pricing-section .pricing-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
}

.pricing-section.active .pricing-body {
    max-height: 5000px;
}
</style>

<script>
// Rozwijanie/zwijanie kategorii - NAPRAWIONE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Inicjalizacja rozwijania kategorii...');
    
    const headers = document.querySelectorAll('.pricing-header');
    console.log('Znaleziono headerów:', headers.length);
    
    headers.forEach((header, index) => {
        console.log('Dodaję listener do headera', index);
        
        header.addEventListener('click', function(e) {
            console.log('Kliknięto header:', index);
            
            const section = this.closest('.pricing-section');
            const isActive = section.classList.contains('active');
            
            console.log('Section active przed:', isActive);
            
            if (isActive) {
                section.classList.remove('active');
                console.log('Zwijam kategorię');
            } else {
                section.classList.add('active');
                console.log('Rozwijam kategorię');
            }
        });
    });
    
    console.log('✅ Rozwijanie kategorii zainicjalizowane');
});

// Otwórz modal dodawania usługi
function openServiceModal(category) {
    document.getElementById('serviceForm').reset();
    document.getElementById('serviceId').value = '';
    document.getElementById('serviceModalTitle').textContent = 'Dodaj usługę';
    if (category) {
        document.getElementById('serviceCategory').value = category;
    }
    document.getElementById('serviceModal').style.display = 'block';
}

// Edytuj usługę
function editService(service) {
    document.getElementById('serviceModalTitle').textContent = 'Edytuj usługę';
    document.getElementById('serviceId').value = service.id;
    document.getElementById('serviceName').value = service.name;
    document.getElementById('serviceDescription').value = service.description || '';
    document.getElementById('serviceCategory').value = service.category;
    document.getElementById('servicePriceStandard').value = service.price_standard;
    document.getElementById('serviceLaborCost').value = service.labor_cost || 0;
    
    document.getElementById('serviceModal').style.display = 'block';
}

// Usuń usługę
async function deleteService(id) {
    if (!confirm('Czy na pewno chcesz usunąć tę usługę?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const response = await fetch('api/pricing-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.href = 'pricing.php?success=delete';
        } else {
            alert('Błąd: ' + result.message);
        }
    } catch (error) {
        alert('Błąd połączenia');
    }
}

// Submit formularzy przez AJAX
document.getElementById('serviceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/pricing-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.href = 'pricing.php?success=' + (formData.get('id') ? 'edit' : 'add');
        } else {
            alert('Błąd: ' + result.message);
        }
    } catch (error) {
        alert('Błąd połączenia');
    }
});

document.getElementById('categoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/pricing-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.href = 'pricing.php?success=category';
        } else {
            alert('Błąd: ' + result.message);
        }
    } catch (error) {
        alert('Błąd połączenia');
    }
});

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