<?php
/**
 * OFERTA.PHP - Strona oferty
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Nasza oferta';
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style dla strony oferty -->
<link rel="stylesheet" href="/assets/css/oferta.css">

<!-- ============================================
     HERO OFERTA
     ============================================ -->
<section class="hero-offer">
    <div class="container">
        <div class="hero-offer__content">
            <h1 class="hero-offer__title">Nasze usługi</h1>
            <p class="hero-offer__subtitle">
                Od elewacji po kompleksowe remonty – każdy projekt wykonujemy z myślą o Twoim spokoju
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     NAWIGACJA WEWNĘTRZNA
     ============================================ -->
<nav class="offer-nav">
    <div class="container">
        <ul class="offer-nav__list">
            <li><a href="#elewacje">Elewacje</a></li>
            <li><a href="#wnetrza">Wnętrza</a></li>
            <li><a href="#remonty">Remonty kompleksowe</a></li>
            <li><a href="#instytucje">Dla firm</a></li>
            <li><a href="#konsultacje">Konsultacje</a></li>
            <li><a href="#premium">Premium</a></li>
        </ul>
    </div>
</nav>

<!-- ============================================
     ELEWACJE BUDYNKÓW
     ============================================ -->
<section id="elewacje" class="section offer-section">
    <div class="container">
        <div class="offer-section__header">
            <div class="offer-section__text">
                <span class="offer-section__badge">Najpopularniejsze</span>
                <h2 class="offer-section__title">Elewacje budynków</h2>
                <p class="offer-section__intro">
                    Twój dom to wizytówka. Zadbamy o to, by wyglądał perfekcyjnie – od przygotowania powierzchni, przez dobór odpowiednich materiałów, po precyzyjne wykonanie.
                </p>
            </div>
            <div class="offer-section__image">
                <img src="/assets/img/elewacja-showcase.jpg" alt="Profesjonalna elewacja budynku" loading="lazy">
            </div>
        </div>
        
        <div class="services-detailed">
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Malowanie elewacji</h3>
                <p class="service-detailed__desc">
                    Profesjonalne malowanie fasad farbami premium. Przygotowanie podłoża, gruntowanie, dwukrotne malowanie. Trwałość koloru i ochrona na lata.
                </p>
                <ul class="service-detailed__features">
                    <li>Farby akrylowe i silikonowe najwyższej jakości</li>
                    <li>Przygotowanie powierzchni (mycie, szlifowanie)</li>
                    <li>Gruntowanie głębokownikające</li>
                    <li>Dwukrotne malowanie w wybranym kolorze</li>
                    <li>Gwarancja 5 lat na wykonanie</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">35 zł/m²</span>
                </div>
            </div>
            
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="3" x2="9" y2="21"/>
                        <line x1="15" y1="3" x2="15" y2="21"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Tynki cienkowarstwowe</h3>
                <p class="service-detailed__desc">
                    Nakładanie tynków dekoracyjnych – baranek, kornik, mozaikowych. Różne faktury, bogata paleta kolorów, trwałość i odporność na warunki atmosferyczne.
                </p>
                <ul class="service-detailed__features">
                    <li>Tynki akrylowe, silikatowe, silikonowe</li>
                    <li>Różne faktury (baranek 1.5-2mm, kornik 2-3mm)</li>
                    <li>Gruntowanie i przygotowanie podłoża</li>
                    <li>Profesjonalne narzędzia i techniki</li>
                    <li>Zabezpieczenie otoczenia przed zabrudzeniem</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">45 zł/m²</span>
                </div>
            </div>
            
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Ocieplenie budynku</h3>
                <p class="service-detailed__desc">
                    Kompleksowe ocieplenie styropianem lub wełną mineralną. Montaż, szpachlowanie, gruntowanie, wykończenie tynkiem. Oszczędność energii i komfort termiczny.
                </p>
                <ul class="service-detailed__features">
                    <li>Styropian grafitowy lub wełna mineralna</li>
                    <li>Zaprawa klejąca i łączniki mechaniczne</li>
                    <li>Siatka zbrojąca i szpachlowanie</li>
                    <li>Gruntowanie i wykończenie tynkiem</li>
                    <li>Izolacja przeciwwilgociowa</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">120 zł/m²</span>
                </div>
            </div>
            
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <polyline points="2 17 12 22 22 17"/>
                        <polyline points="2 12 12 17 22 12"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Remonty elewacji historycznych</h3>
                <p class="service-detailed__desc">
                    Specjalistyczne usługi dla budynków zabytkowych. Szacunek dla oryginalnych materiałów, zachowanie charakteru, współpraca z konserwatorem zabytków.
                </p>
                <ul class="service-detailed__features">
                    <li>Konserwacja elewacji zabytkowych</li>
                    <li>Tynki wapienne i cementowo-wapienne</li>
                    <li>Farby mineralne i silikatowe</li>
                    <li>Współpraca z konserwatorem</li>
                    <li>Dokumentacja fotograficzna prac</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Wycena indywidualna</span>
                </div>
            </div>
        </div>
        
        <div class="offer-section__cta">
            <a href="/kontakt.php?usluaga=elewacje" class="btn btn--primary btn--large">
                Zapytaj o elewację
            </a>
            <a href="/realizacje.php?kategoria=elewacje" class="btn btn--secondary btn--large">
                Zobacz realizacje
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     WYKOŃCZENIA WNĘTRZ
     ============================================ -->
<section id="wnetrza" class="section section--alt offer-section">
    <div class="container">
        <div class="offer-section__header">
            <div class="offer-section__text">
                <h2 class="offer-section__title">Wykończenia wnętrz</h2>
                <p class="offer-section__intro">
                    Twoje wnętrze zasługuje na perfekcję. Gładzie premium, malowanie precyzyjne jak na wystawach, tynki dekoracyjne z charakterem. Wszystko wykonane czysto i z dbałością o detale.
                </p>
            </div>
            <div class="offer-section__image">
                <img src="/assets/img/wnetrza-showcase.jpg" alt="Wykończone wnętrze premium" loading="lazy">
            </div>
        </div>
        
        <div class="services-detailed">
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Gładzie gipsowe premium (Q4)</h3>
                <p class="service-detailed__desc">
                    Najwyższa klasa wykończenia – Q4. Idealna powierzchnia pod malowanie, bez widocznych nierówności. Perfekcja na ścianach i sufitach.
                </p>
                <ul class="service-detailed__features">
                    <li>Gładź Q4 – najwyższy standard</li>
                    <li>Szlifowanie i gruntowanie</li>
                    <li>Naprawa pęknięć i ubytków</li>
                    <li>Idealna powierzchnia pod malowanie</li>
                    <li>Sprzątanie na bieżąco</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">45 zł/m²</span>
                </div>
            </div>
            
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 19l7-7 3 3-7 7-3-3z"/>
                        <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>
                        <path d="M2 2l7.586 7.586"/>
                        <circle cx="11" cy="11" r="2"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Malowanie ścian i sufitów</h3>
                <p class="service-detailed__desc">
                    Profesjonalne malowanie farbami premium. Bogata paleta kolorów, efekty matowe i satynowe, dwukrotne krycie. Bez smug, bez przebarwień.
                </p>
                <ul class="service-detailed__features">
                    <li>Farby Tikkurila, Beckers, Dulux</li>
                    <li>Gruntowanie powierzchni</li>
                    <li>Dwukrotne malowanie</li>
                    <li>Precyzyjne maskowanie</li>
                    <li>Dobór kolorów z katalogu NCS/RAL</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">25 zł/m²</span>
                </div>
            </div>
            
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Tynki dekoracyjne</h3>
                <p class="service-detailed__desc">
                    Weneckie, betonowe, strukturalne. Nadaj swoim wnętrzom charakter i elegancję. Różne faktury, efekty i kolory – dopasowane do Twojego stylu.
                </p>
                <ul class="service-detailed__features">
                    <li>Tynk wenecki, betony architektoniczne</li>
                    <li>Tynki strukturalne i mineralne</li>
                    <li>Efekty metaliczne i perłowe</li>
                    <li>Zabezpieczenie woskiem lub lakierem</li>
                    <li>Indywidualne konsultacje kolorystyczne</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">150 zł/m²</span>
                </div>
            </div>
            
            <div class="service-detailed">
                <div class="service-detailed__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <h3 class="service-detailed__title">Tapetowanie</h3>
                <p class="service-detailed__desc">
                    Profesjonalne tapetowanie – od tapet papierowych po winylowe i flizelinowe. Precyzyjne dopasowanie wzorów, trwałe klejenie, perfekcyjne wykończenie.
                </p>
                <ul class="service-detailed__features">
                    <li>Wszystkie rodzaje tapet</li>
                    <li>Przygotowanie i gruntowanie podłoża</li>
                    <li>Precyzyjne dopasowanie wzorów</li>
                    <li>Usuwanie starych tapet</li>
                    <li>Doradztwo w doborze</li>
                </ul>
                <div class="service-detailed__price">
                    <span class="price-label">Cena od:</span>
                    <span class="price-value">30 zł/m²</span>
                </div>
            </div>
        </div>
        
        <div class="offer-section__cta">
            <a href="/kontakt.php?usluga=wnetrza" class="btn btn--primary btn--large">
                Zapytaj o wykończenie wnętrz
            </a>
            <a href="/realizacje.php?kategoria=wnetrza" class="btn btn--secondary btn--large">
                Zobacz realizacje
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     REMONTY KOMPLEKSOWE
     ============================================ -->
<section id="remonty" class="section offer-section">
    <div class="container">
        <div class="offer-section__header">
            <div class="offer-section__text">
                <h2 class="offer-section__title">Remonty kompleksowe</h2>
                <p class="offer-section__intro">
                    Nie chcesz się martwić o koordynację różnych ekip? Zrobimy wszystko za Ciebie. Od projektu po finalne sprzątanie – kompleksowa obsługa od A do Z.
                </p>
            </div>
            <div class="offer-section__image">
                <img src="/assets/img/remont-showcase.jpg" alt="Remont kompleksowy" loading="lazy">
            </div>
        </div>
        
        <div class="complex-offer">
            <div class="complex-offer__intro">
                <h3>Co obejmuje remont kompleksowy?</h3>
                <p>
                    To pełna koordynacja wszystkich branż – od elektryka i hydraulika, przez glazurnika i stolarza, po nas – malarzy. Ty dostajesz gotowe wnętrze. My zajmujemy się resztą.
                </p>
            </div>
            
            <div class="complex-phases">
                <div class="phase-card">
                    <div class="phase-card__number">Faza 1</div>
                    <h4 class="phase-card__title">Projekt i planowanie</h4>
                    <ul class="phase-card__list">
                        <li>Pomiar i analiza przestrzeni</li>
                        <li>Projekt aranżacji (opcjonalnie)</li>
                        <li>Kosztorys szczegółowy</li>
                        <li>Harmonogram prac</li>
                        <li>Dobór materiałów</li>
                    </ul>
                </div>
                
                <div class="phase-card">
                    <div class="phase-card__number">Faza 2</div>
                    <h4 class="phase-card__title">Prace rozbiórkowe</h4>
                    <ul class="phase-card__list">
                        <li>Demontaż starych instalacji</li>
                        <li>Usunięcie starych wykończeń</li>
                        <li>Wywóz gruzu i śmieci</li>
                        <li>Przygotowanie pomieszczeń</li>
                    </ul>
                </div>
                
                <div class="phase-card">
                    <div class="phase-card__number">Faza 3</div>
                    <h4 class="phase-card__title">Instalacje</h4>
                    <ul class="phase-card__list">
                        <li>Elektryka (przewody, gniazdka, oświetlenie)</li>
                        <li>Hydraulika (rury, odpływy, armatura)</li>
                        <li>Ogrzewanie (grzejniki, instalacje)</li>
                        <li>Wentylacja i klimatyzacja</li>
                    </ul>
                </div>
                
                <div class="phase-card">
                    <div class="phase-card__number">Faza 4</div>
                    <h4 class="phase-card__title">Wykończenia</h4>
                    <ul class="phase-card__list">
                        <li>Wylewki i podkłady</li>
                        <li>Tynki i gładzie</li>
                        <li>Płytki ceramiczne</li>
                        <li>Panele / parkiet</li>
                        <li>Malowanie / tapetowanie</li>
                    </ul>
                </div>
                
                <div class="phase-card">
                    <div class="phase-card__number">Faza 5</div>
                    <h4 class="phase-card__title">Finalizacja</h4>
                    <ul class="phase-card__list">
                        <li>Montaż drzwi i ościeżnic</li>
                        <li>Montaż mebli (opcjonalnie)</li>
                        <li>Podłączenie sprzętów</li>
                        <li>Sprzątanie końcowe</li>
                        <li>Odbiór techniczny</li>
                    </ul>
                </div>
            </div>
            
            <div class="complex-benefits">
                <h3>Dlaczego warto?</h3>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <div>
                            <h5>Jeden kontakt</h5>
                            <p>Dzwonisz do nas, nie do 10 różnych firm</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <div>
                            <h5>Terminowość</h5>
                            <p>Harmonogram ustalony na starcie, realizacja bez opóźnień</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <polyline points="2 17 12 22 22 17"/>
                        </svg>
                        <div>
                            <h5>Koordynacja branż</h5>
                            <p>My ustalamy kolejność prac, Ty nie musisz pilnować ekip</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <div>
                            <h5>Gwarancja jakości</h5>
                            <p>Sprawdzamy każdą branżę, odpowiadamy za efekt końcowy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="offer-section__cta">
            <a href="/kontakt.php?usluga=remont-kompleksowy" class="btn btn--primary btn--large">
                Zapytaj o remont kompleksowy
            </a>
            <a href="/cennik.php" class="btn btn--secondary btn--large">
                Oblicz koszt
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     DLA FIRM I INSTYTUCJI
     ============================================ -->
<section id="instytucje" class="section section--alt offer-section">
    <div class="container">
        <div class="offer-section__header">
            <div class="offer-section__text">
                <h2 class="offer-section__title">Dla firm i instytucji</h2>
                <p class="offer-section__intro">
                    Biura, hotele, restauracje, szkoły – każde wnętrze komercyjne wymaga precyzji, terminowości i minimalnej ingerencji w codzienne funkcjonowanie. Specjalizujemy się w takich projektach.
                </p>
            </div>
        </div>
        
        <div class="b2b-features">
            <div class="b2b-feature">
                <h4>Praca poza godzinami</h4>
                <p>Możemy pracować wieczorami, nocami lub w weekendy, aby nie zakłócać działalności Twojej firmy.</p>
            </div>
            <div class="b2b-feature">
                <h4>Faktury VAT</h4>
                <p>Pełna dokumentacja, faktury VAT, możliwość rozliczenia kosztów jako wydatków firmowych.</p>
            </div>
            <div class="b2b-feature">
                <h4>Umowy długoterminowe</h4>
                <p>Obsługa stała – regularne odświeżanie wnętrz, przeglądy, konserwacje.</p>
            </div>
            <div class="b2b-feature">
                <h4>Indywidualne warunki</h4>
                <p>Rabaty dla stałych klientów, elastyczne płatności, wyceny na zapytanie.</p>
            </div>
        </div>
        
        <div class="b2b-clients">
            <h3>Ufają nam</h3>
            <div class="clients-logos">
                <div class="client-logo">Hotel Example</div>
                <div class="client-logo">Restauracja ABC</div>
                <div class="client-logo">Biuro XYZ</div>
                <div class="client-logo">Szkoła DEF</div>
            </div>
        </div>
        
        <div class="offer-section__cta">
            <a href="/kontakt.php?usluga=b2b" class="btn btn--primary btn--large">
                Zapytaj o ofertę B2B
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     KONSULTACJE ONLINE
     ============================================ -->
<section id="konsultacje" class="section offer-section">
    <div class="container">
        <div class="consultation-box">
            <div class="consultation-box__content">
                <h2 class="consultation-box__title">Konsultacje online</h2>
                <p class="consultation-box__desc">
                    Nie wiesz, jak podejść do remontu? Potrzebujesz porady eksperta? Umów bezpłatną konsultację online – przez telefon lub wideorozmowę.
                </p>
                <div class="consultation-benefits">
                    <div class="consultation-benefit">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Bezpłatna konsultacja (30 min)</span>
                    </div>
                    <div class="consultation-benefit">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Doradztwo techniczne i kolorystyczne</span>
                    </div>
                    <div class="consultation-benefit">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Wstępna wycena na podstawie zdjęć</span>
                    </div>
                    <div class="consultation-benefit">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Rekomendacje materiałów i technologii</span>
                    </div>
                </div>
                <a href="/kontakt.php?typ=konsultacja" class="btn btn--primary btn--large">
                    Umów konsultację
                </a>
            </div>
            <div class="consultation-box__image">
                <img src="/assets/img/konsultacja-online.jpg" alt="Konsultacja online" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     PREMIUM: OGARNĘ CI CAŁOŚĆ
     ============================================ -->
<section id="premium" class="section section--premium">
    <div class="container">
        <div class="premium-package">
            <div class="premium-package__badge">Premium</div>
            <h2 class="premium-package__title">Ogarnę Ci całość</h2>
            <p class="premium-package__subtitle">
                Pakiet premium dla tych, którzy cenią swój czas i spokój
            </p>
            
            <div class="premium-package__content">
                <div class="premium-package__text">
                    <p class="premium-package__intro">
                        Nie chcesz się w ogóle zajmować remontem? Rozumiemy. Nasz pakiet premium to obsługa "pod klucz" – od pomysłu po odbiór. Ty tylko cieszysz się efektem.
                    </p>
                    
                    <h3>Co dostajesz?</h3>
                    <ul class="premium-list">
                        <li>
                            <strong>Dedykowany opiekun projektu</strong>
                            <p>Jedna osoba kontaktowa, dostępna dla Ciebie przez cały czas trwania remontu</p>
                        </li>
                        <li>
                            <strong>Projekt i wizualizacje</strong>
                            <p>Aranżacja wnętrza, dobór kolorów i materiałów, wizualizacje 3D</p>
                        </li>
                        <li>
                            <strong>Załatwienie formalności</strong>
                            <p>Zgłoszenia, pozwolenia, dokumentacja – my się tym zajmiemy</p>
                        </li>
                        <li>
                            <strong>Zakup materiałów</strong>
                            <p>Kupujemy wszystko, co potrzebne (z Twoją akceptacją)</p>
                        </li>
                        <li>
                            <strong>Koordynacja wszystkich branż</strong>
                            <p>Elektryk, hydraulik, glazurnik – my pilnujemy harmonogramu</p>
                        </li>
                        <li>
                            <strong>Regularne raporty</strong>
                            <p>Zdjęcia, informacje o postępach, przejrzyste rozliczenia</p>
                        </li>
                        <li>
                            <strong>Sprzątanie profesjonalne</strong>
                            <p>Po zakończeniu prac firma sprzątająca doprowadza wnętrze do perfekcji</p>
                        </li>
                        <li>
                            <strong>Gwarancja premium</strong>
                            <p>Rozszerzona gwarancja + serwis pogwarancyjny przez 2 lata</p>
                        </li>
                    </ul>
                </div>
                
                <div class="premium-package__pricing">
                    <div class="premium-price-card">
                        <h4>Dla kogo?</h4>
                        <p>
                            Dla osób, które nie mają czasu lub ochoty zajmować się remontem. Dla tych, którzy chcą efektu premium bez stresu.
                        </p>
                        <div class="premium-price">
                            <span class="price-note">Koszt pakietu premium:</span>
                            <span class="price-value">+20% do wartości remontu</span>
                        </div>
                        <p class="premium-note">
                            Minimalny zakres: remont od 30 000 zł
                        </p>
                        <a href="/kontakt.php?usluga=premium" class="btn btn--primary btn--large">
                            Zapytaj o pakiet premium
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     CTA KOŃCOWE
     ============================================ -->
<section class="section cta-final">
    <div class="container">
        <div class="cta-final__content">
            <h2>Gotowy na spokojny remont?</h2>
            <p>Skontaktuj się z nami – pierwsza konsultacja to nic nie kosztuje</p>
            <div class="cta-final__buttons">
                <a href="/kontakt.php" class="btn btn--primary btn--large">Bezpłatna wycena</a>
                <a href="/cennik.php" class="btn btn--secondary btn--large">Kalkulator cen</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
