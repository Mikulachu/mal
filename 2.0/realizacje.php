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
                <i class="bi bi-grid-3x3-gap"></i> Wszystkie
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'elewacje' ? 'active' : ''; ?>" 
                    data-filter="elewacje">
                <i class="bi bi-building"></i> Elewacje
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'wnetrza' ? 'active' : ''; ?>" 
                    data-filter="wnetrza">
                <i class="bi bi-house-door"></i> Wnętrza
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'remonty' ? 'active' : ''; ?>" 
                    data-filter="remonty">
                <i class="bi bi-tools"></i> Remonty kompleksowe
            </button>
            <button class="filter-btn <?php echo $filterCategory === 'instytucje' ? 'active' : ''; ?>" 
                    data-filter="instytucje">
                <i class="bi bi-briefcase"></i> Firmy i instytucje
            </button>
        </div>
        
        <div class="filters-count">
            <i class="bi bi-folder-check"></i>
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
            <i class="bi bi-search"></i>
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
                                    <i class="bi bi-chevron-compact-left"></i>
                                    <i class="bi bi-chevron-compact-right"></i>
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
                                <i class="bi bi-geo-alt"></i>
                                <?php echo htmlspecialchars($real['location']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($real['year']): ?>
                            <span class="meta-item">
                                <i class="bi bi-calendar-event"></i>
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
                <a href="/kontakt.php" class="btn btn--primary btn--large">
                    <i class="bi bi-envelope"></i> Przejdź do formularza
                </a>
                <a href="/cennik.php" class="btn btn--secondary btn--large">
                    <i class="bi bi-calculator"></i> Policz w kalkulatorze
                </a>
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