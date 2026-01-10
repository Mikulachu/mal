<?php
/**
 * REALIZACJE.PHP - Portfolio realizacji 
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Nasze realizacje';

// ============================================
// POBIERZ REALIZACJE Z BAZY
// ============================================

$filterCategory = isset($_GET['kategoria']) ? sanitizeInput($_GET['kategoria']) : 'wszystkie';

// Buduj zapytanie
$sql = "SELECT * FROM realizations WHERE status = 'published'";
$params = [];

if ($filterCategory && $filterCategory !== 'wszystkie') {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
}

$sql .= " ORDER BY is_featured DESC, created_at DESC";

// Wykonaj zapytanie
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $realizations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Realizations error: " . $e->getMessage());
    $realizations = [];
}

// Zlicz realizacje
$totalCount = count($realizations);
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/realizacje.css">
<link rel="stylesheet" href="/assets/css/before-after-slider.css">

<!-- ============================================
     HERO REALIZACJE
     ============================================ -->
<section class="hero-portfolio">
    <div class="container">
        <div class="hero-portfolio__content">
            <h1 class="hero-portfolio__title">Nasze realizacje</h1>
            <p class="hero-portfolio__subtitle">
                Nie sprzedajemy „robót budowlanych". Sprzedajemy spokój: termin, porządek i efekt, który nie wymaga poprawek. Poniżej masz wybrane realizacje z Chojnic i okolic — domy prywatne oraz obiekty publiczne. Przy każdej pokazujemy, z jakim problemem był klient i jak go rozwiązaliśmy.
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     FILTRY
     ============================================ -->
<section class="filters-section">
    <div class="container">
        <div class="filters">
            <button class="filter-btn <?php echo $filterCategory === 'wszystkie' ? 'active' : ''; ?>" 
                    data-filter="wszystkie">
                Wszystkie
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'elewacje' ? 'active' : ''; ?>" 
                    data-filter="elewacje">
                Elewacje
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'wnetrza' ? 'active' : ''; ?>" 
                    data-filter="wnetrza">
                Wnętrza
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'remonty' ? 'active' : ''; ?>" 
                    data-filter="remonty">
                Remonty kompleksowe
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'instytucje' ? 'active' : ''; ?>" 
                    data-filter="instytucje">
                Firmy i instytucje
            </button>
        </div>
        
        <div class="filters-count">
            <span id="resultsCount"><?php echo $totalCount; ?></span> realizacji
        </div>
    </div>
</section>

<!-- ============================================
     PORTFOLIO GRID
     ============================================ -->
<section class="section portfolio-section">
    <div class="container">
        
        <?php if (empty($realizations)): ?>
        <!-- Brak realizacji -->
        <div class="portfolio-no-results">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <h3>Brak realizacji</h3>
            <p>Nie znaleziono realizacji w wybranej kategorii. <a href="?kategoria=wszystkie">Pokaż wszystkie</a></p>
        </div>
        
        <?php else: ?>
        
        <div class="portfolio-grid" id="portfolioGrid">
            
            <?php foreach ($realizations as $real): ?>
            <!-- Realizacja z bazy danych -->
            <article class="portfolio-item" data-category="<?php echo htmlspecialchars($real['category']); ?>">
                
                <!-- SUWAK PRZED/PO (jeśli są oba zdjęcia) -->
                <?php if (!empty($real['image_before']) && !empty($real['image_after'])): ?>
                <a href="realizacja.php?id=<?php echo $real['id']; ?>" class="portfolio-item__link-wrapper">
                    <div class="portfolio-item__image before-after-container">
                        <div class="before-after-slider" data-realization-id="<?php echo $real['id']; ?>">
                            
                            <!-- ZDJĘCIE PO (na spodzie, z-index 1) -->
                            <div class="after-image">
                                <img src="<?php echo htmlspecialchars($real['image_after']); ?>" 
                                     alt="Po - <?php echo htmlspecialchars($real['title']); ?>" 
                                     loading="lazy">
                                <span class="label label-after">PO</span>
                            </div>
                            
                            <!-- ZDJĘCIE PRZED (na wierzchu, z-index 2, obcinane przez clip-path) -->
                            <div class="before-image">
                                <img src="<?php echo htmlspecialchars($real['image_before']); ?>" 
                                     alt="Przed - <?php echo htmlspecialchars($real['title']); ?>" 
                                     loading="lazy">
                                <span class="label label-before">PRZED</span>
                            </div>
                            
                            <!-- SUWAK -->
                            <div class="slider-handle">
                                <div class="slider-line"></div>
                                <div class="slider-button">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M15 6l-6 6 6 6V6z"/>
                                        <path d="M9 6l6 6-6 6V6z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                
                <?php else: ?>
                <!-- Standardowe zdjęcie (bez suwaka) -->
                <a href="realizacja.php?id=<?php echo $real['id']; ?>" class="portfolio-item__link-wrapper">
                    <div class="portfolio-item__image">
                        <img src="<?php echo htmlspecialchars($real['thumbnail'] ?: $real['image_after'] ?: $real['image_before'] ?: 'assets/img/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($real['title']); ?>" 
                             loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
                
                <!-- Treść (też klikalna) -->
                <a href="realizacja.php?id=<?php echo $real['id']; ?>" class="portfolio-item__content-link">
                    <div class="portfolio-item__content">
                        <span class="portfolio-item__category">
                            <?php 
                            $catNames = [
                                'elewacje' => 'Elewacje',
                                'wnetrza' => 'Wnętrza',
                                'remonty' => 'Remonty',
                                'instytucje' => 'Instytucje'
                            ];
                            echo $catNames[$real['category']] ?? ucfirst($real['category']); 
                            ?>
                        </span>
                        <h3 class="portfolio-item__title">
                            <?php echo htmlspecialchars($real['title']); ?>
                        </h3>
                        <p class="portfolio-item__desc">
                            <?php echo htmlspecialchars(mb_substr($real['description'], 0, 100)); ?>
                            <?php if (strlen($real['description']) > 100): ?>...<?php endif; ?>
                        </p>
                        <div class="portfolio-item__meta">
                            <?php if ($real['location']): ?>
                            <span class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <?php echo htmlspecialchars($real['location']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($real['year']): ?>
                            <span class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <?php echo htmlspecialchars($real['year']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
            
        </div>
        
        <?php endif; ?>
        
    </div>
</section>

<!-- ============================================
     CTA
     ============================================ -->
<section class="section section--alt cta-portfolio">
    <div class="container">
        <div class="cta-portfolio__content">
            <h2>Masz podobny temat?</h2>
            <p>Wypełnij formularz i policz orientacyjny koszt w kalkulatorze</p>
            <div class="cta-portfolio__buttons">
                <a href="/kontakt.php" class="btn btn--primary btn--large">Przejdź do formularza</a>
                <a href="/cennik.php" class="btn btn--secondary btn--large">Policz w kalkulatorze</a>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="/assets/js/portfolio.js"></script>
<script src="/assets/js/before-after-slider.js"></script>

<!-- Filtrowanie kategoriami -->
<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        window.location.href = '?kategoria=' + filter;
    });
});
</script>

<?php include 'includes/footer.php'; ?>