<?php
/**
 * O-NAS.PHP - Strona o firmie
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'O nas';
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/o-nas.css">

<!-- ============================================
     HERO O NAS
     ============================================ -->
<section class="hero-about">
    <div class="container">
        <div class="hero-about__content">
            <div class="hero-about__text">
                <h1 class="hero-about__title">O nas</h1>
                <p class="hero-about__intro">
                    Nazywam się Wojtek i jestem twarzą Maltechnik. Ale za efektem stoi zespół. Robimy roboty, które mają wyglądać dobrze nie tylko na zdjęciu — tylko normalnie, na żywo, przez lata.
                </p>
            </div>
            <div class="hero-about__image">
                <img src="/assets/img/o-nas-hero.jpg" alt="Wojtek - Maltechnik" loading="eager">
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     CO JEST DLA NAS WAŻNE
     ============================================ -->
<section class="section section--alt values-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Co jest dla nas ważne</h2>
        </div>
        
        <div class="values-grid">
            <div class="value-card">
                <div class="value-card__icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3 class="value-card__title">Termin i odpowiedzialność</h3>
                <p class="value-card__desc">
                    Ustalamy realistyczne terminy i się ich trzymamy. Jeśli coś obiecujemy, to dotrzymujemy słowa. Bez "przyjdziemy jak będziemy".
                </p>
            </div>
            
            <div class="value-card">
                <div class="value-card__icon">
                    <i class="bi bi-house-check"></i>
                </div>
                <h3 class="value-card__title">Porządek i kultura pracy</h3>
                <p class="value-card__desc">
                    Po każdym dniu pracy sprzątamy. Zabezpieczamy meble i podłogi. Twój dom to nie "budowa jak budowa" – traktujemy go z szacunkiem.
                </p>
            </div>
            
            <div class="value-card">
                <div class="value-card__icon">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <h3 class="value-card__title">Prosta komunikacja</h3>
                <p class="value-card__desc">
                    WhatsApp + zdjęcia/filmiki. Bez dzwonienia o pierdoły. Zawsze wiesz, co się dzieje, bez zbędnych telefonów.
                </p>
            </div>
            
            <div class="value-card">
                <div class="value-card__icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="value-card__title">Efekt bez poprawek</h3>
                <p class="value-card__desc">
                    Robimy raz, a dobrze. Używamy materiałów premium i nie idziemy na skróty. To ma służyć latami, nie miesiącami.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     DLA KOGO PRACUJEMY
     ============================================ -->
<section class="section clients-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Dla kogo pracujemy</h2>
        </div>
        
        <div class="clients-grid">
            
            <!-- Klienci prywatni -->
            <div class="client-box">
                <div class="client-icon">
                    <i class="bi bi-house-heart"></i>
                </div>
                <h3>Właściciele domów i mieszkań</h3>
                <p>
                    Którzy chcą świętego spokoju. Nie chcesz się martwić o remont, tylko cieszyć się efektem? To dla Ciebie. Bierzemy odpowiedzialność za cały proces – od wyceny po sprzątanie.
                </p>
            </div>
            
            <!-- Instytucje -->
            <div class="client-box">
                <div class="client-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h3>Instytucje i firmy</h3>
                <p>
                    Które potrzebują wykonawcy „do ogarnięcia tematu" kosztorysowo. Kosztorysy, faktury, harmonogramy, dokumentacja – wszystko zgodnie z wymogami. Robimy projekty dla szkół, urzędów, firm.
                </p>
            </div>
            
        </div>
    </div>
</section>

<!-- ============================================
     LICZBY
     ============================================ -->
<section class="section section--alt stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-item__icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-item__number">12+</div>
                <div class="stat-item__label">Lat na rynku</div>
            </div>
            <div class="stat-item">
                <div class="stat-item__icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-item__number">100+</div>
                <div class="stat-item__label">Zrealizowanych projektów</div>
            </div>
            <div class="stat-item">
                <div class="stat-item__icon">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <div class="stat-item__number">98%</div>
                <div class="stat-item__label">Klientów zadowolonych</div>
            </div>
            <div class="stat-item">
                <div class="stat-item__icon">
                    <i class="bi bi-shield-fill-check"></i>
                </div>
                <div class="stat-item__number">5 lat</div>
                <div class="stat-item__label">Gwarancji</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     CTA KOŃCOWE
     ============================================ -->
<section class="section cta-about">
    <div class="container">
        <div class="cta-about__content">
            <h2>Jeśli chcesz, żeby ktoś wziął odpowiedzialność za efekt</h2>
            <p>Wypełnij formularz i wybierz godzinę rozmowy</p>
            <div class="cta-about__buttons">
                <a href="/kontakt.php" class="btn btn--primary btn--large">
                    <i class="bi bi-envelope"></i> Przejdź do formularza
                </a>
                <a href="/realizacje.php" class="btn btn--secondary btn--large">
                    <i class="bi bi-images"></i> Zobacz realizacje
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Clients Grid - Brand Colors */
.clients-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
    max-width: 900px;
    margin: 0 auto;
}

@media (min-width: 768px) {
    .clients-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.client-box {
    background: #FFFFFF;
    padding: 2.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #E5E7EB;
    transition: all 300ms ease;
}

.client-box:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(43, 89, 166, 0.15);
    border-color: #2B59A6;
}

.client-icon {
    font-size: 3rem;
    margin-bottom: 1.25rem;
    color: #2B59A6;
}

.client-box h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #111827;
    font-weight: 700;
}

.client-box p {
    color: #6B7280;
    font-size: 1rem;
    line-height: 1.8;
    margin: 0;
}

@media (max-width: 767px) {
    .clients-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>