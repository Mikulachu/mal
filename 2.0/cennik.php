<?php
/**
 * CENNIK.PHP - Kalkulator z rozwijalnymi kategoriami
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Cennik i kalkulator';

// Nazwy kategorii po polsku
$categoryNames = [
    'elewacje' => 'Elewacje budynków',
    'wnetrza' => 'Wykończenia wnętrz',
    'remonty' => 'Remonty kompleksowe',
    'dodatkowe' => 'Usługi dodatkowe'
];

// Pobierz listę kategorii dla nagłówków (jeśli są w bazie)
$categories = [];
try {
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
} catch (PDOException $e) {
    error_log("Błąd pobierania kategorii: " . $e->getMessage());
    // Fallback do domyślnych kategorii
    $categories = ['elewacje', 'wnetrza', 'remonty', 'dodatkowe'];
}

// Ikony Bootstrap kategorii
$categoryIcons = [
    'elewacje' => '<i class="bi bi-building" style="vertical-align: middle; margin-right: 10px; font-size: 1.5rem;"></i>',
    'wnetrza' => '<i class="bi bi-door-open" style="vertical-align: middle; margin-right: 10px; font-size: 1.5rem;"></i>',
    'remonty' => '<i class="bi bi-tools" style="vertical-align: middle; margin-right: 10px; font-size: 1.5rem;"></i>',
    'dodatkowe' => '<i class="bi bi-plus-circle" style="vertical-align: middle; margin-right: 10px; font-size: 1.5rem;"></i>'
];

// Domyślna ikona dla nieznanych kategorii
$defaultIcon = '<i class="bi bi-grid" style="vertical-align: middle; margin-right: 10px; font-size: 1.5rem;"></i>';

?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/cennik.css">

<style>
/* STYLE DLA ROZWIJANYCH KATEGORII - Brand Colors */
.category-section {
    margin-bottom: 30px;
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    overflow: hidden;
}

.category-header {
    background: linear-gradient(135deg, #2B59A6 0%, #244C8F 100%);
    color: #FFFFFF;
    padding: 20px 25px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    user-select: none;
}

.category-header:hover {
    background: linear-gradient(135deg, #244C8F 0%, #1e3a73 100%);
}

.category-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
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
    background: #111827;
}

.price-calculator tbody tr:hover {
    background: #F7F8FA;
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
                <p style="font-size: 18px; color: #6B7280; max-width: 700px; margin: 0 auto;">
                    Wpisz metry, a kalkulator policzy orientacyjny koszt. Kwoty są z materiałem.
                </p>
            </div>
            
            <div class="calculator-table-container" id="calculatorContainer">
                
                <!-- Loading state -->
                <div id="loadingState" style="text-align: center; padding: 60px; color: #6B7280;">
                    <div style="display: inline-block; width: 48px; height: 48px; border: 4px solid #E5E7EB; border-top-color: #2B59A6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1rem;"></div>
                    <p style="font-size: 18px; color: #111827; font-weight: 500;">Ładowanie cennika...</p>
                    <p style="font-size: 14px;">Pobieranie aktualnych cen z serwera</p>
                </div>
                
                <style>
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
                </style>
                
                <!-- Dynamiczne kategorie z API -->
                <div id="categoriesWrapper" style="display: none;">
                <?php if (empty($categories)): ?>
                    <div style="text-align: center; padding: 60px; color: #6B7280;">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #E5E7EB; display: block; margin-bottom: 1rem;"></i>
                        <p style="font-size: 18px;">Brak kategorii w bazie danych.</p>
                        <p>Dodaj kategorie i usługi w panelu administratora.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $index => $category): ?>
                    <!-- Kategoria: <?php echo htmlspecialchars($category); ?> -->
                    <div class="category-section <?php echo $index === 0 ? 'active' : ''; ?>" data-category="<?php echo htmlspecialchars($category); ?>">
                        <div class="category-header">
                            <h3>
                                <?php echo isset($categoryIcons[$category]) ? $categoryIcons[$category] : $defaultIcon; ?>
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
                
                </div><!-- #categoriesWrapper -->
                
                <div class="calculator-disclaimer" style="margin-top: 30px;">
                    <i class="bi bi-info-circle" style="font-size: 1.25rem; color: #2B59A6;"></i>
                    <p><strong>Kwoty są orientacyjne.</strong> Ostateczna cena może się zmienić po ocenie na miejscu (stan podłoża, naprawy, dostęp, zabezpieczenia, technologia, materiały).</p>
                </div>
                
                <div class="calculator-actions">
                    <button type="button" class="btn btn--secondary" id="resetCalculator">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                    
                    <!-- Wyślij wycenę -->
                    <button type="button" class="btn btn--primary btn--large" id="orderQuoteBtn" style="display: none;">
                        <i class="bi bi-envelope"></i> Wyślij wycenę na email
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
        <div class="institutions-box" style="max-width: 800px; margin: 0 auto; text-align: center; padding: 40px; background: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <h2 style="font-size: 28px; margin-bottom: 20px; color: #111827;">
                <i class="bi bi-building" style="color: #2B59A6; margin-right: 0.5rem;"></i>
                Dla instytucji i firm
            </h2>
            <p style="font-size: 18px; color: #6B7280; line-height: 1.8; margin-bottom: 30px;">
                Dla instytucji i firm pracujemy kosztorysowo. Wyślij zapytanie — przygotujemy kosztorys i harmonogram.
            </p>
            <a href="/kontakt.php" class="btn btn--primary btn--large">
                <i class="bi bi-send"></i> Wyślij zapytanie
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
            <div class="service-box" style="background: #FFFFFF; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border: 1px solid #E5E7EB;">
                <div class="service-icon" style="font-size: 48px; margin-bottom: 20px;">
                    <i class="bi bi-camera-video" style="color: #2B59A6; font-size: 3rem;"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 15px; color: #111827;">Konsultacja online</h3>
                <p style="color: #6B7280; margin-bottom: 20px;">45 minut rozmowy z ekspertem – doradzamy, odpowiadamy na pytania, pomagamy wybrać najlepsze rozwiązania.</p>
                <div class="price" style="font-size: 32px; font-weight: 700; color: #2B59A6; margin-bottom: 20px;">200 zł</div>
                <a href="/kontakt.php" class="btn btn--secondary">
                    <i class="bi bi-calendar-check"></i> Umów konsultację
                </a>
            </div>
            
            <!-- Kursy -->
            <div class="service-box" style="background: #FFFFFF; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border: 1px solid #E5E7EB;">
                <div class="service-icon" style="font-size: 48px; margin-bottom: 20px;">
                    <i class="bi bi-people" style="color: #2B59A6; font-size: 3rem;"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 15px; color: #111827;">Kursy malarskie</h3>
                <p style="color: #6B7280; margin-bottom: 20px;">Profesjonalne szkolenia z technik malarskich, gładzi, tynków dekoracyjnych. Nauka od praktyków.</p>
                <a href="https://maltechnik.pl/kursy" class="btn btn--primary" target="_blank" rel="noopener">
                    <i class="bi bi-mortarboard"></i> Zobacz kursy
                </a>
            </div>
            
        </div>
    </div>
</section>

<!-- ===== DODANE: Dane z API ===== -->
<script>
// Ten skrypt pobiera dane z API i wypełnia tabele
// Logika kalkulatora jest w calculator-new.js

console.log('📊 Ładowanie danych z API...');

// Funkcja wypełniająca tabele
function populateTables(services) {
    console.log('✅ Załadowano usług:', services.length);
    
    // Grupuj po kategoriach
    const servicesByCategory = {};
    
    services.forEach(service => {
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
    
    // Wypełnij tabele
    Object.keys(servicesByCategory).forEach(category => {
        const services = servicesByCategory[category];
        
        if (services.length === 0) return;
        
        console.log(`🔍 Kategoria ${category}: ${services.length} usług`);
        console.log(`🔍 Pierwsza usługa:`, services[0]);
        
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
            
            // Fallback dla brakujących danych
            const serviceName = service.name || 'Placeholder - usuń lub edytuj';
            const serviceDesc = service.description || 'Usługa tymczasowa';
            
            const html = `
                <tr class="calculator-row" 
                    data-id="${service.id}"
                    data-name="${serviceName}"
                    data-price="${service.price_standard}"
                    data-labor-cost="${service.labor_cost}">
                    <td class="service-checkbox">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" 
                                   class="service-check" 
                                   data-id="${service.id}" 
                                   id="service_${service.id}">
                            <label for="service_${service.id}">${serviceName}</label>
                        </div>
                        ${serviceDesc ? `<small class="service-desc">${serviceDesc}</small>` : ''}
                    </td>
                    <td class="service-name">
                        <span class="service-name-desktop">${serviceName}</span>
                        ${serviceDesc ? `<small class="service-desc-desktop">${serviceDesc}</small>` : ''}
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
            
            // DEBUG: Pokaż pierwszą usługę
            if (service.id === services[0].id) {
                console.log('🔍 Przykładowa usługa:', service.name || '(BRAK NAZWY)', service);
            }
            
            return html;
        }).join('');
        
        console.log(`✅ ${category}: ${services.length} usług`);
    });
    
    console.log('✅ Tabele wypełnione - calculator-new.js przejmuje kontrolę');
}

// Pobierz dane z API
const apiPath = '/api/get-prices.php';
console.log('🔄 Pobieranie z:', window.location.origin + apiPath);

fetch(apiPath)
    .then(response => {
        console.log('📡 Response status:', response.status);
        console.log('📡 Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📦 Otrzymane dane:', data);
        
        if (data.success && data.data && Array.isArray(data.data)) {
            // Ukryj loading
            const loadingState = document.getElementById('loadingState');
            if (loadingState) {
                loadingState.style.display = 'none';
            }
            
            // Pokaż kategorie
            const categoriesWrapper = document.getElementById('categoriesWrapper');
            if (categoriesWrapper) {
                categoriesWrapper.style.display = 'block';
            }
            
            // Wypełnij tabele
            populateTables(data.data);
        } else {
            console.error('❌ Nieprawidłowy format danych:', data);
            throw new Error('Nieprawidłowy format danych z API');
        }
    })
    .catch(error => {
        console.error('❌ Błąd ładowania danych z API:', error);
        console.error('❌ Error details:', error.message);
        
        // Ukryj loading
        const loadingState = document.getElementById('loadingState');
        if (loadingState) {
            loadingState.style.display = 'none';
        }
        
        // Pokaż komunikat błędu
        const container = document.getElementById('calculatorContainer');
        if (container) {
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = 'text-align: center; padding: 60px; color: #DC2626;';
            errorDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle" style="font-size: 4rem; display: block; margin-bottom: 1rem; color: #DC2626;"></i>
                <p style="font-size: 18px; margin-bottom: 10px; font-weight: 600;">Nie udało się załadować cennika.</p>
                <p style="color: #6B7280; margin-bottom: 10px;">Błąd: ${error.message}</p>
                <p style="color: #6B7280; margin-bottom: 20px; font-size: 14px;">Sprawdź konsolę przeglądarki (F12) aby zobaczyć więcej szczegółów.</p>
                <button onclick="location.reload()" style="background: #2B59A6; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="bi bi-arrow-clockwise"></i> Odśwież stronę
                </button>
            `;
            container.appendChild(errorDiv);
        }
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