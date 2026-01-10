<?php
/**
 * INDEX.PHP - Strona główna
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ustawienia
$settings = getSettings();
$companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
$companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';

$pageTitle = 'Remont bez stresu - Zrobione raz, a dobrze';
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style dla strony głównej -->
<link rel="stylesheet" href="/assets/css/home.css">

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero">
    <div class="container">
        <div class="hero__content">
            <div class="hero__text">
                <h1 class="hero__title">
                    Remont / elewacja <span class="highlight">bez stresu</span>. Zrobione raz, a dobrze.
                </h1>
                <p class="hero__subtitle">
                    Nie sprzedajemy „robót budowlanych". Sprzedajemy spokój: termin, porządek i efekt, który nie wymaga poprawek. Działamy w Chojnicach i okolicy.
                </p>
                <div class="hero__cta">
                    <a href="/kontakt.php" class="btn btn--primary btn--large">
                        Wypełnij formularz i wybierz godzinę rozmowy
                    </a>
                    <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--secondary btn--large">
                        📞 Zadzwoń: <?php echo h($companyPhone); ?>
                    </a>
                </div>
            </div>
            <div class="hero__image">
                <img src="/assets/img/hero-house.jpg" alt="Dom po profesjonalnej elewacji" loading="eager">
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     PROBLEMY KLIENTA
     ============================================ -->
<section class="section problems">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Najczęstsze problemy klientów</h2>
        </div>
        
        <div class="problems__grid">
            <div class="problem-card">
                <div class="problem-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M2 12h20"/>
                    </svg>
                </div>
                <p class="problem-card__quote">
                    „Boję się, że wydam kupę pieniędzy i będzie źle zrobione."
                </p>
            </div>
            
            <div class="problem-card">
                <div class="problem-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <p class="problem-card__quote">
                    „Wykonawca przestanie przyjeżdżać i wszystko się rozjedzie."
                </p>
            </div>
            
            <div class="problem-card">
                <div class="problem-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                    </svg>
                </div>
                <p class="problem-card__quote">
                    „Nie mam czasu prowadzić remontu i odpowiadać na głupie pytania."
                </p>
            </div>
            
            <div class="problem-card">
                <div class="problem-card__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                </div>
                <p class="problem-card__quote">
                    „Nie chcę bałaganu i mieszkania na budowie."
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     JAK ZDEJMUJEMY TO Z GŁOWY
     ============================================ -->
<section class="section section--alt solution">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Jak pracujemy (żebyś miał spokój)</h2>
        </div>
        
        <div class="solution__steps">
            <div class="step-card">
                <div class="step-card__number">01</div>
                <div class="step-card__content">
                    <h3 class="step-card__title">Ustalamy konkretny zakres</h3>
                    <p class="step-card__desc">
                        I trzymamy się ustaleń. Bez zmian w trakcie, bez „a może jeszcze to". Wszystko ustalone na początku.
                    </p>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-card__number">02</div>
                <div class="step-card__content">
                    <h3 class="step-card__title">Komunikacja jest prosta</h3>
                    <p class="step-card__desc">
                        WhatsApp + zdjęcia/krótkie filmiki. Bez dzwonienia o pierdoły. Wiesz, co się dzieje, bez zbędnych telefonów.
                    </p>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-card__number">03</div>
                <div class="step-card__content">
                    <h3 class="step-card__title">Nie zostawiamy bałaganu</h3>
                    <p class="step-card__desc">
                        Po nas możesz od razu mieszkać. Sprzątamy na bieżąco, zabezpieczamy, dbamy o porządek.
                    </p>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-card__number">04</div>
                <div class="step-card__content">
                    <h3 class="step-card__title">Jeśli wyjdą problemy</h3>
                    <p class="step-card__desc">
                        Pokazujemy je, proponujemy rozwiązania i dopiero działamy. Bez niespodzianek w trakcie.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     OFERTA
     ============================================ -->
<section class="section services">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">W czym jesteśmy najmocniejsi</h2>
        </div>
        
        <div class="services__grid">
            <div class="service-card">
                <div class="service-card__image">
                    <img src="/assets/img/service-elewacja.jpg" alt="Malowanie elewacji" loading="lazy">
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">Malowanie elewacji (z materiałem)</h3>
                    <p class="service-card__desc">
                        Przygotowanie, podkład, dwie warstwy farby premium. Twój dom będzie wyglądał jak nowy.
                    </p>
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-card__image">
                    <img src="/assets/img/service-wnetrza.jpg" alt="Gładzie i malowanie" loading="lazy">
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">Gładzie + malowanie wnętrz</h3>
                    <p class="service-card__desc">
                        Gładź gipsowa premium Q4, malowanie sufitów i ścian. Idealne wykończenie bez poprawek.
                    </p>
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-card__image">
                    <img src="/assets/img/service-remont.jpg" alt="Remonty kompleksowe" loading="lazy">
                </div>
                <div class="service-card__content">
                    <h3 class="service-card__title">Remonty i wykończenia (większy zakres)</h3>
                    <p class="service-card__desc">
                        Kompleksowe wykończenie od A do Z. Koordynujemy wszystkie branże – elektryka, hydraulika, glazura.
                    </p>
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-card__content">
                    <h3 class="service-card__title">Malowanie wielkopowierzchniowe agregatem</h3>
                    <p class="service-card__desc">
                        Hale, magazyny, powierzchnie przemysłowe. Szybko, równo, profesjonalnie.
                    </p>
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-card__content">
                    <h3 class="service-card__title">Instytucje i firmy (kosztorysowo)</h3>
                    <p class="service-card__desc">
                        Obiekty publiczne, szkoły, urzędy. Kosztorysy, faktury, terminy – wszystko zgodnie z wymogami.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="services__cta text-center" style="margin-top: 40px;">
            <a href="/cennik.php" class="btn btn--secondary" style="margin: 0 10px;">
                Zobacz kalkulator cen
            </a>
            <a href="/realizacje.php" class="btn btn--secondary" style="margin: 0 10px;">
                Przejdź do realizacji
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     DOWODY
     ============================================ -->
<section class="section section--alt trust">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Zobacz dowody, nie obietnice</h2>
            <p class="section-subtitle">
                Robiliśmy m.in. obiekty publiczne i prywatne domy w Chojnicach i okolicy. Poniżej masz realizacje i case study.
            </p>
        </div>
        
        <!-- Logotypy/realizacje -->
        <div class="trust__logos" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin: 40px 0;">
            <div class="logo-item" style="font-size: 18px; font-weight: 600; color: #2c3e50; padding: 15px 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                II LO Chojnice
            </div>
            <div class="logo-item" style="font-size: 18px; font-weight: 600; color: #2c3e50; padding: 15px 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                Ratusz
            </div>
            <div class="logo-item" style="font-size: 18px; font-weight: 600; color: #2c3e50; padding: 15px 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                Muzeum
            </div>
            <div class="logo-item" style="font-size: 18px; font-weight: 600; color: #2c3e50; padding: 15px 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                Szkoła Katolicka
            </div>
            <div class="logo-item" style="font-size: 18px; font-weight: 600; color: #2c3e50; padding: 15px 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                Kościoły
            </div>
        </div>
        
        <div class="testimonials">
            <div class="testimonial-card">
                <div class="testimonial-card__stars">★★★★★</div>
                <p class="testimonial-card__text">
                    „Korzystałam z usług Maltechnik 5 lat temu. Tapeta przyklejona tak, jak sobie wymarzyłam poza tym doceniam porządek po skończonej pracy Pełen profesjonalizm Polecam!"
                </p>
                <div class="testimonial-card__author">
                    <strong>Martyna Sprada</strong>, Chojnice
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-card__stars">★★★★★</div>
                <p class="testimonial-card__text">
                    „Polecam firmę, profesjonalne podejście do klienta, roboty wykonane w terminie. Fachowe doradztwo na każdym etapie prac. Wykonali u mnie kilka inwestycji, zawsze byłem zadowolony."
                </p>
                <div class="testimonial-card__author">
                    <strong>Dominik Turowski</strong>, Chojnice
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-card__stars">★★★★★</div>
                <p class="testimonial-card__text">
                    „Bardzo solidna firma. Wysoka jakość świadczonych usług, punktualność i dokładność wykonywanych prac."
                </p>
                <div class="testimonial-card__author">
                    <strong>Michał Szpręga</strong>, Chojnice
                </div>
            </div>
        </div>
        
        <div class="trust__cta text-center" style="margin-top: 40px;">
            <a href="/realizacje.php" class="btn btn--primary">
                Zobacz realizacje
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     CENNIK
     ============================================ -->
<section class="section pricing-preview">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Orientacyjne ceny „na gotowo"</h2>
            <p class="section-subtitle">
                Żebyś wiedział, z czym się liczysz — pokazujemy orientacyjne stawki z materiałem. Dokładną wycenę potwierdzamy po oględzinach.
            </p>
        </div>
        
        <div class="pricing__example" style="max-width: 700px; margin: 0 auto;">
            <div class="pricing-box" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <h3 class="pricing-box__title" style="font-size: 24px; margin-bottom: 20px; color: #2c3e50;">Przykład: Malowanie elewacji (z materiałem)</h3>
                
                <div class="pricing-box__price" style="margin: 30px 0;">
                    <span class="price-large" style="font-size: 48px; font-weight: 700; color: #e67e22;">100 zł/m²</span>
                </div>
                
                <div class="pricing-box__calculation" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 30px 0;">
                    <p style="font-size: 18px; margin-bottom: 10px;"><strong>Dom 200 m² elewacji:</strong></p>
                    <p class="price-result" style="font-size: 28px; font-weight: 600; color: #2c3e50;">ok. 20 000 zł (orientacyjnie)</p>
                </div>
                
                <div class="pricing-box__includes" style="text-align: left; color: #555; line-height: 1.8;">
                    <p style="margin: 8px 0;">✓ Przygotowanie powierzchni</p>
                    <p style="margin: 8px 0;">✓ Podkład + 2 warstwy farby premium</p>
                    <p style="margin: 8px 0;">✓ Materiały w cenie</p>
                    <p style="margin: 8px 0;">✓ Sprzątanie na bieżąco</p>
                </div>
            </div>
        </div>
        
        <div class="pricing__calculator text-center">
            <p class="pricing__calculator-text">
                <strong>Chcesz dokładniej policzyć koszt?</strong>
            </p>
            <a href="/cennik.php" class="btn btn--primary btn--large">
                Policz w kalkulatorze
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     CTA GŁÓWNE
     ============================================ -->
<section class="section section--alt cta-main">
    <div class="container">
        <div class="cta-box">
            <div class="cta-box__content">
                <h2 class="cta-box__title">Chcesz mieć spokój? Zrób pierwszy krok.</h2>
                <p class="cta-box__desc">
                    Wypełnij formularz i wybierz godzinę rozmowy. Oddzwonię przygotowany i konkretnie powiem Ci, co dalej.
                </p>
                <div class="cta-box__buttons">
                    <a href="/kontakt.php" class="btn btn--primary btn--large">
                        Przejdź do formularza
                    </a>
                    <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--secondary btn--large">
                        📞 Zadzwoń: <?php echo h($companyPhone); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>