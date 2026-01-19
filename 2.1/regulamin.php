<?php
/**
 * REGULAMIN.PHP - Regulamin serwisu
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ustawienia
$settings = getSettings();
$companyName = $settings['company_name'] ?? 'Maltechnik';
$companyAddress = $settings['company_address'] ?? '89-600 Chojnice, Ul. Tischnera 8';
$companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
$companyPhone = $settings['company_phone'] ?? '+48 784 607 452';

$pageTitle = 'Regulamin';
?>
<?php include 'includes/header.php'; ?>

<style>
.terms-page {
    padding: 4rem 0;
    background-color: #FFFFFF;
}

@media (min-width: 768px) {
    .terms-page {
        padding: 5rem 0;
    }
}

.terms-content {
    max-width: 900px;
    margin: 0 auto;
}

.terms-content h1 {
    font-size: 2.25rem;
    margin-bottom: 1rem;
    color: #2B59A6;
    font-weight: 700;
}

@media (min-width: 768px) {
    .terms-content h1 {
        font-size: 3rem;
    }
}

.terms-content .intro {
    font-size: 1.125rem;
    color: #6B7280;
    margin-bottom: 3rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #E5E7EB;
    line-height: 1.7;
}

.terms-content h2 {
    font-size: 1.5rem;
    margin-top: 3rem;
    margin-bottom: 1.25rem;
    color: #2B59A6;
    font-weight: 700;
}

@media (min-width: 768px) {
    .terms-content h2 {
        font-size: 1.75rem;
    }
}

.terms-content h3 {
    font-size: 1.25rem;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #111827;
    font-weight: 600;
}

.terms-content p {
    font-size: 1rem;
    line-height: 1.75;
    color: #111827;
    margin-bottom: 1.25rem;
}

.terms-content ul,
.terms-content ol {
    margin-bottom: 1.25rem;
    padding-left: 2rem;
}

.terms-content ul {
    list-style: none;
}

.terms-content ul li {
    position: relative;
    padding-left: 1.5rem;
}

.terms-content ul li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.625rem;
    width: 6px;
    height: 6px;
    background-color: #2B59A6;
    border-radius: 50%;
}

.terms-content ol {
    list-style: decimal;
}

.terms-content li {
    font-size: 1rem;
    line-height: 1.75;
    color: #111827;
    margin-bottom: 0.5rem;
}

.terms-content strong {
    color: #2B59A6;
    font-weight: 600;
}

.terms-content a {
    color: #2B59A6;
    text-decoration: underline;
    transition: color 150ms ease-in-out;
}

.terms-content a:hover {
    color: #244C8F;
}

.last-updated {
    font-size: 0.875rem;
    color: #6B7280;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.last-updated i {
    color: #2B59A6;
}

.important-box {
    background-color: #FEF3C7;
    border-left: 4px solid #F59E0B;
    padding: 1.25rem;
    margin: 1.5rem 0;
    border-radius: 0.5rem;
    display: flex;
    gap: 1rem;
}

.important-box i {
    flex-shrink: 0;
    color: #F59E0B;
    font-size: 1.5rem;
}

.important-box p {
    margin-bottom: 0;
    color: #78350F;
}

.important-box strong {
    color: #78350F;
}

.contact-info-box {
    background: #E9F0FF;
    padding: 1.5rem;
    border-radius: 0.75rem;
    margin-top: 2rem;
    border: 1px solid #DBEAFE;
}

.contact-info-box p {
    margin-bottom: 0.5rem;
}

.contact-info-box p:last-child {
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 767px) {
    .terms-content {
        padding: 0 1rem;
    }
    
    .terms-content h1 {
        font-size: 2rem;
    }
    
    .terms-content h2 {
        font-size: 1.5rem;
    }
    
    .terms-content h3 {
        font-size: 1.125rem;
    }
    
    .terms-content .intro {
        font-size: 1rem;
    }
}
</style>

<section class="terms-page">
    <div class="container">
        <div class="terms-content">
            
            <h1>Regulamin Serwisu</h1>
            
            <p class="last-updated">
                <i class="bi bi-calendar-check"></i>
                <strong>Data ostatniej aktualizacji:</strong> <?php echo date('d.m.Y'); ?>
            </p>
            
            <p class="intro">
                Niniejszy regulamin określa zasady korzystania ze strony internetowej <?php echo h($companyName); ?>, 
                dostępnej pod adresem www.maltechnik.pl, oraz zasady świadczenia usług przez firmę <?php echo h($companyName); ?>.
            </p>
            
            <!-- 1. Postanowienia ogólne -->
            <h2>§1. Postanowienia ogólne</h2>
            
            <h3>1.1. Definicje</h3>
            <p>Użyte w Regulaminie pojęcia oznaczają:</p>
            <ul>
                <li><strong>Serwis</strong> – strona internetowa dostępna pod adresem www.maltechnik.pl</li>
                <li><strong>Usługodawca/Administrator</strong> – <?php echo h($companyName); ?>, <?php echo h($companyAddress); ?></li>
                <li><strong>Użytkownik</strong> – osoba fizyczna, osoba prawna lub jednostka organizacyjna nieposiadająca osobowości prawnej, korzystająca z Serwisu</li>
                <li><strong>Usługi</strong> – usługi budowlane świadczone przez Usługodawcę (malowanie, tynkowanie, remonty, wykończenia)</li>
                <li><strong>Regulamin</strong> – niniejszy dokument</li>
            </ul>
            
            <h3>1.2. Zakres regulaminu</h3>
            <p>
                Regulamin określa zasady i warunki korzystania z Serwisu oraz zasady świadczenia usług budowlanych 
                przez <?php echo h($companyName); ?>.
            </p>
            
            <!-- 2. Zasady korzystania z serwisu -->
            <h2>§2. Zasady korzystania z Serwisu</h2>
            
            <h3>2.1. Wymogi techniczne</h3>
            <p>Do korzystania z Serwisu niezbędne są:</p>
            <ul>
                <li>Urządzenie z dostępem do Internetu (komputer, telefon, tablet)</li>
                <li>Przeglądarka internetowa (Chrome, Firefox, Safari, Edge)</li>
                <li>Aktywne połączenie internetowe</li>
                <li>Włączona obsługa JavaScript i cookies</li>
            </ul>
            
            <h3>2.2. Zakaz niedozwolonego użytkowania</h3>
            <p>Użytkownik zobowiązuje się do korzystania z Serwisu w sposób zgodny z prawem i dobrymi obyczajami. Zabronione jest:</p>
            <ul>
                <li>Przekazywanie treści o charakterze bezprawnym</li>
                <li>Wykorzystywanie Serwisu w sposób zakłócający jego funkcjonowanie</li>
                <li>Podszywanie się pod inne osoby lub podmioty</li>
                <li>Wprowadzanie do Serwisu złośliwego oprogramowania (wirusy, malware)</li>
                <li>Próby nieuprawnionego dostępu do systemów informatycznych</li>
                <li>Automatyczne pobieranie danych (scraping) bez zgody Administratora</li>
            </ul>
            
            <!-- 3. Usługi elektroniczne -->
            <h2>§3. Usługi elektroniczne świadczone w Serwisie</h2>
            
            <h3>3.1. Rodzaje usług elektronicznych</h3>
            <p>W ramach Serwisu świadczone są następujące usługi elektroniczne (bezpłatne):</p>
            <ul>
                <li><strong>Formularz kontaktowy</strong> – umożliwia przesłanie zapytania do Usługodawcy</li>
                <li><strong>Kalkulator cen</strong> – narzędzie do orientacyjnego wyliczenia kosztów usług</li>
                <li><strong>Newsletter</strong> – wysyłka informacji o usługach i promocjach (po zapisaniu się)</li>
                <li><strong>Przeglądanie portfolio</strong> – prezentacja zrealizowanych projektów</li>
                <li><strong>Formularz zgłoszeniowy na kursy</strong> – rejestracja zainteresowania kursami</li>
            </ul>
            
            <h3>3.2. Warunki świadczenia usług elektronicznych</h3>
            <p>
                Usługi elektroniczne świadczone są nieodpłatnie, bezterminowo, do czasu wycofania zgody 
                lub zaprzestania korzystania z Serwisu przez Użytkownika.
            </p>
            
            <!-- 4. Składanie zamówień -->
            <h2>§4. Składanie zapytań i zamówień na usługi</h2>
            
            <h3>4.1. Proces składania zapytania</h3>
            <ol>
                <li>Użytkownik wypełnia formularz kontaktowy lub dzwoni na podany numer telefonu</li>
                <li>Usługodawca kontaktuje się z Użytkownikiem w celu ustalenia szczegółów</li>
                <li>Usługodawca (opcjonalnie) przeprowadza wizję lokalną</li>
                <li>Usługodawca przesyła wycenę usług</li>
                <li>W przypadku akceptacji wyceny, strony podpisują umowę</li>
            </ol>
            
            <h3>4.2. Wycena usług</h3>
            <p>
                Wycena usług budowlanych jest przygotowywana indywidualnie po wizji lokalnej lub na podstawie 
                dostarczonych zdjęć i informacji. Orientacyjne ceny podane w Serwisie (w kalkulatorze i na stronie cennika) 
                mają charakter informacyjny i nie stanowią oferty handlowej w rozumieniu Kodeksu Cywilnego.
            </p>
            
            <div class="important-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <p>
                    <strong>Ważne:</strong> Ostateczna cena usługi jest ustalana po oględzinach obiektu 
                    i może różnić się od wyceny kalkulatora online.
                </p>
            </div>
            
            <!-- 5. Warunki świadczenia usług -->
            <h2>§5. Warunki świadczenia usług budowlanych</h2>
            
            <h3>5.1. Zakres usług</h3>
            <p>Usługodawca świadczy następujące usługi:</p>
            <ul>
                <li>Malowanie elewacji budynków</li>
                <li>Malowanie i wykończenia wnętrz (gładzie, tynki, tapetowanie)</li>
                <li>Remonty kompleksowe</li>
                <li>Tynki dekoracyjne</li>
                <li>Pakiety premium (z dodatkowymi usługami)</li>
                <li>Kursy i szkolenia z zakresu malarstwa budowlanego</li>
            </ul>
            
            <h3>5.2. Warunki realizacji</h3>
            <p>
                Szczegółowe warunki realizacji usługi (zakres prac, termin, cena, materiały) 
                są określane indywidualnie w umowie podpisanej przez Strony.
            </p>
            
            <h3>5.3. Materiały</h3>
            <p>
                Usługodawca stosuje materiały premium renomowanych producentów. 
                Klient może zażyczyć sobie użycia konkretnych materiałów – wycena zostanie dostosowana.
            </p>
            
            <h3>5.4. Płatności</h3>
            <p>Formy płatności:</p>
            <ul>
                <li>Przelew bankowy</li>
                <li>Gotówka</li>
            </ul>
            <p>
                Szczegóły płatności (zaliczka, transze, termin płatności) określa umowa. 
                Zazwyczaj: 30-50% zaliczka przed rozpoczęciem, reszta po zakończeniu prac.
            </p>
            
            <!-- 6. Gwarancja -->
            <h2>§6. Gwarancja i reklamacje</h2>
            
            <h3>6.1. Okres gwarancji</h3>
            <p>
                Usługodawca udziela <strong>5-letniej gwarancji</strong> na wykonane prace, 
                pod warunkiem prawidłowego użytkowania powierzchni zgodnie z zaleceniami.
            </p>
            
            <h3>6.2. Zakres gwarancji</h3>
            <p>Gwarancja obejmuje:</p>
            <ul>
                <li>Odpryskiwanie farby/tynku (przy prawidłowym użytkowaniu)</li>
                <li>Pęknięcia gładzi (niezwiązane z uszkodzeniami mechanicznymi)</li>
                <li>Zmiany koloru (przy stosowaniu zalecanych środków czyszczących)</li>
            </ul>
            
            <h3>6.3. Wyłączenia gwarancji</h3>
            <p>Gwarancja NIE obejmuje:</p>
            <ul>
                <li>Uszkodzeń mechanicznych (uderzenia, zarysowania)</li>
                <li>Uszkodzeń powstałych w wyniku nieprawidłowego użytkowania</li>
                <li>Uszkodzeń wynikających z działania siły wyższej (powódź, pożar)</li>
                <li>Zmian dokonanych przez osoby trzecie bez zgody Usługodawcy</li>
            </ul>
            
            <h3>6.4. Zgłaszanie reklamacji</h3>
            <p>Reklamacje należy zgłaszać:</p>
            <ul>
                <li>Email: <?php echo h($companyEmail); ?></li>
                <li>Telefon: <?php echo h($companyPhone); ?></li>
                <li>Pisemnie: <?php echo h($companyAddress); ?></li>
            </ul>
            <p>
                Reklamacja powinna zawierać: opis problemu, datę wykonania usługi, zdjęcia (jeśli możliwe). 
                Usługodawca ustosunkuje się do reklamacji w ciągu <strong>14 dni roboczych</strong>.
            </p>
            
            <!-- 7. Odpowiedzialność -->
            <h2>§7. Odpowiedzialność</h2>
            
            <h3>7.1. Odpowiedzialność Usługodawcy</h3>
            <p>
                Usługodawca ponosi odpowiedzialność za szkody wyrządzone Klientowi na zasadach ogólnych 
                określonych w Kodeksie Cywilnym.
            </p>
            <p>
                Usługodawca posiada ubezpieczenie OC na kwotę 500 000 PLN.
            </p>
            
            <h3>7.2. Wyłączenia odpowiedzialności za Serwis</h3>
            <p>Administrator nie ponosi odpowiedzialności za:</p>
            <ul>
                <li>Przerwy w działaniu Serwisu wynikające z awarii technicznych lub konserwacji</li>
                <li>Działania Użytkowników niezgodne z Regulaminem</li>
                <li>Treści publikowane przez osoby trzecie (linki zewnętrzne)</li>
            </ul>
            
            <!-- 8. Dane osobowe -->
            <h2>§8. Ochrona danych osobowych</h2>
            <p>
                Zasady przetwarzania danych osobowych określa 
                <a href="/polityka-prywatnosci.php">Polityka Prywatności</a>, 
                która stanowi integralną część Regulaminu.
            </p>
            <p>Korzystając z Serwisu, Użytkownik akceptuje warunki Polityki Prywatności.</p>
            
            <!-- 9. Własność intelektualna -->
            <h2>§9. Własność intelektualna</h2>
            <p>
                Wszystkie treści zamieszczone w Serwisie (teksty, zdjęcia, grafiki, layout strony) 
                są chronione prawem autorskim i stanowią własność <?php echo h($companyName); ?> lub 
                zostały wykorzystane za zgodą właścicieli praw.
            </p>
            <p>
                Zabronione jest kopiowanie, modyfikowanie lub wykorzystywanie treści Serwisu bez pisemnej zgody Administratora.
            </p>
            
            <!-- 10. Postanowienia końcowe -->
            <h2>§10. Postanowienia końcowe</h2>
            
            <h3>10.1. Prawo właściwe</h3>
            <p>
                W sprawach nieuregulowanych Regulaminem zastosowanie mają przepisy prawa polskiego, 
                w szczególności Kodeksu Cywilnego.
            </p>
            
            <h3>10.2. Rozstrzyganie sporów</h3>
            <p>
                Strony będą dążyć do polubownego rozstrzygnięcia wszelkich sporów. 
                W przypadku braku porozumienia, spory będą rozstrzygane przez sąd właściwy dla siedziby Usługodawcy.
            </p>
            
            <h3>10.3. Zmiany Regulaminu</h3>
            <p>
                Administrator zastrzega sobie prawo do wprowadzania zmian w Regulaminie. 
                O zmianach Użytkownicy zostaną poinformowani poprzez publikację nowej wersji Regulaminu na stronie.
            </p>
            <p>
                Zmiany wchodzą w życie w terminie wskazanym przez Administratora, nie krótszym niż 7 dni od daty publikacji.
            </p>
            
            <h3>10.4. Kontakt</h3>
            <p>
                W sprawach dotyczących Regulaminu lub świadczonych usług, prosimy o kontakt:
            </p>
            <div class="contact-info-box">
                <p><strong><?php echo h($companyName); ?></strong></p>
                <p><?php echo h($companyAddress); ?></p>
                <p><i class="bi bi-envelope"></i> Email: <?php echo h($companyEmail); ?></p>
                <p><i class="bi bi-telephone"></i> Telefon: <?php echo h($companyPhone); ?></p>
            </div>
            
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>