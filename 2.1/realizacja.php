<?php
/**
 * REALIZACJA.PHP - Szczegóły realizacji
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ID
$realizationId = intval($_GET['id'] ?? 0);

if (!$realizationId) {
    header('Location: realizacje.php');
    exit;
}

// Pobierz realizację
try {
    $stmt = $pdo->prepare("SELECT * FROM realizations WHERE id = ? AND status = 'published'");
    $stmt->execute([$realizationId]);
    $real = $stmt->fetch();
    
    if (!$real) {
        header('Location: realizacje.php');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Realization error: " . $e->getMessage());
    header('Location: realizacje.php');
    exit;
}

$pageTitle = $real['title'];

// Rozpakuj galerię
$gallery = json_decode($real['gallery'] ?? '[]', true) ?: [];
?>
<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="/assets/css/before-after-slider.css">
<link rel="stylesheet" href="/assets/css/lightbox.css">

<style>
/* Realizacja - Brand Colors */
.hero-realization {
    padding: 3.75rem 0;
    background: linear-gradient(135deg, #2B59A6 0%, #244C8F 100%);
}

@media (min-width: 768px) {
    .hero-realization {
        padding: 5rem 0;
    }
}

.hero-realization__container {
    max-width: 800px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #FFFFFF;
    text-decoration: none;
    margin-bottom: 1.25rem;
    opacity: 0.9;
    transition: opacity 200ms ease;
    font-size: 1rem;
}

.back-link:hover {
    opacity: 1;
    color: #FFFFFF;
}

.back-link i {
    font-size: 1.25rem;
}

.category-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: #FFFFFF;
    padding: 0.375rem 1rem;
    border-radius: 1.25rem;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    font-weight: 500;
}

.hero-realization h1 {
    color: #FFFFFF;
    font-size: 2.25rem;
    margin: 0 0 1rem 0;
    line-height: 1.2;
    font-weight: 700;
}

@media (min-width: 768px) {
    .hero-realization h1 {
        font-size: 3rem;
    }
}

.hero-realization p {
    color: rgba(255,255,255,0.95);
    font-size: 1.125rem;
    margin: 0;
    line-height: 1.6;
}

@media (min-width: 768px) {
    .hero-realization p {
        font-size: 1.25rem;
    }
}

/* Content Section */
.content-section {
    padding: 3.75rem 0;
}

@media (min-width: 768px) {
    .content-section {
        padding: 5rem 0;
    }
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
    margin-bottom: 3.75rem;
}

@media (min-width: 1024px) {
    .content-grid {
        grid-template-columns: 2fr 1fr;
    }
}

.content-description h2 {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    color: #111827;
    font-weight: 700;
}

@media (min-width: 768px) {
    .content-description h2 {
        font-size: 2rem;
    }
}

.content-description__text {
    font-size: 1rem;
    line-height: 1.8;
    color: #111827;
}

@media (min-width: 768px) {
    .content-description__text {
        font-size: 1.125rem;
    }
}

.project-details {
    background: #F7F8FA;
    padding: 1.875rem;
    border-radius: 0.75rem;
    height: fit-content;
    border: 1px solid #E5E7EB;
}

.project-details h3 {
    font-size: 1.25rem;
    margin: 0 0 1.5rem 0;
    color: #111827;
    font-weight: 700;
}

@media (min-width: 768px) {
    .project-details h3 {
        font-size: 1.5rem;
    }
}

.detail-item {
    margin-bottom: 1.25rem;
    display: flex;
    gap: 0.75rem;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-item i {
    color: #2B59A6;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.detail-item__content {
    flex: 1;
}

.detail-item__label {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.detail-item__value {
    color: #6B7280;
    font-size: 1rem;
}

/* Gallery */
.gallery-heading {
    font-size: 1.75rem;
    margin-bottom: 1.875rem;
    color: #111827;
    font-weight: 700;
}

@media (min-width: 768px) {
    .gallery-heading {
        font-size: 2rem;
    }
}

/* CTA Section */
.cta-realization {
    padding: 4rem 0;
    background: linear-gradient(135deg, #2B59A6 0%, #244C8F 100%);
}

@media (min-width: 768px) {
    .cta-realization {
        padding: 5rem 0;
    }
}

.cta-realization__container {
    text-align: center;
}

.cta-realization h2 {
    color: #FFFFFF;
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

@media (min-width: 768px) {
    .cta-realization h2 {
        font-size: 2.625rem;
    }
}

.cta-realization p {
    color: rgba(255,255,255,0.95);
    font-size: 1.125rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .cta-realization p {
        font-size: 1.25rem;
    }
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #16A34A;
    color: #FFFFFF;
    padding: 1rem 2.5rem;
    border-radius: 0.5rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 200ms ease;
    border: 2px solid #16A34A;
}

.cta-btn-primary:hover {
    background: #15803D;
    border-color: #15803D;
    transform: translateY(-2px);
    color: #FFFFFF;
}

.cta-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    color: #FFFFFF;
    padding: 1rem 2.5rem;
    border-radius: 0.5rem;
    font-weight: 700;
    text-decoration: none;
    border: 2px solid #FFFFFF;
    transition: all 200ms ease;
}

.cta-btn-secondary:hover {
    background: #FFFFFF;
    color: #2B59A6;
    transform: translateY(-2px);
}

/* Mobile */
@media (max-width: 767px) {
    .hero-realization h1 {
        font-size: 2rem;
    }
    
    .content-grid {
        gap: 2rem;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .cta-btn-primary,
    .cta-btn-secondary {
        justify-content: center;
    }
}
</style>

<!-- ============================================
     HERO REALIZACJI
     ============================================ -->
<section class="hero-realization">
    <div class="container">
        <div class="hero-realization__container">
            <a href="realizacje.php" class="back-link">
                <i class="bi bi-arrow-left"></i>
                Powrót do realizacji
            </a>
            
            <span class="category-badge">
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
            <h1><?php echo htmlspecialchars($real['title']); ?></h1>
            <p><?php echo htmlspecialchars($real['description']); ?></p>
        </div>
    </div>
</section>

<!-- ============================================
     SZCZEGÓŁY + GALERIA (2 KOLUMNY)
     ============================================ -->
<section class="content-section">
    <div class="container">
        <div class="content-grid">
            
            <!-- Lewa kolumna - Opis -->
            <div class="content-description">
                <h2>O projekcie</h2>
                <div class="content-description__text">
                    <?php echo nl2br(htmlspecialchars($real['full_description'] ?: $real['description'])); ?>
                </div>
            </div>
            
            <!-- Prawa kolumna - Dane -->
            <div class="project-details">
                <h3>Dane projektu</h3>
                
                <?php if ($real['location']): ?>
                <div class="detail-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <div class="detail-item__content">
                        <div class="detail-item__label">Lokalizacja</div>
                        <div class="detail-item__value"><?php echo htmlspecialchars($real['location']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($real['year']): ?>
                <div class="detail-item">
                    <i class="bi bi-calendar-event"></i>
                    <div class="detail-item__content">
                        <div class="detail-item__label">Rok realizacji</div>
                        <div class="detail-item__value"><?php echo htmlspecialchars($real['year']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($real['area']): ?>
                <div class="detail-item">
                    <i class="bi bi-rulers"></i>
                    <div class="detail-item__content">
                        <div class="detail-item__label">Powierzchnia</div>
                        <div class="detail-item__value"><?php echo htmlspecialchars($real['area']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($real['duration']): ?>
                <div class="detail-item">
                    <i class="bi bi-clock-history"></i>
                    <div class="detail-item__content">
                        <div class="detail-item__label">Czas realizacji</div>
                        <div class="detail-item__value"><?php echo htmlspecialchars($real['duration']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- ============================================
             GALERIA Z SUWAKIEM JAKO PIERWSZA POZYCJA
             ============================================ -->
        
        <h2 class="gallery-heading">Zdjęcia realizacji</h2>
        
        <div class="gallery-grid">
            
            <!-- SUWAK PRZED/PO - JAKO PIERWSZE ZDJĘCIE -->
            <?php if (!empty($real['image_before']) && !empty($real['image_after'])): ?>
            <div class="gallery-item gallery-item--slider">
                <div class="before-after-container" style="height: 100%; border-radius: 12px;">
                    <div class="before-after-slider">
                        <!-- PO (spód) -->
                        <div class="after-image">
                            <img src="<?php echo htmlspecialchars($real['image_after']); ?>" alt="Po">
                            <span class="label label-after">PO</span>
                        </div>
                        <!-- PRZED (wierzch) -->
                        <div class="before-image">
                            <img src="<?php echo htmlspecialchars($real['image_before']); ?>" alt="Przed">
                            <span class="label label-before">PRZED</span>
                        </div>
                        <!-- Suwak -->
                        <div class="slider-handle">
                            <div class="slider-line"></div>
                            <div class="slider-button">
                                <i class="bi bi-chevron-compact-left"></i>
                                <i class="bi bi-chevron-compact-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- RESZTA GALERII -->
            <?php foreach ($gallery as $index => $img): ?>
            <div class="gallery-item" onclick="openLightbox(<?php echo $index; ?>)">
                <img src="<?php echo htmlspecialchars($img); ?>" 
                     alt="Galeria <?php echo $index + 1; ?>">
                <div class="gallery-overlay">
                    <i class="bi bi-zoom-in"></i>
                </div>
            </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</section>

<!-- LIGHTBOX -->
<?php if (!empty($gallery)): ?>
<div id="lightbox" class="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="bi bi-x-lg"></i></button>
    <button class="lightbox-prev" onclick="changeImage(-1)"><i class="bi bi-chevron-left"></i></button>
    <button class="lightbox-next" onclick="changeImage(1)"><i class="bi bi-chevron-right"></i></button>
    <img id="lightbox-img" src="" alt="">
    <div class="lightbox-counter">
        <span id="lightbox-current">1</span> / <span id="lightbox-total"><?php echo count($gallery); ?></span>
    </div>
</div>

<script>
// Galeria images
const galleryImages = <?php echo json_encode($gallery); ?>;
let currentImageIndex = 0;

function openLightbox(index) {
    currentImageIndex = index;
    document.getElementById('lightbox').classList.add('active');
    updateLightboxImage();
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}

function changeImage(direction) {
    currentImageIndex += direction;
    
    if (currentImageIndex < 0) {
        currentImageIndex = galleryImages.length - 1;
    } else if (currentImageIndex >= galleryImages.length) {
        currentImageIndex = 0;
    }
    
    updateLightboxImage();
}

function updateLightboxImage() {
    document.getElementById('lightbox-img').src = galleryImages[currentImageIndex];
    document.getElementById('lightbox-current').textContent = currentImageIndex + 1;
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (!document.getElementById('lightbox').classList.contains('active')) return;
    
    if (e.key === 'ArrowLeft') {
        changeImage(-1);
    } else if (e.key === 'ArrowRight') {
        changeImage(1);
    } else if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Click outside to close
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});
</script>
<?php endif; ?>

<!-- ============================================
     CTA
     ============================================ -->
<section class="cta-realization">
    <div class="container">
        <div class="cta-realization__container">
            <h2>Masz podobny temat?</h2>
            <p>
                Wypełnij formularz i policz orientacyjny koszt w kalkulatorze
            </p>
            <div class="cta-buttons">
                <a href="/kontakt.php" class="cta-btn-primary">
                    <i class="bi bi-envelope"></i>
                    Przejdź do formularza
                </a>
                <a href="/cennik.php" class="cta-btn-secondary">
                    <i class="bi bi-calculator"></i>
                    Policz w kalkulatorze
                </a>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/before-after-slider.js"></script>

<?php include 'includes/footer.php'; ?>