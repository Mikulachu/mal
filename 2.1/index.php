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
                    <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--primary btn--large">
                        <i class="bi bi-telephone"></i> Zadzwoń: <?php echo h($companyPhone); ?>
                    </a>
                    <a href="/kontakt.php" class="btn btn--secondary btn--large">
                        <i class="bi bi-envelope"></i> Napisz przez formularz (ze zdjęciami)
                    </a>
                </div>
            </div>
            <div class="hero__image">
                <img src="/assets/img/hero-house.png" alt="Dom po profesjonalnej elewacji" loading="eager">
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
                    <i class="bi bi-cash-coin"></i>
                </div>
                <p class="problem-card__quote">
                    „Boję się, że wydam kupę pieniędzy i będzie źle zrobione."
                </p>
            </div>
            
            <div class="problem-card">
                <div class="problem-card__icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <p class="problem-card__quote">
                    „Wykonawca przestanie przyjeżdżać i wszystko się rozjedzie."
                </p>
            </div>
            
            <div class="problem-card">
                <div class="problem-card__icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <p class="problem-card__quote">
                    „Nie mam czasu prowadzić remontu i odpowiadać na głupie pytania."
                </p>
            </div>
            
            <div class="problem-card">
                <div class="problem-card__icon">
                    <i class="bi bi-house-slash"></i>
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
        
        <div class="services__cta text-center" style="margin-top: 2.5rem;">
            <a href="/cennik.php" class="btn btn--secondary" style="margin: 0 0.625rem;">
                <i class="bi bi-calculator"></i> Zobacz kalkulator cen
            </a>
            <a href="/realizacje.php" class="btn btn--secondary" style="margin: 0 0.625rem;">
                <i class="bi bi-images"></i> Przejdź do realizacji
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
        <div class="trust__logos" style="display: flex; flex-wrap: wrap; gap: 1.25rem; justify-content: center; margin: 2.5rem 0;">
            <div class="logo-item" style="font-size: 1.125rem; font-weight: 600; color: #111827; padding: 0.9375rem 1.875rem; background: #FFFFFF; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #E5E7EB;">
                <i class="bi bi-building" style="color: #2B59A6; margin-right: 0.5rem;"></i> II LO Chojnice
            </div>
            <div class="logo-item" style="font-size: 1.125rem; font-weight: 600; color: #111827; padding: 0.9375rem 1.875rem; background: #FFFFFF; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #E5E7EB;">
                <i class="bi bi-bank" style="color: #2B59A6; margin-right: 0.5rem;"></i> Ratusz
            </div>
            <div class="logo-item" style="font-size: 1.125rem; font-weight: 600; color: #111827; padding: 0.9375rem 1.875rem; background: #FFFFFF; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #E5E7EB;">
                <i class="bi bi-bezier2" style="color: #2B59A6; margin-right: 0.5rem;"></i> Muzeum
            </div>
            <div class="logo-item" style="font-size: 1.125rem; font-weight: 600; color: #111827; padding: 0.9375rem 1.875rem; background: #FFFFFF; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #E5E7EB;">
                <i class="bi bi-mortarboard" style="color: #2B59A6; margin-right: 0.5rem;"></i> Szkoła Katolicka
            </div>
            <div class="logo-item" style="font-size: 1.125rem; font-weight: 600; color: #111827; padding: 0.9375rem 1.875rem; background: #FFFFFF; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #E5E7EB;">
                <i class="bi bi-globe" style="color: #2B59A6; margin-right: 0.5rem;"></i> Kościoły
            </div>
        </div>
        
        <div class="testimonials">
            <div class="testimonial-card">
                <div class="testimonial-card__stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="testimonial-card__text">
                    „Korzystałam z usług Maltechnik 5 lat temu. Tapeta przyklejona tak, jak sobie wymarzyłam poza tym doceniam porządek po skończonej pracy Pełen profesjonalizm Polecam!"
                </p>
                <div class="testimonial-card__author">
                    <strong>Martyna Sprada</strong>, Chojnice
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-card__stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="testimonial-card__text">
                    „Polecam firmę, profesjonalne podejście do klienta, roboty wykonane w terminie. Fachowe doradztwo na każdym etapie prac. Wykonali u mnie kilka inwestycji, zawsze byłem zadowolony."
                </p>
                <div class="testimonial-card__author">
                    <strong>Dominik Turowski</strong>, Chojnice
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-card__stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="testimonial-card__text">
                    „Bardzo solidna firma. Wysoka jakość świadczonych usług, punktualność i dokładność wykonywanych prac."
                </p>
                <div class="testimonial-card__author">
                    <strong>Michał Szpręga</strong>, Chojnice
                </div>
            </div>
        </div>
        
        <div class="trust__cta text-center" style="margin-top: 2.5rem;">
            <a href="/realizacje.php" class="btn btn--primary">
                <i class="bi bi-images"></i> Zobacz realizacje
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
            <div class="pricing-box" style="background: #FFFFFF; padding: 2.5rem; border-radius: 0.75rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #E5E7EB;">
                <h3 class="pricing-box__title" style="font-size: 1.5rem; margin-bottom: 1.25rem; color: #111827;">
                    <i class="bi bi-paint-bucket" style="color: #2B59A6; margin-right: 0.5rem;"></i>
                    Przykład: Malowanie elewacji (z materiałem)
                </h3>
                
                <div class="pricing-box__price" style="margin: 1.875rem 0;">
                    <span class="price-large" style="font-size: 3rem; font-weight: 700; color: #2B59A6;">100 zł/m²</span>
                </div>
                
                <div class="pricing-box__calculation" style="background: #F7F8FA; padding: 1.25rem; border-radius: 0.5rem; margin: 1.875rem 0;">
                    <p style="font-size: 1.125rem; margin-bottom: 0.625rem; color: #111827;"><strong>Dom 200 m² elewacji:</strong></p>
                    <p class="price-result" style="font-size: 1.75rem; font-weight: 600; color: #111827;">ok. 20 000 zł (orientacyjnie)</p>
                </div>
                
                <div class="pricing-box__includes" style="text-align: left; color: #6B7280; line-height: 1.8;">
                    <p style="margin: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-check-circle-fill" style="color: #16A34A;"></i> Przygotowanie powierzchni
                    </p>
                    <p style="margin: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-check-circle-fill" style="color: #16A34A;"></i> Podkład + 2 warstwy farby premium
                    </p>
                    <p style="margin: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-check-circle-fill" style="color: #16A34A;"></i> Materiały w cenie
                    </p>
                    <p style="margin: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-check-circle-fill" style="color: #16A34A;"></i> Sprzątanie na bieżąco
                    </p>
                </div>
            </div>
        </div>
        
        <div class="pricing__calculator text-center">
            <p class="pricing__calculator-text">
                <strong>Chcesz dokładniej policzyć koszt?</strong>
            </p>
            <a href="/cennik.php" class="btn btn--primary btn--large">
                <i class="bi bi-calculator"></i> Policz w kalkulatorze
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     PREMIUM: PROWADZENIE BUDOWY
     ============================================ -->
<section class="section section--premium" style="background: linear-gradient(135deg, #1e3a73 0%, #2B59A6 100%); color: #FFFFFF; padding: 4rem 0;">
    <div class="container">
        <div class="premium-box" style="max-width: 900px; margin: 0 auto;">
            <div class="premium-badge" style="display: inline-block; background: #FFFFFF; color: #2B59A6; padding: 0.5rem 1.25rem; border-radius: 2rem; font-weight: 700; font-size: 0.875rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Premium
            </div>

            <h2 style="font-size: 2.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #FFFFFF;">
                Prowadzenie budowy / organizacja ekip
            </h2>

            <div style="font-size: 1.125rem; line-height: 1.8; color: rgba(255,255,255,0.95);">
                <p style="margin-bottom: 1rem;">
                    <strong>Masz pieniądze na budowę, ale nie masz czasu na cyrk z ekipami?</strong>
                </p>
                <p style="margin-bottom: 1rem;">
                    Budowa potrafi zjeść miesiące życia: telefony, pytania o oczywistości, brak decyzji, przerwy w robocie i poprawki.
                </p>
                <p style="margin-bottom: 1rem;">
                    U nas to wygląda inaczej: bierzemy na siebie organizację, koordynację i jakość.
                </p>
                <p style="margin-bottom: 2rem;">
                    Ty zamiast studiować technologie i tłumaczyć wykonawcom „jak to ma być", zajmujesz się pracą, rodziną albo odpoczywasz.
                </p>
            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                <a href="tel:<?php echo h($companyPhone); ?>" class="btn" style="background: #FFFFFF; color: #2B59A6; padding: 1rem 2rem; border-radius: 0.5rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-telephone"></i> Zadzwoń: <?php echo h($companyPhone); ?>
                </a>
                <a href="/kontakt.php" class="btn" style="background: transparent; color: #FFFFFF; border: 2px solid #FFFFFF; padding: 1rem 2rem; border-radius: 0.5rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-envelope"></i> Napisz przez formularz (ze zdjęciami)
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     PREMIUM: REALIZACJA PROJEKTU DEWELOPERSKIEGO
     ============================================ -->
<section class="section" style="background: #F7F8FA; padding: 4rem 0;">
    <div class="container">
        <div class="premium-box" style="max-width: 900px; margin: 0 auto;">
            <div class="premium-badge" style="display: inline-block; background: #2B59A6; color: #FFFFFF; padding: 0.5rem 1.25rem; border-radius: 2rem; font-weight: 700; font-size: 0.875rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Premium
            </div>

            <h2 style="font-size: 2.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #111827;">
                Realizacja projektu deweloperskiego
            </h2>

            <div style="font-size: 1.125rem; line-height: 1.8; color: #6B7280;">
                <p style="margin-bottom: 1rem;">
                    <strong>Masz działkę albo kapitał i chcesz zrobić projekt, który ma się sprzedać — a nie „ładnie wyglądać na papierze"?</strong>
                </p>
                <p style="margin-bottom: 1rem;">
                    Pomagamy poukładać temat tak, żeby inwestycja miała sens: decyzje, wykonawcy, standard, terminy, ryzyka i jakość.
                </p>
                <p style="margin-bottom: 2rem;">
                    Celem jest prosty wynik: zbudować, dowieźć i sprzedać bez przepalania kasy na błędach.
                </p>
            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--primary btn--large">
                    <i class="bi bi-telephone"></i> Zadzwoń: <?php echo h($companyPhone); ?>
                </a>
                <a href="/kontakt.php" class="btn btn--secondary btn--large">
                    <i class="bi bi-envelope"></i> Napisz przez formularz
                </a>
            </div>
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
                    Zadzwoń w godzinach 8:00-16:00 (pn-pt) lub napisz przez formularz. Odpowiadamy pisemnie (mail/WhatsApp).
                </p>
                <div class="cta-box__buttons">
                    <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--primary btn--large">
                        <i class="bi bi-telephone"></i> Zadzwoń: <?php echo h($companyPhone); ?>
                    </a>
                    <a href="/kontakt.php" class="btn btn--secondary btn--large">
                        <i class="bi bi-envelope"></i> Napisz przez formularz (ze zdjęciami)
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>