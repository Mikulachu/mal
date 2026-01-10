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
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <h3 class="value-card__title">Termin i odpowiedzialność</h3>
                <p class="value-card__desc">
                    Ustalamy realistyczne terminy i się ich trzymamy. Jeśli coś obiecujemy, to dotrzymujemy słowa. Bez "przyjdziemy jak będziemy".
                </p>
            </div>
            
            <div class="value-card">
                <div class="value-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <h3 class="value-card__title">Porządek i kultura pracy</h3>
                <p class="value-card__desc">
                    Po każdym dniu pracy sprzątamy. Zabezpieczamy meble i podłogi. Twój dom to nie "budowa jak budowa" – traktujemy go z szacunkiem.
                </p>
            </div>
            
            <div class="value-card">
                <div class="value-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                </div>
                <h3 class="value-card__title">Prosta komunikacja</h3>
                <p class="value-card__desc">
                    WhatsApp + zdjęcia/filmiki. Bez dzwonienia o pierdoły. Zawsze wiesz, co się dzieje, bez zbędnych telefonów.
                </p>
            </div>
            
            <div class="value-card">
                <div class="value-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
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
        
        <div class="clients-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px; max-width: 900px; margin: 0 auto;">
            
            <!-- Klienci prywatni -->
            <div class="client-box" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <div class="client-icon" style="font-size: 48px; margin-bottom: 20px; color: #e67e22;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <h3 style="font-size: 24px; margin-bottom: 16px; color: #2c3e50;">Właściciele domów i mieszkań</h3>
                <p style="color: #6c757d; font-size: 16px; line-height: 1.8;">
                    Którzy chcą świętego spokoju. Nie chcesz się martwić o remont, tylko cieszyć się efektem? To dla Ciebie. Bierzemy odpowiedzialność za cały proces – od wyceny po sprzątanie.
                </p>
            </div>
            
            <!-- Instytucje -->
            <div class="client-box" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <div class="client-icon" style="font-size: 48px; margin-bottom: 20px; color: #e67e22;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <h3 style="font-size: 24px; margin-bottom: 16px; color: #2c3e50;">Instytucje i firmy</h3>
                <p style="color: #6c757d; font-size: 16px; line-height: 1.8;">
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
                <div class="stat-item__number">12+</div>
                <div class="stat-item__label">Lat na rynku</div>
            </div>
            <div class="stat-item">
                <div class="stat-item__number">100+</div>
                <div class="stat-item__label">Zrealizowanych projektów</div>
            </div>
            <div class="stat-item">
                <div class="stat-item__number">98%</div>
                <div class="stat-item__label">Klientów zadowolonych</div>
            </div>
            <div class="stat-item">
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
                <a href="/kontakt.php" class="btn btn--primary btn--large">Przejdź do formularza</a>
                <a href="/realizacje.php" class="btn btn--secondary btn--large">Zobacz realizacje</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>