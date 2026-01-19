<?php
/**
 * ADMIN-PRICE-LIST.PHP - Prosty panel admina do edycji cen
 * 
 * PRZYKŁADOWY PLIK - do wdrożenia w przyszłości
 */

require_once '../includes/functions.php';
require_once '../includes/db.php';

// Sprawdź czy admin jest zalogowany
// if (!isAdmin()) { redirect('/login.php'); }

$pageTitle = 'Edycja cennika - Panel Admina';

// Pobierz wszystkie ceny
try {
    $sql = "SELECT * FROM price_list ORDER BY category, sort_order, id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Błąd bazy danych');
}

// Grupuj po kategoriach
$categories = [
    'elewacje' => 'Elewacje budynków',
    'wnetrza' => 'Wykończenia wnętrz',
    'remonty' => 'Remonty kompleksowe',
    'dodatkowe' => 'Usługi dodatkowe'
];

$grouped = [];
foreach ($prices as $price) {
    $grouped[$price['category']][] = $price;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2c3e50; margin-bottom: 30px; }
        .category { background: white; border-radius: 8px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .category h2 { color: #34495e; font-size: 20px; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #3498db; }
        .price-item { display: grid; grid-template-columns: 2fr 120px 120px 100px 80px; gap: 16px; align-items: center; padding: 16px; border-bottom: 1px solid #ecf0f1; }
        .price-item:last-child { border-bottom: none; }
        .price-name { font-weight: 500; color: #2c3e50; }
        .price-desc { font-size: 13px; color: #7f8c8d; margin-top: 4px; }
        .price-input { padding: 8px 12px; border: 2px solid #e0e6ed; border-radius: 6px; font-size: 14px; width: 100%; }
        .price-input:focus { outline: none; border-color: #3498db; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-save { background: #27ae60; color: white; }
        .btn-save:hover { background: #229954; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .status-active { background: #d5f4e6; color: #27ae60; }
        .status-inactive { background: #fadbd8; color: #c0392b; }
        .success { background: #d5f4e6; color: #27ae60; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .error { background: #fadbd8; color: #c0392b; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .info { background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; border-radius: 6px; margin-bottom: 24px; }
        .info p { color: #856404; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📊 Edycja cennika</h1>
        <a href="/admin/" class="btn" style="background: #95a5a6; color: white;">← Powrót</a>
    </div>
    
    <div class="info">
        <p><strong>ℹ️ Informacja:</strong> Zmiany w cenniku są natychmiastowo widoczne w kalkulatorze na stronie. Ceny podawaj w zł za m².</p>
    </div>
    
    <div id="message"></div>
    
    <?php foreach ($categories as $catKey => $catName): ?>
        <?php if (isset($grouped[$catKey]) && count($grouped[$catKey]) > 0): ?>
        <div class="category">
            <h2><?php echo h($catName); ?></h2>
            
            <?php foreach ($grouped[$catKey] as $price): ?>
            <div class="price-item">
                <div>
                    <div class="price-name"><?php echo h($price['name']); ?></div>
                    <?php if ($price['description']): ?>
                    <div class="price-desc"><?php echo h($price['description']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; color: #7f8c8d; margin-bottom: 4px;">Standard</label>
                    <input type="number" 
                           class="price-input" 
                           data-id="<?php echo h($price['id']); ?>" 
                           data-field="price_standard"
                           value="<?php echo h($price['price_standard']); ?>" 
                           step="0.01" 
                           min="0">
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; color: #7f8c8d; margin-bottom: 4px;">Premium</label>
                    <input type="number" 
                           class="price-input" 
                           data-id="<?php echo h($price['id']); ?>" 
                           data-field="price_premium"
                           value="<?php echo h($price['price_premium']); ?>" 
                           step="0.01" 
                           min="0"
                           placeholder="—">
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; color: #e67e22; margin-bottom: 4px;">🔒 Robocizna</label>
                    <input type="number" 
                           class="price-input" 
                           data-id="<?php echo h($price['id']); ?>" 
                           data-field="labor_cost"
                           value="<?php echo h($price['labor_cost'] ?? 0); ?>" 
                           step="0.01" 
                           min="0"
                           style="border-color: #e67e22;">
                    <small style="color: #7f8c8d; font-size: 10px;">Ukryta dla klienta</small>
                </div>
                
                <div>
                    <span class="status <?php echo $price['active'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $price['active'] ? 'Aktywna' : 'Ukryta'; ?>
                    </span>
                </div>
                
                <div>
                    <button class="btn btn-save" onclick="savePrice('<?php echo h($price['id']); ?>')">Zapisz</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<script>
// Dodaj event listener do każdego inputa - zapisuj na zmianę
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.price-input').forEach(input => {
        input.addEventListener('blur', function() {
            const id = this.dataset.id;
            const field = this.dataset.field;
            const value = parseFloat(this.value) || 0;
            
            saveSingleField(id, field, value);
        });
    });
});

async function saveSingleField(id, field, value) {
    const data = {
        id: id,
        field: field,
        value: value
    };
    
    try {
        const response = await fetch('/api/update-price.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(`✅ ${field === 'labor_cost' ? 'Robocizna' : 'Cena'} zapisana!`, 'success');
        } else {
            showMessage('❌ Błąd: ' + result.message, 'error');
        }
    } catch (error) {
        showMessage('❌ Błąd połączenia', 'error');
    }
}

async function savePrice(id) {
    const standardInput = document.querySelector(`input[data-id="${id}"][data-field="price_standard"]`);
    const premiumInput = document.querySelector(`input[data-id="${id}"][data-field="price_premium"]`);
    const laborInput = document.querySelector(`input[data-id="${id}"][data-field="labor_cost"]`);
    
    // Zapisz wszystkie pola naraz
    await saveSingleField(id, 'price_standard', parseFloat(standardInput.value) || 0);
    if (premiumInput.value) {
        await saveSingleField(id, 'price_premium', parseFloat(premiumInput.value) || 0);
    }
    if (laborInput) {
        await saveSingleField(id, 'labor_cost', parseFloat(laborInput.value) || 0);
    }
}

function showMessage(text, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.className = type;
    messageDiv.textContent = text;
    
    setTimeout(() => {
        messageDiv.className = '';
        messageDiv.textContent = '';
    }, 3000);
}
</script>

</body>
</html>