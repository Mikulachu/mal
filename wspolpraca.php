<?php
/**
 * WSPOLPRACA.PHP - Współpraca medialna
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ustawienia
$settings = getSettings();
$companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
$companyPhone = $settings['company_phone'] ?? '+48 784 607 452';

$pageTitle = 'Współpraca medialna';
?>
<?php include 'includes/header.php'; ?>

<style>
/* Współpraca - style */
.hero-cooperation {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    padding: 120px 0 100px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-cooperation::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-cooperation .container {
    position: relative;
    z-index: 1;
}

.hero-cooperation__title {
    font-size: 56px;
    font-weight: 800;
    margin-bottom: 30px;
    line-height: 1.1;
    text-shadow: 0 2px 20px rgba(0,0,0,0.2);
    letter-spacing: -0.5px;
}

.hero-cooperation__lead {
    font-size: 22px;
    line-height: 1.7;
    max-width: 850px;
    margin: 0 auto;
    font-weight: 400;
    opacity: 0.98;
    text-shadow: 0 1px 10px rgba(0,0,0,0.15);
}

.cooperation-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.cooperation-content {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 60px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.cooperation-content h2 {
    font-size: 36px;
    margin-bottom: 30px;
    color: var(--text-primary);
    font-weight: 700;
}

.cooperation-content h3 {
    font-size: 28px;
    margin: 50px 0 20px;
    color: var(--text-primary);
    font-weight: 700;
}

.cooperation-list {
    list-style: none;
    padding: 0;
    margin: 30px 0;
}

.cooperation-list li {
    padding: 20px 0 20px 50px;
    position: relative;
    font-size: 18px;
    line-height: 1.7;
    border-bottom: 1px solid #f0f0f0;
}

.cooperation-list li:last-child {
    border-bottom: none;
}

.cooperation-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    top: 20px;
    color: #e67e22;
    font-weight: 700;
    font-size: 28px;
    width: 35px;
    height: 35px;
    background: rgba(230, 126, 34, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.about-box {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    padding: 35px;
    border-radius: 12px;
    border-left: 5px solid #e67e22;
    margin: 40px 0;
    font-size: 18px;
    line-height: 1.8;
    box-shadow: 0 2px 10px rgba(230, 126, 34, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    margin: 40px 0;
}

.stat-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 40px 30px;
    border-radius: 16px;
    text-align: center;
    border: 2px solid #e0e0e0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.stat-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e67e22, #d35400);
    transform: scaleX(0);
    transition: transform 0.4s;
}

.stat-box:hover {
    border-color: #e67e22;
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(230, 126, 34, 0.2);
}

.stat-box:hover::before {
    transform: scaleX(1);
}

.stat-box__number {
    font-size: 42px;
    font-weight: 800;
    background: linear-gradient(135deg, #e67e22, #d35400);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 12px;
    display: block;
}

.stat-box__label {
    font-size: 14px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.contact-box {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    padding: 60px 50px;
    border-radius: 20px;
    text-align: center;
    margin: 60px 0 0;
    box-shadow: 0 10px 40px rgba(230, 126, 34, 0.3);
    position: relative;
    overflow: hidden;
}

.contact-box::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.contact-box h3 {
    color: white;
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 32px;
    position: relative;
    z-index: 1;
}

.contact-box p {
    font-size: 18px;
    margin-bottom: 35px;
    opacity: 0.95;
    position: relative;
    z-index: 1;
}

.contact-methods {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.contact-method {
    background: rgba(255,255,255,0.15);
    padding: 25px 35px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.25);
    transition: all 0.3s;
}

.contact-method:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
    transform: translateY(-3px);
}

.contact-method strong {
    display: block;
    font-size: 13px;
    margin-bottom: 10px;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-method a {
    color: white;
    text-decoration: none;
    font-size: 19px;
    font-weight: 700;
}

.contact-method a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .hero-cooperation {
        padding: 80px 0 60px;
    }
    
    .hero-cooperation__title {
        font-size: 38px;
    }
    
    .hero-cooperation__lead {
        font-size: 18px;
    }
    
    .cooperation-content {
        padding: 40px 25px;
    }
    
    .cooperation-content h2 {
        font-size: 28px;
    }
    
    .cooperation-content h3 {
        font-size: 24px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-box {
        padding: 40px 25px;
    }
    
    .contact-box h3 {
        font-size: 26px;
    }
    
    .contact-methods {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<!-- ============================================
     HERO WSPÓŁPRACA
     ============================================ -->
<section class="hero-cooperation">
    <div class="container">
        <h1 class="hero-cooperation__title">Współpraca medialna</h1>
        <p class="hero-cooperation__lead">
            Jeśli chcesz dotrzeć do ludzi, którzy budują, remontują i podejmują decyzje zakupowe — możemy zrobić to mądrze i z efektem.
        </p>
    </div>
</section>

<!-- ============================================
     TREŚĆ
     ============================================ -->
<section class="cooperation-section">
    <div class="container">
        <div class="cooperation-content">
            
            <!-- Co mogę zrobić -->
            <h2>Co mogę zrobić:</h2>
            <ul class="cooperation-list">
                <li><strong>Film na YouTube</strong> — długi format, tutorial, case study realizacji</li>
                <li><strong>Shorty / Reels</strong> — krótkie, treściwe, viralowe</li>
                <li><strong>Stories</strong> — relacje na żywo z budowy, za kulisami</li>
                <li><strong>Case study z realizacji</strong> — szczegółowy opis projektu przed/po</li>
            </ul>
            
            <!-- O mnie -->
            <h3>O mnie</h3>
            <div class="about-box">
                Jestem <strong>Wojtek Maltechnik</strong>. Pokazuję budowlankę po ludzku i konkretnie. Moja społeczność to osoby budujące i remontujące, a nie przypadkowe zasięgi.
            </div>
            
            <!-- Zasięgi -->
            <h3>Zasięgi</h3>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-box__number">12K+</span>
                    <span class="stat-box__label">Subskrybenci YouTube</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box__number">50K+</span>
                    <span class="stat-box__label">Średnie wyświetlenia</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box__number">8K+</span>
                    <span class="stat-box__label">TikTok / Instagram</span>
                </div>
            </div>
            
            <!-- Kontakt -->
            <div class="contact-box">
                <h3>Skontaktuj się ze mną</h3>
                <p>Omówimy szczegóły i ustalimy formę współpracy</p>
                <div class="contact-methods">
                    <div class="contact-method">
                        <strong>E-mail:</strong>
                        <a href="mailto:<?php echo h($companyEmail); ?>"><?php echo h($companyEmail); ?></a>
                    </div>
                    <div class="contact-method">
                        <strong>Telefon:</strong>
                        <a href="tel:<?php echo h($companyPhone); ?>"><?php echo h($companyPhone); ?></a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
