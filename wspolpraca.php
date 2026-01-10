<?php
/**
 * WSPOLPRACA.PHP - Współpraca medialna
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ustawienia
$settings = getSettings();
$companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';

$pageTitle = 'Współpraca';
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/wspolpraca.css">

<!-- ============================================
     HERO WSPÓŁPRACA
     ============================================ -->
<section class="hero-cooperation">
    <div class="container">
        <div class="hero-cooperation__content">
            <h1 class="hero-cooperation__title">Współpracujmy razem</h1>
            <p class="hero-cooperation__subtitle">
                Szukasz partnera do współpracy medialnej? Sprawdź, co możemy razem osiągnąć.
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     WSPÓŁPRACA MEDIALNA
     ============================================ -->
<section class="section">
    <div class="container">
        
        <!-- Intro -->
        <div class="media-intro">
            <h2>Współpraca z markami i mediami</h2>
            <p>
                Działamy w branży remontowej od ponad 12 lat. Realizujemy projekty premium, szkolimy, dzielimy się wiedzą. Jeśli szukasz eksperta do materiału, case study lub współpracy brandowej – porozmawiajmy.
            </p>
        </div>
        
        <!-- Co oferujemy -->
        <div class="media-offer">
            <h3>Co możemy dla Ciebie zrobić?</h3>
            
            <div class="offer-grid">
                <div class="offer-card">
                    <div class="offer-card__icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="15" rx="2" ry="2"/>
                            <polyline points="17 2 12 7 7 2"/>
                        </svg>
                    </div>
                    <h4>Artykuły eksperckie</h4>
                    <p>Przygotujemy merytoryczne artykuły o trendach w wykończeniach, technikach malarskich, doborze materiałów.</p>
                </div>
                
                <div class="offer-card">
                    <div class="offer-card__icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7"/>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                        </svg>
                    </div>
                    <h4>Materiały wideo</h4>
                    <p>Możemy nagrać tutoriale, time-lapse'y z realizacji, porady praktyczne – dostosowane do Twojego formatu.</p>
                </div>
                
                <div class="offer-card">
                    <div class="offer-card__icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h4>Wywiady i podcasty</h4>
                    <p>Chętnie opowiemy o pracy w branży, wyzwaniach, trendach – format dostosujemy do Twoich potrzeb.</p>
                </div>
                
                <div class="offer-card">
                    <div class="offer-card__icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                            <line x1="6" y1="6" x2="6.01" y2="6"/>
                            <line x1="6" y1="18" x2="6.01" y2="18"/>
                        </svg>
                    </div>
                    <h4>Case studies</h4>
                    <p>Mamy ponad 100 zrealizowanych projektów – możemy przygotować szczegółowe case study z before/after.</p>
                </div>
                
                <div class="offer-card">
                    <div class="offer-card__icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <polyline points="2 17 12 22 22 17"/>
                            <polyline points="2 12 12 17 22 12"/>
                        </svg>
                    </div>
                    <h4>Współpraca brandowa</h4>
                    <p>Jesteś producentem farb, narzędzi, materiałów? Możemy przetestować Twoje produkty w rzeczywistych projektach.</p>
                </div>
                
                <div class="offer-card">
                    <div class="offer-card__icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h4>Szkolenia i webinary</h4>
                    <p>Prowadzimy szkolenia dla branży – możemy przygotować webinar dla Twojej społeczności lub klientów.</p>
                </div>
            </div>
        </div>
        
        <!-- Dlaczego my -->
        <div class="media-why">
            <h3>Dlaczego warto z nami współpracować?</h3>
            <div class="why-list">
                <div class="why-item">
                    <span class="why-item__number">01</span>
                    <div class="why-item__content">
                        <h4>Doświadczenie</h4>
                        <p>12 lat w branży, ponad 100 projektów – wiemy, o czym mówimy.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="why-item__number">02</span>
                    <div class="why-item__content">
                        <h4>Autentyczność</h4>
                        <p>Nie udajemy ekspertów – jesteśmy nimi. Pracujemy na co dzień, nie tylko mówimy.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="why-item__number">03</span>
                    <div class="why-item__content">
                        <h4>Profesjonalizm</h4>
                        <p>Dotrzymujemy terminów, jesteśmy dostępni, dbamy o jakość treści.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="why-item__number">04</span>
                    <div class="why-item__content">
                        <h4>Zdjęcia i video</h4>
                        <p>Dokumentujemy projekty, mamy archiwum materiałów before/after.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CTA -->
        <div class="media-cta">
            <h3>Zainteresowany współpracą?</h3>
            <p>Napisz do nas – opowiemy więcej i ustalimy szczegóły</p>
            <a href="mailto:<?php echo h($companyEmail); ?>?subject=Współpraca%20medialna" class="btn btn--primary btn--large">
                📧 Napisz do nas
            </a>
        </div>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>