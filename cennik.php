<?php
/**
 * CENNIK.PHP - Kalkulator z rozwijalnymi kategoriami
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Cennik i kalkulator';

// ===== POBIERZ KATEGORIE I USŁUGI Z BAZY =====
$categories = [];
$servicesData = [];

try {
    // Pobierz kategorie (tak jak w admin/pricing.php)
    $categoriesQuery = "SELECT DISTINCT category FROM price_list WHERE active = 1 ORDER BY 
        CASE category 
            WHEN 'elewacje' THEN 1
            WHEN 'wnetrza' THEN 2
            WHEN 'remonty' THEN 3
            WHEN 'dodatkowe' THEN 4
            ELSE 5
        END";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Pobierz usługi
    $stmt = $pdo->query("SELECT * FROM price_list WHERE active = 1 ORDER BY category, id");
    $servicesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Błąd pobierania cennika: " . $e->getMessage());
    $categories = [];
    $servicesData = [];
}
// =========================================

// Nazwy kategorii po polsku
$categoryNames = [
    'elewacje' => 'Elewacje budynków',
    'wnetrza' => 'Wykończenia wnętrz',
    'remonty' => 'Remonty kompleksowe',
    'dodatkowe' => 'Usługi dodatkowe'
];

// Ikony kategorii
$categorySVG = [
    'elewacje' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 10px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'wnetrza' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 10px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>',
    'remonty' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 10px;"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
    'dodatkowe' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 10px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>'
];

// Domyślna ikona dla nieznanych kategorii
$defaultSVG = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 10px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>';

?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/cennik.css">

<style>
/* STYLE DLA ROZWIJANYCH KATEGORII */
.category-section {
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.category-header {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    padding: 20px 25px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    user-select: none;
}

.category-header:hover {
    background: linear-gradient(135deg, #d35400 0%, #c0440d 100%);
}

.category-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.category-toggle {
    font-size: 24px;
    font-weight: 300;
    transition: transform 0.3s ease;
}

.category-section.active .category-toggle {
    transform: rotate(45deg);
}

.category-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
}

.category-section.active .category-body {
    max-height: 2000px;
}

.category-table-wrapper {
    padding: 0;
}

.price-calculator {
    margin: 0;
}

.price-calculator thead {
    background: #34495e;
}

.price-calculator tbody tr:hover {
    background: #f8f9fa;
}

/* Responsywność */
@media (max-width: 768px) {
    .category-header h3 {
        font-size: 16px;
    }
    
    .category-header {
        padding: 15px 20px;
    }
}
</style>

<!-- ============================================
     HERO CENNIK
     ============================================ -->
<section class="hero-pricing">
    <div class="container">
        <div class="hero-pricing__content">
            <h1 class="hero-pricing__title">Cennik i kalkulator</h1>
            <p class="hero-pricing__subtitle">
                To nie jest „wycena z kosmosu". To jest szybki szacunek, żebyś wiedział, z czym się liczysz. Dokładną cenę potwierdzamy po oględzinach.
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     KALKULATOR Z KATEGORIAMI
     ============================================ -->
<section class="section calculator-section">
    <div class="container">
        
        <div class="calculator-wrapper">
            
            <div class="calculator-intro" style="text-align: center; margin-bottom: 40px;">
                <p style="font-size: 18px; color: #6c757d; max-width: 700px; margin: 0 auto;">
                    Wpisz metry, a kalkulator policzy orientacyjny koszt. Kwoty są z materiałem.
                </p>
            </div>
            
            <div class="calculator-table-container" id="calculatorContainer">
                
                <!-- Dynamiczne kategorie z bazy -->
                <?php if (empty($categories)): ?>
                    <div style="text-align: center; padding: 60px; color: #95a5a6;">
                        <p style="font-size: 18px;">Brak kategorii w bazie danych.</p>
                        <p>Dodaj kategorie i usługi w panelu administratora.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $index => $category): ?>
                    <!-- Kategoria: <?php echo htmlspecialchars($category); ?> -->
                    <div class="category-section <?php echo $index === 0 ? 'active' : ''; ?>" data-category="<?php echo htmlspecialchars($category); ?>">
                        <div class="category-header">
                            <h3>
                                <?php echo isset($categorySVG[$category]) ? $categorySVG[$category] : $defaultSVG; ?>
                                <?php echo isset($categoryNames[$category]) ? $categoryNames[$category] : ucfirst($category); ?>
                            </h3>
                            <span class="category-toggle">+</span>
                        </div>
                        <div class="category-body">
                            <div class="category-table-wrapper">
                                <table class="price-calculator">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th>Usługa</th>
                                            <th style="width: 200px;">Cena za m²</th>
                                            <th style="width: 200px;"><?php echo $category === 'dodatkowe' ? 'Ilość / m²' : 'Powierzchnia m²'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="category<?php echo ucfirst($category); ?>">
                                        <!-- Rzędy generowane przez JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="calculator-disclaimer" style="margin-top: 30px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p><strong>Kwoty są orientacyjne.</strong> Ostateczna cena może się zmienić po ocenie na miejscu (stan podłoża, naprawy, dostęp, zabezpieczenia, technologia, materiały).</p>
                </div>
                
                <div class="calculator-actions">
                    <button type="button" class="btn btn--secondary" id="resetCalculator">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="1 4 1 10 7 10"/>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                        </svg>
                        Reset
                    </button>
                    
                    <!-- Wyślij wycenę -->
                    <button type="button" class="btn btn--primary btn--large" id="orderQuoteBtn" style="display: none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        Wyślij wycenę na email
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- ============================================
     INSTYTUCJE
     ============================================ -->
<section class="section section--alt institutions-section">
    <div class="container">
        <div class="institutions-box" style="max-width: 800px; margin: 0 auto; text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <h2 style="font-size: 28px; margin-bottom: 20px; color: #2c3e50;">Dla instytucji i firm</h2>
            <p style="font-size: 18px; color: #6c757d; line-height: 1.8; margin-bottom: 30px;">
                Dla instytucji i firm pracujemy kosztorysowo. Wyślij zapytanie — przygotujemy kosztorys i harmonogram.
            </p>
            <a href="/kontakt.php" class="btn btn--primary btn--large">
                Wyślij zapytanie
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     KONSULTACJE I KURSY
     ============================================ -->
<section class="section consultations-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Konsultacje i kursy</h2>
        </div>
        
        <div class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 900px; margin: 0 auto;">
            
            <!-- Konsultacja online -->
            <div class="service-box" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center;">
                <div class="service-icon" style="font-size: 48px; margin-bottom: 20px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #e67e22;">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c3e50;">Konsultacja online</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">45 minut rozmowy z ekspertem – doradzamy, odpowiadamy na pytania, pomagamy wybrać najlepsze rozwiązania.</p>
                <div class="price" style="font-size: 32px; font-weight: 700; color: #e67e22; margin-bottom: 20px;">200 zł</div>
                <a href="/kontakt.php" class="btn btn--secondary">
                    Umów konsultację
                </a>
            </div>
            
            <!-- Kursy -->
            <div class="service-box" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center;">
                <div class="service-icon" style="font-size: 48px; margin-bottom: 20px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #e67e22;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c3e50;">Kursy malarskie</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Profesjonalne szkolenia z technik malarskich, gładzi, tynków dekoracyjnych. Nauka od praktyków.</p>
                <a href="https://maltechnik.pl/kursy" class="btn btn--primary" target="_blank" rel="noopener">
                    Zobacz kursy
                </a>
            </div>
            
        </div>
    </div>
</section>

<!-- ===== DODANE: Dane z bazy do JavaScript ===== -->
<script>
// Ten skrypt tylko wypełnia tabele danymi z bazy
// Logika kalkulatora jest w calculator-new.js

console.log('📊 Ładowanie danych z bazy...');

const servicesFromDatabase = <?php echo json_encode($servicesData); ?>;

console.log('✅ Załadowano usług:', servicesFromDatabase.length);

// Grupuj po kategoriach
const servicesByCategory = {};

servicesFromDatabase.forEach(service => {
    const cat = service.category;
    if (!servicesByCategory[cat]) {
        servicesByCategory[cat] = [];
    }
    servicesByCategory[cat].push({
        id: service.id,
        name: service.name,
        description: service.description || '',
        category: service.category,
        price_standard: parseFloat(service.price_standard),
        labor_cost: parseFloat(service.labor_cost)
    });
});

console.log('📁 Kategorie:', Object.keys(servicesByCategory));

// Wypełnij tabele po załadowaniu DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Wypełniam tabele...');
    
    Object.keys(servicesByCategory).forEach(category => {
        const services = servicesByCategory[category];
        
        if (services.length === 0) return;
        
        // ID tbody: category + pierwsza wielka
        const tbodyId = 'category' + category.charAt(0).toUpperCase() + category.slice(1);
        const tbody = document.getElementById(tbodyId);
        
        if (!tbody) {
            console.error(`❌ Nie znaleziono tbody #${tbodyId}`);
            return;
        }
        
        // Renderuj rzędy (format zgodny z calculator-new.js)
        tbody.innerHTML = services.map(service => {
            const totalPrice = service.price_standard + service.labor_cost;
            
            return `
                <tr class="calculator-row" 
                    data-id="${service.id}"
                    data-name="${service.name}"
                    data-price="${service.price_standard}"
                    data-labor-cost="${service.labor_cost}">
                    <td class="service-checkbox">
                        <input type="checkbox" 
                               class="service-check" 
                               data-id="${service.id}" 
                               id="service_${service.id}">
                    </td>
                    <td class="service-name">
                        <label for="service_${service.id}">${service.name}</label>
                        ${service.description ? `<small>${service.description}</small>` : ''}
                    </td>
                    <td class="service-price">${totalPrice.toFixed(2)} zł/m²</td>
                    <td class="service-meters">
                        <input type="number" 
                               class="meter-input" 
                               min="0" 
                               step="0.01" 
                               placeholder="0"
                               disabled
                               data-id="${service.id}">
                    </td>
                </tr>
            `;
        }).join('');
        
        console.log(`✅ ${category}: ${services.length} usług`);
    });
    
    console.log('✅ Tabele wypełnione - calculator-new.js przejmuje kontrolę');
});
</script>
<!-- ============================================= -->

<!-- Scripts - CALCULATOR-NEW.JS OBSŁUGUJE LOGIKĘ -->
<script src="/assets/js/calculator-new.js"></script>

<script>
// ROZWIJANIE/ZWIJANIE KATEGORII
document.addEventListener('DOMContentLoaded', function() {
    const categoryHeaders = document.querySelectorAll('.category-header');
    
    categoryHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const section = this.closest('.category-section');
            section.classList.toggle('active');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>