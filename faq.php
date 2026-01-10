<?php
/**
 * FAQ.PHP - Najczęściej zadawane pytania
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Najczęstsze pytania';

// ============================================
// POBIERZ FAQ Z BAZY DANYCH
// ============================================

// Pobierz aktywne FAQ pogrupowane po kategoriach
$faqData = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM faq 
        WHERE is_visible = 1 
        ORDER BY category, order_index ASC, id ASC
    ");
    
    while ($row = $stmt->fetch()) {
        $faqData[$row['category']][] = $row;
    }
} catch (PDOException $e) {
    error_log("FAQ error: " . $e->getMessage());
    // Jeśli błąd - pokazuj hardcoded wersję poniżej
}

// Nazwy kategorii (mapowanie z bazy na polskie nazwy)
$categoryNames = [
    'ogólne' => 'Ogólne pytania',
    'wycena' => 'Wycena i koszty',
    'realizacja' => 'Realizacja projektu',
    'gwarancja' => 'Gwarancja i serwis',
    'materiały' => 'Materiały i techniki',
    'konsultacje' => 'Konsultacje i doradztwo'
];
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/faq.css">

<!-- ============================================
     HERO FAQ
     ============================================ -->
<section class="hero-faq">
    <div class="container">
        <div class="hero-faq__content">
            <h1 class="hero-faq__title">Najczęściej zadawane pytania</h1>
            <p class="hero-faq__subtitle">
                Wszystko, co chcesz wiedzieć o współpracy z nami
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     SEARCH
     ============================================ -->
<section class="faq-search-section">
    <div class="container">
        <div class="faq-search">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" 
                   id="faqSearch" 
                   placeholder="Wpisz pytanie lub słowo kluczowe..." 
                   class="faq-search__input">
        </div>
        <p class="faq-search__hint">np. "ile kosztuje", "jak długo", "gwarancja"</p>
    </div>
</section>

<!-- ============================================
     FAQ CATEGORIES (Z BAZY DANYCH)
     ============================================ -->
<section class="section faq-section">
    <div class="container">
        
        <?php if (!empty($faqData)): ?>
            <?php foreach ($categoryNames as $categoryKey => $categoryName): ?>
                <?php if (isset($faqData[$categoryKey]) && !empty($faqData[$categoryKey])): ?>
                
                <!-- Kategoria: <?php echo htmlspecialchars($categoryName); ?> -->
                <div class="faq-category">
                    <h2 class="faq-category__title"><?php echo htmlspecialchars($categoryName); ?></h2>
                    
                    <div class="accordion">
                        <?php foreach ($faqData[$categoryKey] as $faq): ?>
                        <div class="accordion__item">
                            <button class="accordion__header">
                                <span class="accordion__title"><?php echo htmlspecialchars($faq['question']); ?></span>
                                <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div class="accordion__content">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php endif; ?>
            <?php endforeach; ?>
            
        <?php else: ?>
            <!-- FALLBACK: Jeśli baza pusta - pokaż hardcoded wersję -->
            
            <!-- Kategoria: Ogólne -->
            <div class="faq-category">
                <h2 class="faq-category__title">Ogólne pytania</h2>
                
                <div class="accordion">
                    <div class="accordion__item">
                        <button class="accordion__header">
                            <span class="accordion__title">Na jakim obszarze działacie?</span>
                            <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <p>Działamy głównie na terenie województwa pomorskiego (Gdańsk, Gdynia, Sopot, Chojnice, Kartuzy, Pruszcz Gdański) oraz kujawsko-pomorskiego (Bydgoszcz, Toruń). Przy większych projektach jesteśmy gotowi podjąć się realizacji również w innych regionach Polski.</p>
                        </div>
                    </div>
                    
                    <div class="accordion__item">
                        <button class="accordion__header">
                            <span class="accordion__title">Jak długo działa Wasza firma?</span>
                            <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <p>Działamy od 12 lat. Przez ten czas zrealizowaliśmy ponad 100 projektów – od drobnych malowań wnętrz po kompleksowe remonty domów i elewacje budynków komercyjnych.</p>
                        </div>
                    </div>
                    
                    <div class="accordion__item">
                        <button class="accordion__header">
                            <span class="accordion__title">Czy jesteście ubezpieczeni?</span>
                            <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <p>Tak, posiadamy pełne ubezpieczenie OC i NNW. W razie jakichkolwiek szkód powstałych podczas prac, jesteśmy w pełni ubezpieczeni i odpowiadamy za ewentualne straty.</p>
                        </div>
                    </div>
                    
                    <div class="accordion__item">
                        <button class="accordion__header">
                            <span class="accordion__title">Czy możecie pokazać referencje?</span>
                            <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <p>Oczywiście. Na naszej stronie znajdziesz <a href="/realizacje.php">portfolio zrealizowanych projektów</a> ze zdjęciami przed i po. Możemy również udostępnić kontakty do naszych klientów, którzy chętnie podzielą się swoimi doświadczeniami.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Kategoria: Wycena i koszty -->
            <div class="faq-category">
                <h2 class="faq-category__title">Wycena i koszty</h2>
                
                <div class="accordion">
                    <div class="accordion__item">
                        <button class="accordion__header">
                            <span class="accordion__title">Ile kosztuje wycena?</span>
                            <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <p>Wycena jest całkowicie bezpłatna i do niczego nie zobowiązuje. Przyjeżdżamy na miejsce, wykonujemy pomiary, omawiamy Twoje potrzeby i w ciągu 24-48 godzin dostarczamy szczegółową wycenę.</p>
                        </div>
                    </div>
                    
                    <div class="accordion__item">
                        <button class="accordion__header">
                            <span class="accordion__title">Czy ceny zawierają materiały?</span>
                            <svg class="accordion__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <p>Tak, wszystkie nasze ceny zawierają zarówno materiały premium, jak i robociznę. Nie ma ukrytych kosztów. W wycenie dokładnie wyszczególniamy, jakie materiały będą użyte.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Więcej kategorii... -->
            
        <?php endif; ?>
        
    </div>
</section>

<!-- ============================================
     CTA
     ============================================ -->
<section class="section section--alt cta-faq">
    <div class="container">
        <div class="cta-faq__content">
            <h2>Nie znalazłeś odpowiedzi?</h2>
            <p>Skontaktuj się z nami – chętnie odpowiemy na wszystkie pytania</p>
            <div class="cta-faq__buttons">
                <a href="/kontakt.php" class="btn btn--primary btn--large">Zadaj pytanie</a>
                <a href="tel:+48123456789" class="btn btn--secondary btn--large">📞 Zadzwoń</a>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="/assets/js/faq.js"></script>

<?php include 'includes/footer.php'; ?>