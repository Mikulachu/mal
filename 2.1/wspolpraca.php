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
/* Współpraca - Brand Colors */
.hero-cooperation {
    background: linear-gradient(135deg, #2B59A6 0%, #244C8F 100%);
    color: #FFFFFF;
    padding: 5rem 0 4rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

@media (min-width: 1024px) {
    .hero-cooperation {
        padding: 7.5rem 0 6.25rem;
    }
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
    font-size: 2.25rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    line-height: 1.1;
    text-shadow: 0 2px 20px rgba(0,0,0,0.2);
    letter-spacing: -0.5px;
}

@media (min-width: 768px) {
    .hero-cooperation__title {
        font-size: 3rem;
    }
}

@media (min-width: 1024px) {
    .hero-cooperation__title {
        font-size: 3.5rem;
    }
}

.hero-cooperation__lead {
    font-size: 1.125rem;
    line-height: 1.7;
    max-width: 850px;
    margin: 0 auto;
    font-weight: 400;
    opacity: 0.98;
    text-shadow: 0 1px 10px rgba(0,0,0,0.15);
}

@media (min-width: 768px) {
    .hero-cooperation__lead {
        font-size: 1.375rem;
    }
}

.cooperation-section {
    padding: 4rem 0;
    background: #F7F8FA;
}

@media (min-width: 768px) {
    .cooperation-section {
        padding: 5rem 0;
    }
}

.cooperation-content {
    max-width: 900px;
    margin: 0 auto;
    background: #FFFFFF;
    padding: 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    width: 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .cooperation-content {
        padding: 3.75rem;
    }
}

.cooperation-content h2 {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    color: #111827;
    font-weight: 700;
}

@media (min-width: 768px) {
    .cooperation-content h2 {
        font-size: 2.25rem;
        margin-bottom: 1.875rem;
    }
}

.cooperation-content h3 {
    font-size: 1.5rem;
    margin: 2.5rem 0 1.25rem;
    color: #111827;
    font-weight: 700;
}

@media (min-width: 768px) {
    .cooperation-content h3 {
        font-size: 1.75rem;
        margin: 3.125rem 0 1.25rem;
    }
}

.cooperation-list {
    list-style: none;
    padding: 0;
    margin: 1.875rem 0;
}

.cooperation-list li {
    padding: 1.25rem 0 1.25rem 3.5rem;
    position: relative;
    font-size: 1rem;
    line-height: 1.7;
    border-bottom: 1px solid #E5E7EB;
}

@media (min-width: 768px) {
    .cooperation-list li {
        font-size: 1.125rem;
    }
}

.cooperation-list li:last-child {
    border-bottom: none;
}

.cooperation-list li {
    padding: 1.25rem 0;
    position: relative;
    font-size: 1rem;
    line-height: 1.7;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

@media (min-width: 768px) {
    .cooperation-list li {
        font-size: 1.125rem;
    }
}

.cooperation-list li .list-icon {
    flex-shrink: 0;
    width: 2.25rem;
    height: 2.25rem;
    background: #E9F0FF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2B59A6;
    font-size: 1.125rem;
}

.cooperation-list li .list-content {
    flex: 1;
    padding-top: 0.125rem;
}

.about-box {
    background: linear-gradient(135deg, #E9F0FF 0%, #DBEAFE 100%);
    padding: 2rem;
    border-radius: 0.75rem;
    border-left: 5px solid #2B59A6;
    margin: 2.5rem 0;
    font-size: 1rem;
    line-height: 1.8;
    box-shadow: 0 2px 10px rgba(43, 89, 166, 0.1);
}

@media (min-width: 768px) {
    .about-box {
        padding: 2.25rem;
        font-size: 1.125rem;
    }
}

.about-box strong {
    color: #2B59A6;
    font-weight: 700;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin: 2.5rem 0;
}

@media (min-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.stat-box {
    background: linear-gradient(135deg, #FFFFFF 0%, #F7F8FA 100%);
    padding: 2.5rem 1.875rem;
    border-radius: 1rem;
    text-align: center;
    border: 2px solid #E5E7EB;
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
    background: linear-gradient(90deg, #2B59A6, #244C8F);
    transform: scaleX(0);
    transition: transform 0.4s;
}

.stat-box:hover {
    border-color: #2B59A6;
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(43, 89, 166, 0.2);
}

.stat-box:hover::before {
    transform: scaleX(1);
}

.stat-box__number {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #2B59A6, #244C8F);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.75rem;
    display: block;
}

@media (min-width: 768px) {
    .stat-box__number {
        font-size: 2.625rem;
    }
}

.stat-box__label {
    font-size: 0.875rem;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.contact-box {
    background: linear-gradient(135deg, #2B59A6 0%, #244C8F 100%);
    color: #FFFFFF;
    padding: 2.5rem;
    border-radius: 1.25rem;
    text-align: center;
    margin: 3.75rem 0 0;
    box-shadow: 0 10px 40px rgba(43, 89, 166, 0.3);
    position: relative;
    overflow: hidden;
}

@media (min-width: 768px) {
    .contact-box {
        padding: 3.75rem 3.125rem;
    }
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
    color: #FFFFFF;
    margin-top: 0;
    margin-bottom: 1.25rem;
    font-size: 1.75rem;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .contact-box h3 {
        font-size: 2rem;
    }
}

.contact-box p {
    font-size: 1rem;
    margin-bottom: 2rem;
    opacity: 0.95;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .contact-box p {
        font-size: 1.125rem;
        margin-bottom: 2.25rem;
    }
}

.contact-methods {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

@media (min-width: 640px) {
    .contact-methods {
        flex-direction: row;
        gap: 1.875rem;
    }
}

.contact-method {
    background: rgba(255,255,255,0.15);
    padding: 1.5rem 2rem;
    border-radius: 0.75rem;
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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    margin-bottom: 0.625rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-method strong i {
    font-size: 1rem;
}

.contact-method a {
    color: #FFFFFF;
    text-decoration: none;
    font-size: 1.125rem;
    font-weight: 700;
}

@media (min-width: 768px) {
    .contact-method a {
        font-size: 1.1875rem;
    }
}

.contact-method a:hover {
    text-decoration: underline;
}

/* Mobile */
@media (max-width: 767px) {
    .hero-cooperation {
        padding: 5rem 0 3.75rem;
    }
    
    .hero-cooperation__title {
        font-size: 2.375rem;
    }
    
    .hero-cooperation__lead {
        font-size: 1.125rem;
    }
    
    .cooperation-content {
        padding: 2.5rem 1.5rem;
    }
    
    .cooperation-content h2 {
        font-size: 1.75rem;
    }
    
    .cooperation-content h3 {
        font-size: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-box {
        padding: 2.5rem 1.5rem;
    }
    
    .contact-box h3 {
        font-size: 1.625rem;
    }
    
    .contact-methods {
        flex-direction: column;
        gap: 1rem;
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
                <li>
                    <span class="list-icon"><i class="bi bi-camera-video"></i></span>
                    <span class="list-content"><strong>Film na YouTube</strong> — długi format, tutorial, case study realizacji</span>
                </li>
                <li>
                    <span class="list-icon"><i class="bi bi-play-circle"></i></span>
                    <span class="list-content"><strong>Shorty / Reels</strong> — krótkie, treściwe, viralowe</span>
                </li>
                <li>
                    <span class="list-icon"><i class="bi bi-camera"></i></span>
                    <span class="list-content"><strong>Stories</strong> — relacje na żywo z budowy, za kulisami</span>
                </li>
                <li>
                    <span class="list-icon"><i class="bi bi-file-text"></i></span>
                    <span class="list-content"><strong>Case study z realizacji</strong> — szczegółowy opis projektu przed/po</span>
                </li>
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
                        <strong><i class="bi bi-envelope"></i> E-mail:</strong>
                        <a href="mailto:<?php echo h($companyEmail); ?>"><?php echo h($companyEmail); ?></a>
                    </div>
                    <div class="contact-method">
                        <strong><i class="bi bi-telephone"></i> Telefon:</strong>
                        <a href="tel:<?php echo h($companyPhone); ?>"><?php echo h($companyPhone); ?></a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>