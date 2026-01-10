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

<!-- ============================================
     HERO REALIZACJI
     ============================================ -->
<section class="hero-realization" style="padding: 60px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div style="max-width: 800px;">
            <a href="realizacje.php" style="display: inline-flex; align-items: center; gap: 8px; color: white; text-decoration: none; margin-bottom: 20px; opacity: 0.9; transition: opacity 0.2s;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Powrót do realizacji
            </a>
            
            <span style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 6px 16px; border-radius: 20px; font-size: 14px; margin-bottom: 16px;">
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
            <h1 style="color: white; font-size: 48px; margin: 0 0 16px 0; line-height: 1.2;"><?php echo htmlspecialchars($real['title']); ?></h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 20px; margin: 0;"><?php echo htmlspecialchars($real['description']); ?></p>
        </div>
    </div>
</section>

<!-- ============================================
     SZCZEGÓŁY + GALERIA (2 KOLUMNY)
     ============================================ -->
<section style="padding: 60px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 60px; margin-bottom: 60px;">
            
            <!-- Lewa kolumna - Opis -->
            <div>
                <h2 style="font-size: 32px; margin-bottom: 24px; color: #2c3e50;">O projekcie</h2>
                <div style="font-size: 18px; line-height: 1.8; color: #555;">
                    <?php echo nl2br(htmlspecialchars($real['full_description'] ?: $real['description'])); ?>
                </div>
            </div>
            
            <!-- Prawa kolumna - Dane -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 12px; height: fit-content;">
                <h3 style="font-size: 24px; margin: 0 0 24px 0; color: #2c3e50;">Dane projektu</h3>
                
                <?php if ($real['location']): ?>
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #333; margin-bottom: 4px;">📍 Lokalizacja</div>
                    <div style="color: #666;"><?php echo htmlspecialchars($real['location']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($real['year']): ?>
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #333; margin-bottom: 4px;">📅 Rok realizacji</div>
                    <div style="color: #666;"><?php echo htmlspecialchars($real['year']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($real['area']): ?>
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #333; margin-bottom: 4px;">📐 Powierzchnia</div>
                    <div style="color: #666;"><?php echo htmlspecialchars($real['area']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($real['duration']): ?>
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #333; margin-bottom: 4px;">⏱️ Czas realizacji</div>
                    <div style="color: #666;"><?php echo htmlspecialchars($real['duration']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- ============================================
             GALERIA Z SUWAKIEM JAKO PIERWSZA POZYCJA
             ============================================ -->
        
        <h2 style="font-size: 32px; margin-bottom: 30px; color: #2c3e50;">Zdjęcia realizacji</h2>
        
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
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                    <path d="M15 6l-6 6 6 6V6z"/>
                                    <path d="M9 6l6 6-6 6V6z"/>
                                </svg>
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
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="11" y1="8" x2="11" y2="14"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </div>
            </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</section>

<!-- LIGHTBOX -->
<?php if (!empty($gallery)): ?>
<div id="lightbox" class="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
    <button class="lightbox-prev" onclick="changeImage(-1)">‹</button>
    <button class="lightbox-next" onclick="changeImage(1)">›</button>
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
<section style="padding: 80px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container" style="text-align: center;">
        <h2 style="color: white; font-size: 42px; margin-bottom: 16px;">Masz podobny temat?</h2>
        <p style="color: rgba(255,255,255,0.9); font-size: 20px; margin-bottom: 32px;">
            Wypełnij formularz i policz orientacyjny koszt w kalkulatorze
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="/kontakt.php" style="display: inline-block; background: white; color: #667eea; padding: 16px 40px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: transform 0.2s;">
                Przejdź do formularza
            </a>
            <a href="/cennik.php" style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 16px 40px; border-radius: 8px; font-weight: 700; text-decoration: none; border: 2px solid white; transition: transform 0.2s;">
                Policz w kalkulatorze
            </a>
        </div>
    </div>
</section>

<script src="/assets/js/before-after-slider.js"></script>

<?php include 'includes/footer.php'; ?>