<?php
/**
 * KURSY.PHP - Kursy i szkolenia
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Kursy i szkolenia';
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/kursy.css">

<!-- ============================================
     HERO KURSY
     ============================================ -->
<section class="hero-courses">
    <div class="container">
        <div class="hero-courses__content">
            <span class="hero-courses__label">Dziel się wiedzą</span>
            <h1 class="hero-courses__title">Kursy i szkolenia malarskie</h1>
            <p class="hero-courses__subtitle">
                Poznaj techniki, które stosujemy w naszych projektach premium. Od podstaw po zaawansowane wykończenia dekoracyjne.
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     INTRO
     ============================================ -->
<section class="section courses-intro">
    <div class="container">
        <div class="intro-content">
            <h2>Ucz się od praktyków</h2>
            <p>
                Przez 12 lat pracy zebraliśmy doświadczenie, które chcemy przekazać dalej. Nasze kursy to nie teoria z podręcznika – to praktyczne warsztaty, gdzie nauczysz się technik stosowanych w prawdziwych projektach premium.
            </p>
            <p>
                Czy jesteś początkującym malarzem, czy doświadczonym fachowcem szukającym nowych umiejętności – mamy coś dla Ciebie.
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     OFERTA KURSÓW
     ============================================ -->
<section class="section section--alt courses-offer">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Nasze kursy</h2>
        </div>
        
        <div class="courses-grid">
            
            <!-- Kurs 1 -->
            <div class="course-card">
                <div class="course-card__badge">Dla początkujących</div>
                <div class="course-card__content">
                    <h3 class="course-card__title">Podstawy malarstwa budowlanego</h3>
                    <p class="course-card__desc">
                        Naucz się profesjonalnie malować ściany i sufity. Przygotowanie powierzchni, techniki aplikacji, dobór narzędzi i farb.
                    </p>
                    <ul class="course-card__features">
                        <li>Przygotowanie podłoża i gruntowanie</li>
                        <li>Techniki malowania wałkiem i pędzlem</li>
                        <li>Maskowanie i wykończenia precyzyjne</li>
                        <li>Dobór farb i narzędzi</li>
                        <li>Praktyczne ćwiczenia na ścianie testowej</li>
                    </ul>
                    <div class="course-card__meta">
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            2 dni (16h)
                        </span>
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            max 8 osób
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Kurs 2 -->
            <div class="course-card course-card--featured">
                <div class="course-card__badge">Najpopularniejszy</div>
                <div class="course-card__content">
                    <h3 class="course-card__title">Gładzie gipsowe premium (Q4)</h3>
                    <p class="course-card__desc">
                        Nauczysz się wykonywać gładzie w najwyższym standardzie Q4 – idealna powierzchnia pod malowanie premium.
                    </p>
                    <ul class="course-card__features">
                        <li>Techniki nakładania gładzi Q3 i Q4</li>
                        <li>Przygotowanie podłoża (płyty G-K, tynki)</li>
                        <li>Szlifowanie i wykończenie</li>
                        <li>Naprawa pęknięć i ubytków</li>
                        <li>Certyfikat po ukończeniu</li>
                    </ul>
                    <div class="course-card__meta">
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            3 dni (24h)
                        </span>
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            max 6 osób
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Kurs 3 -->
            <div class="course-card">
                <div class="course-card__badge">Zaawansowany</div>
                <div class="course-card__content">
                    <h3 class="course-card__title">Tynki dekoracyjne</h3>
                    <p class="course-card__desc">
                        Tynk wenecki, betony architektoniczne, struktury. Poznaj techniki, które tworzą wyjątkowe wnętrza.
                    </p>
                    <ul class="course-card__features">
                        <li>Tynk wenecki – techniki aplikacji</li>
                        <li>Betony architektoniczne</li>
                        <li>Tynki strukturalne i mineralne</li>
                        <li>Efekty metaliczne i perłowe</li>
                        <li>Zabezpieczenie woskiem/lakierem</li>
                    </ul>
                    <div class="course-card__meta">
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            3 dni (24h)
                        </span>
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            max 6 osób
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Kurs 4 -->
            <div class="course-card">
                <div class="course-card__badge">Dla firm</div>
                <div class="course-card__content">
                    <h3 class="course-card__title">Szkolenia dla ekip remontowych</h3>
                    <p class="course-card__desc">
                        Podnosimy kwalifikacje całych zespołów. Materiały, techniki, standardy jakości – wszystko, co podniesie poziom Waszych realizacji.
                    </p>
                    <ul class="course-card__features">
                        <li>Szkolenia dopasowane do poziomu zespołu</li>
                        <li>Praktyczne warsztaty na obiekcie</li>
                        <li>Materiały i narzędzia premium</li>
                        <li>Certyfikaty dla uczestników</li>
                        <li>Wsparcie poszkoleniowe</li>
                    </ul>
                    <div class="course-card__meta">
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Ustalane indywidualnie
                        </span>
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Bez limitu
                        </span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- ============================================
     DLACZEGO MY
     ============================================ -->
<section class="section why-us">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Dlaczego warto się uczyć od nas?</h2>
        </div>
        
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-item__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <polyline points="2 17 12 22 22 17"/>
                        <polyline points="2 12 12 17 22 12"/>
                    </svg>
                </div>
                <h4>12 lat praktyki</h4>
                <p>Uczymy tego, co robiliśmy w ponad 100 projektach rzeczywistych</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-item__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <h4>Praktyka, nie teoria</h4>
                <p>80% czasu to praca ręczna – uczysz się robiąc, nie słuchając</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-item__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <h4>Małe grupy</h4>
                <p>Maksymalnie 6-8 osób – indywidualne podejście do każdego</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-item__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <h4>Materiały premium</h4>
                <p>Pracujesz na tych samych produktach, co my w projektach premium</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-item__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <h4>Certyfikat</h4>
                <p>Otrzymujesz certyfikat ukończenia – wartościowe potwierdzenie umiejętności</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-item__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                </div>
                <h4>Wsparcie po kursie</h4>
                <p>Zostajemy w kontakcie – możesz pytać, konsultować się</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     FORMULARZ ZAINTERESOWANIA
     ============================================ -->
<section class="section section--alt interest-form-section">
    <div class="container">
        <div class="interest-form-wrapper">
            <div class="interest-form-content">
                <h2>Zainteresowany kursem?</h2>
                <p>
                    Wypełnij formularz, a skontaktujemy się z Tobą z informacjami o najbliższych terminach i cenach.
                </p>
            </div>
            
            <form class="interest-form" id="interestForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="imie" class="form-label">Imię <span class="required">*</span></label>
                        <input type="text" name="imie" id="imie" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nazwisko" class="form-label">Nazwisko</label>
                        <input type="text" name="nazwisko" id="nazwisko" class="form-input">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" id="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefon" class="form-label">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="typ_kursu" class="form-label">Który kurs Cię interesuje?</label>
                    <select name="typ_kursu" id="typ_kursu" class="form-select">
                        <option value="">-- Wybierz --</option>
                        <option value="podstawy">Podstawy malarstwa budowlanego</option>
                        <option value="gladzie">Gładzie gipsowe premium (Q4)</option>
                        <option value="tynki">Tynki dekoracyjne</option>
                        <option value="firmowe">Szkolenia dla ekip (B2B)</option>
                        <option value="inne">Chcę dowiedzieć się więcej</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="doswiadczenie" class="form-label">Twoje doświadczenie</label>
                    <select name="doswiadczenie" id="doswiadczenie" class="form-select">
                        <option value="">-- Wybierz --</option>
                        <option value="poczatkujacy">Początkujący (brak doświadczenia)</option>
                        <option value="sredniozaawansowany">Średniozaawansowany (1-3 lata)</option>
                        <option value="zaawansowany">Zaawansowany (3+ lata)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="wiadomosc" class="form-label">Dodatkowe informacje</label>
                    <textarea name="wiadomosc" id="wiadomosc" class="form-textarea" rows="4" placeholder="Opcjonalnie: napisz, czego konkretnie chcesz się nauczyć..."></textarea>
                </div>
                
                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" name="zgoda_rodo" id="zgoda_rodo" required>
                        <label for="zgoda_rodo">
                            Akceptuję <a href="/polityka-prywatnosci.php" target="_blank">politykę prywatności</a> <span class="required">*</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" name="zgoda_marketing" id="zgoda_marketing">
                        <label for="zgoda_marketing">
                            Chcę otrzymywać informacje o kursach i szkoleniach
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn--primary btn--large btn--full" id="submitBtn">
                    Wyślij zapytanie
                </button>
                
                <div class="form-status" id="formStatus"></div>
            </form>
        </div>
    </div>
</section>

<!-- ============================================
     FAQ KURSOWE
     ============================================ -->
<section class="section courses-faq">
    <div class="container">
        <h2 class="text-center mb-xl">Najczęstsze pytania o kursy</h2>
        
        <div class="faq-simple">
            <div class="faq-simple__item">
                <h4>Czy potrzebuję jakiegoś doświadczenia?</h4>
                <p>Nie – kursy podstawowe są dla osób bez doświadczenia. Kursy zaawansowane wymagają znajomości podstaw.</p>
            </div>
            
            <div class="faq-simple__item">
                <h4>Czy dostanę certyfikat?</h4>
                <p>Tak, każdy uczestnik otrzymuje certyfikat ukończenia kursu z naszym podpisem.</p>
            </div>
            
            <div class="faq-simple__item">
                <h4>Gdzie odbywają się kursy?</h4>
                <p>W Chojnicach, w naszej siedzibie. Mamy przygotowaną salę szkoleniową z ścianami testowymi.</p>
            </div>
            
            <div class="faq-simple__item">
                <h4>Ile kosztuje kurs?</h4>
                <p>Ceny ustalamy indywidualnie w zależności od kursu i liczby uczestników. Skontaktuj się z nami po szczegóły.</p>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="/assets/js/courses-form.js"></script>

<?php include 'includes/footer.php'; ?>
