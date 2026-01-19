<?php
/**
 * POLITYKA-PRYWATNOSCI.PHP - Polityka prywatności i RODO
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ustawienia
$settings = getSettings();
$companyName = $settings['company_name'] ?? 'Premium Elewacje & Wnętrza';
$companyAddress = $settings['company_address'] ?? 'ul. Przykładowa 123, 89-600 Chojnice';
$companyEmail = $settings['company_email'] ?? 'kontakt@example.pl';
$companyPhone = $settings['company_phone'] ?? '+48 123 456 789';

$pageTitle = 'Polityka prywatności';
?>
<?php include 'includes/header.php'; ?>

<style>
.privacy-page {
    padding: 4rem 0;
    background-color: #FFFFFF;
}

@media (min-width: 768px) {
    .privacy-page {
        padding: 5rem 0;
    }
}

.privacy-content {
    max-width: 900px;
    margin: 0 auto;
}

.privacy-content h1 {
    font-size: 2.25rem;
    margin-bottom: 1rem;
    color: #2B59A6;
    font-weight: 700;
}

@media (min-width: 768px) {
    .privacy-content h1 {
        font-size: 3rem;
    }
}

.privacy-content .intro {
    font-size: 1.125rem;
    color: #6B7280;
    margin-bottom: 3rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #E5E7EB;
    line-height: 1.7;
}

.privacy-content h2 {
    font-size: 1.5rem;
    margin-top: 3rem;
    margin-bottom: 1.25rem;
    color: #2B59A6;
    font-weight: 700;
}

@media (min-width: 768px) {
    .privacy-content h2 {
        font-size: 1.75rem;
    }
}

.privacy-content h3 {
    font-size: 1.25rem;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #111827;
    font-weight: 600;
}

.privacy-content p {
    font-size: 1rem;
    line-height: 1.75;
    color: #111827;
    margin-bottom: 1.25rem;
}

.privacy-content ul,
.privacy-content ol {
    margin-bottom: 1.25rem;
    padding-left: 2rem;
}

.privacy-content ul {
    list-style: none;
}

.privacy-content ul li {
    position: relative;
    padding-left: 1.5rem;
}

.privacy-content ul li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.625rem;
    width: 6px;
    height: 6px;
    background-color: #2B59A6;
    border-radius: 50%;
}

.privacy-content ol {
    list-style: decimal;
}

.privacy-content li {
    font-size: 1rem;
    line-height: 1.75;
    color: #111827;
    margin-bottom: 0.5rem;
}

.privacy-content strong {
    color: #2B59A6;
    font-weight: 600;
}

.privacy-content a {
    color: #2B59A6;
    text-decoration: underline;
    transition: color 150ms ease-in-out;
}

.privacy-content a:hover {
    color: #244C8F;
}

.privacy-content em {
    color: #6B7280;
    font-style: italic;
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

.contact-box {
    background: #E9F0FF;
    padding: 1.5rem;
    border-radius: 0.75rem;
    margin-top: 3rem;
    border: 1px solid #DBEAFE;
}

.contact-box h3 {
    margin-top: 0;
    color: #2B59A6;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.contact-box h3 i {
    color: #2B59A6;
}

.contact-box p {
    margin-bottom: 0.75rem;
}

.contact-box p:last-child {
    margin-bottom: 0;
}

.info-box {
    background: #FEF3C7;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
    border-left: 4px solid #F59E0B;
    display: flex;
    gap: 0.75rem;
}

.info-box i {
    flex-shrink: 0;
    color: #F59E0B;
    font-size: 1.25rem;
}

.info-box p {
    margin: 0;
    color: #78350F;
}

/* Responsive */
@media (max-width: 767px) {
    .privacy-content {
        padding: 0 1rem;
    }
    
    .privacy-content h1 {
        font-size: 2rem;
    }
    
    .privacy-content h2 {
        font-size: 1.5rem;
    }
    
    .privacy-content h3 {
        font-size: 1.125rem;
    }
    
    .privacy-content .intro {
        font-size: 1rem;
    }
}
</style>

<section class="privacy-page">
    <div class="container">
        <div class="privacy-content">
            
            <h1>Polityka Prywatności</h1>
            
            <p class="last-updated">
                <i class="bi bi-calendar-check"></i>
                <strong>Data ostatniej aktualizacji:</strong> <?php echo date('d.m.Y'); ?>
            </p>
            
            <p class="intro">
                Szanujemy Twoją prywatność i zobowiązujemy się do ochrony Twoich danych osobowych. 
                Niniejsza polityka prywatności wyjaśnia, jak zbieramy, wykorzystujemy i chronimy Twoje dane 
                zgodnie z Rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 (RODO).
            </p>
            
            <!-- 1. Administrator danych -->
            <h2>1. Administrator danych osobowych</h2>
            <p>
                Administratorem Twoich danych osobowych jest:
            </p>
            <p>
                <strong><?php echo h($companyName); ?></strong><br>
                <?php echo h($companyAddress); ?><br>
                <i class="bi bi-envelope"></i> Email: <?php echo h($companyEmail); ?><br>
                <i class="bi bi-telephone"></i> Telefon: <?php echo h($companyPhone); ?>
            </p>
            
            <!-- 2. Jakie dane zbieramy -->
            <h2>2. Jakie dane osobowe zbieramy?</h2>
            
            <h3>2.1. Dane podane przez użytkownika</h3>
            <p>Zbieramy dane, które dobrowolnie nam przekazujesz poprzez formularze na stronie:</p>
            <ul>
                <li><strong>Imię i nazwisko</strong> – w celu personalizacji kontaktu</li>
                <li><strong>Adres email</strong> – w celu kontaktu zwrotnego</li>
                <li><strong>Numer telefonu</strong> – jeśli wolisz kontakt telefoniczny (opcjonalnie)</li>
                <li><strong>Treść wiadomości</strong> – w celu udzielenia odpowiedzi na Twoje pytanie</li>
                <li><strong>Informacje o usłudze</strong> – np. rodzaj usługi, która Cię interesuje</li>
            </ul>
            
            <h3>2.2. Dane zbierane automatycznie</h3>
            <p>Podczas korzystania z naszej strony automatycznie zbieramy:</p>
            <ul>
                <li><strong>Adres IP</strong> – w celach bezpieczeństwa i statystycznych</li>
                <li><strong>Dane o przeglądarce i urządzeniu</strong> – w celach optymalizacji strony</li>
                <li><strong>Dane o aktywności</strong> – za pomocą Google Analytics (zobacz punkt 6)</li>
            </ul>
            
            <!-- 3. Cel przetwarzania -->
            <h2>3. W jakim celu przetwarzamy Twoje dane?</h2>
            <p>Twoje dane osobowe przetwarzamy w następujących celach:</p>
            
            <h3>3.1. Kontakt i komunikacja</h3>
            <ul>
                <li>Odpowiedź na Twoje zapytanie wysłane przez formularz kontaktowy</li>
                <li>Przygotowanie wyceny usług</li>
                <li>Umówienie konsultacji lub spotkania</li>
                <li>Kontakt w sprawie kursów i szkoleń</li>
            </ul>
            <p><strong>Podstawa prawna:</strong> Twoja zgoda (Art. 6 ust. 1 lit. a RODO)</p>
            
            <h3>3.2. Marketing (opcjonalnie)</h3>
            <ul>
                <li>Wysyłka newslettera z informacjami o usługach, promocjach i kursach</li>
                <li>Informowanie o nowościach w ofercie</li>
            </ul>
            <p><strong>Podstawa prawna:</strong> Twoja zgoda (Art. 6 ust. 1 lit. a RODO)</p>
            
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <p><em>Zgoda na marketing jest dobrowolna i możesz ją wycofać w każdej chwili.</em></p>
            </div>
            
            <h3>3.3. Statystyki i analityka</h3>
            <ul>
                <li>Analiza ruchu na stronie</li>
                <li>Optymalizacja działania strony</li>
                <li>Zbieranie statystyk (anonimowo)</li>
            </ul>
            <p><strong>Podstawa prawna:</strong> Nasz prawnie uzasadniony interes (Art. 6 ust. 1 lit. f RODO)</p>
            
            <h3>3.4. Bezpieczeństwo</h3>
            <ul>
                <li>Zapobieganie nadużyciom i spamowi</li>
                <li>Ochrona przed atakami (np. DDoS)</li>
            </ul>
            <p><strong>Podstawa prawna:</strong> Nasz prawnie uzasadniony interes (Art. 6 ust. 1 lit. f RODO)</p>
            
            <!-- 4. Jak długo przechowujemy dane -->
            <h2>4. Jak długo przechowujemy Twoje dane?</h2>
            <p>Twoje dane osobowe przechowujemy przez:</p>
            <ul>
                <li><strong>Zapytania przez formularz:</strong> do 2 lat od ostatniego kontaktu lub do momentu wycofania zgody</li>
                <li><strong>Newsletter:</strong> do momentu wypisania się lub wycofania zgody</li>
                <li><strong>Dane z kalkulatora:</strong> do 2 lat od użycia kalkulatora</li>
                <li><strong>Logi systemowe (IP, aktywność):</strong> do 12 miesięcy</li>
            </ul>
            <p>
                Po upływie tych okresów Twoje dane są automatycznie usuwane z naszych systemów, 
                chyba że prawo wymaga ich dłuższego przechowania (np. przepisy podatkowe).
            </p>
            
            <!-- 5. Komu udostępniamy dane -->
            <h2>5. Komu udostępniamy Twoje dane?</h2>
            <p>Twoje dane osobowe możemy przekazać następującym podmiotom:</p>
            <ul>
                <li><strong>Dostawca hostingu</strong> – serwery, na których przechowywana jest strona</li>
                <li><strong>Google Analytics</strong> – narzędzie do analizy ruchu (dane anonimowe)</li>
                <li><strong>Dostawca poczty email</strong> – w celu wysyłki wiadomości</li>
                <li><strong>Biuro rachunkowe</strong> – jeśli nawiążemy współpracę (faktury, księgowość)</li>
            </ul>
            
            <div class="info-box">
                <i class="bi bi-shield-check"></i>
                <p>
                    <strong>NIE sprzedajemy</strong> Twoich danych osobowych osobom trzecim. 
                    Wszystkie podmioty, którym udostępniamy dane, są zobowiązane do ich ochrony zgodnie z RODO.
                </p>
            </div>
            
            <!-- 6. Cookies i Google Analytics -->
            <h2>6. Pliki cookies i Google Analytics</h2>
            
            <h3>6.1. Czym są pliki cookies?</h3>
            <p>
                Pliki cookies to małe pliki tekstowe zapisywane na Twoim urządzeniu podczas odwiedzania strony. 
                Służą one m.in. do zapamiętywania Twoich preferencji i analizy ruchu.
            </p>
            
            <h3>6.2. Jakie cookies używamy?</h3>
            <ul>
                <li><strong>Cookies niezbędne</strong> – umożliwiają podstawowe funkcje strony (sesje, formularze)</li>
                <li><strong>Cookies analityczne</strong> – Google Analytics (zbieranie statystyk anonimowo)</li>
                <li><strong>Cookies marketingowe</strong> – zapamiętywanie preferencji (opcjonalnie)</li>
            </ul>
            
            <h3>6.3. Google Analytics</h3>
            <p>
                Używamy Google Analytics do analizy ruchu na stronie. Google Analytics zbiera dane anonimowo 
                (np. liczba odwiedzin, źródło ruchu, czas spędzony na stronie). 
                Twój adres IP jest anonimizowany.
            </p>
            <p>
                Więcej informacji: 
                <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">
                    Polityka prywatności Google <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </p>
            
            <h3>6.4. Jak zarządzać cookies?</h3>
            <p>
                Możesz zarządzać plikami cookies w ustawieniach swojej przeglądarki. 
                Pamiętaj, że wyłączenie cookies może wpłynąć na działanie strony.
            </p>
            
            <!-- 7. Twoje prawa -->
            <h2>7. Twoje prawa zgodnie z RODO</h2>
            <p>Masz następujące prawa dotyczące Twoich danych osobowych:</p>
            
            <h3>7.1. Prawo dostępu do danych</h3>
            <p>Możesz poprosić o kopię swoich danych osobowych, które przetwarzamy.</p>
            
            <h3>7.2. Prawo do sprostowania danych</h3>
            <p>Możesz poprosić o poprawienie nieprawidłowych lub niekompletnych danych.</p>
            
            <h3>7.3. Prawo do usunięcia danych ("prawo do bycia zapomnianym")</h3>
            <p>Możesz poprosić o usunięcie swoich danych, jeśli nie są już potrzebne do celów, w jakich zostały zebrane.</p>
            
            <h3>7.4. Prawo do ograniczenia przetwarzania</h3>
            <p>Możesz poprosić o ograniczenie przetwarzania Twoich danych w określonych sytuacjach.</p>
            
            <h3>7.5. Prawo do przenoszenia danych</h3>
            <p>Możesz otrzymać swoje dane w ustrukturyzowanym formacie i przenieść je do innego administratora.</p>
            
            <h3>7.6. Prawo sprzeciwu</h3>
            <p>Możesz wnieść sprzeciw wobec przetwarzania danych w celach marketingowych.</p>
            
            <h3>7.7. Prawo do wycofania zgody</h3>
            <p>
                Jeśli przetwarzamy Twoje dane na podstawie zgody, możesz ją wycofać w każdej chwili. 
                Wycofanie zgody nie wpływa na legalność przetwarzania przed jej wycofaniem.
            </p>
            
            <p>
                <strong>Jak skorzystać z praw?</strong><br>
                Aby skorzystać z któregokolwiek z powyższych praw, skontaktuj się z nami:
            </p>
            <ul>
                <li><i class="bi bi-envelope"></i> Email: <?php echo h($companyEmail); ?></li>
                <li><i class="bi bi-telephone"></i> Telefon: <?php echo h($companyPhone); ?></li>
                <li><i class="bi bi-geo-alt"></i> Adres: <?php echo h($companyAddress); ?></li>
            </ul>
            
            <!-- 8. Bezpieczeństwo -->
            <h2>8. Jak chronimy Twoje dane?</h2>
            <p>Stosujemy odpowiednie środki techniczne i organizacyjne w celu ochrony Twoich danych:</p>
            <ul>
                <li><strong>Szyfrowanie połączenia</strong> – strona używa protokołu HTTPS (SSL)</li>
                <li><strong>Bezpieczne przechowywanie</strong> – dane są przechowywane na zabezpieczonych serwerach</li>
                <li><strong>Ograniczony dostęp</strong> – dostęp do danych mają tylko upoważnione osoby</li>
                <li><strong>Regularne backupy</strong> – kopie zapasowe są tworzone automatycznie</li>
                <li><strong>Monitoring bezpieczeństwa</strong> – wykrywanie prób nieautoryzowanego dostępu</li>
            </ul>
            
            <!-- 9. Zmiany w polityce -->
            <h2>9. Zmiany w polityce prywatności</h2>
            <p>
                Zastrzegamy sobie prawo do wprowadzania zmian w niniejszej polityce prywatności. 
                O wszelkich zmianach poinformujemy na tej stronie. 
                Data ostatniej aktualizacji jest zawsze widoczna na górze dokumentu.
            </p>
            
            <!-- 10. Skargi -->
            <h2>10. Prawo do wniesienia skargi</h2>
            <p>
                Jeśli uważasz, że przetwarzanie Twoich danych osobowych narusza przepisy RODO, 
                masz prawo wnieść skargę do organu nadzorczego:
            </p>
            <p>
                <strong>Urząd Ochrony Danych Osobowych</strong><br>
                ul. Stawki 2<br>
                00-193 Warszawa<br>
                <i class="bi bi-envelope"></i> Email: kancelaria@uodo.gov.pl<br>
                <i class="bi bi-telephone"></i> Telefon: +48 22 531 03 00<br>
                <i class="bi bi-globe"></i> Strona: <a href="https://uodo.gov.pl" target="_blank" rel="noopener">uodo.gov.pl <i class="bi bi-box-arrow-up-right"></i></a>
            </p>
            
            <!-- Kontakt -->
            <div class="contact-box">
                <h3><i class="bi bi-question-circle"></i> Pytania dotyczące prywatności?</h3>
                <p>
                    Jeśli masz pytania dotyczące tej polityki prywatności lub przetwarzania Twoich danych osobowych, 
                    skontaktuj się z nami:
                </p>
                <p>
                    <i class="bi bi-envelope"></i> <strong>Email:</strong> <?php echo h($companyEmail); ?><br>
                    <i class="bi bi-telephone"></i> <strong>Telefon:</strong> <?php echo h($companyPhone); ?><br>
                    <i class="bi bi-geo-alt"></i> <strong>Adres:</strong> <?php echo h($companyAddress); ?>
                </p>
                <p>
                    <i class="bi bi-clock"></i> Odpowiemy na Twoje zapytanie w ciągu 30 dni.
                </p>
            </div>
            
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>