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
    padding: var(--space-4xl) 0;
    background-color: var(--color-white);
}

.privacy-content {
    max-width: 900px;
    margin: 0 auto;
}

.privacy-content h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--space-md);
    color: var(--color-primary);
}

.privacy-content .intro {
    font-size: var(--font-size-lg);
    color: var(--color-gray-600);
    margin-bottom: var(--space-3xl);
    padding-bottom: var(--space-xl);
    border-bottom: 2px solid var(--color-gray-200);
}

.privacy-content h2 {
    font-size: var(--font-size-2xl);
    margin-top: var(--space-3xl);
    margin-bottom: var(--space-lg);
    color: var(--color-primary);
}

.privacy-content h3 {
    font-size: var(--font-size-xl);
    margin-top: var(--space-2xl);
    margin-bottom: var(--space-md);
    color: var(--color-primary);
}

.privacy-content p {
    font-size: var(--font-size-base);
    line-height: var(--line-height-relaxed);
    color: var(--color-gray-700);
    margin-bottom: var(--space-lg);
}

.privacy-content ul,
.privacy-content ol {
    margin-bottom: var(--space-lg);
    padding-left: var(--space-2xl);
}

.privacy-content li {
    font-size: var(--font-size-base);
    line-height: var(--line-height-relaxed);
    color: var(--color-gray-700);
    margin-bottom: var(--space-sm);
}

.privacy-content strong {
    color: var(--color-primary);
    font-weight: var(--font-weight-semibold);
}

.last-updated {
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    margin-bottom: var(--space-2xl);
}

.contact-box {
    background-color: var(--color-gray-50);
    padding: var(--space-xl);
    border-radius: var(--radius-lg);
    margin-top: var(--space-3xl);
}

.contact-box h3 {
    margin-top: 0;
}
</style>

<section class="privacy-page">
    <div class="container">
        <div class="privacy-content">
            
            <h1>Polityka Prywatności</h1>
            
            <p class="last-updated">
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
                Email: <?php echo h($companyEmail); ?><br>
                Telefon: <?php echo h($companyPhone); ?>
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
            <p><em>Zgoda na marketing jest dobrowolna i możesz ją wycofać w każdej chwili.</em></p>
            
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
            <p>
                <strong>NIE sprzedajemy</strong> Twoich danych osobowych osobom trzecim. 
                Wszystkie podmioty, którym udostępniamy dane, są zobowiązane do ich ochrony zgodnie z RODO.
            </p>
            
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
                    Polityka prywatności Google
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
                <li>Email: <?php echo h($companyEmail); ?></li>
                <li>Telefon: <?php echo h($companyPhone); ?></li>
                <li>Adres: <?php echo h($companyAddress); ?></li>
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
                Email: kancelaria@uodo.gov.pl<br>
                Telefon: +48 22 531 03 00<br>
                Strona: <a href="https://uodo.gov.pl" target="_blank" rel="noopener">uodo.gov.pl</a>
            </p>
            
            <!-- Kontakt -->
            <div class="contact-box">
                <h3>Pytania dotyczące prywatności?</h3>
                <p>
                    Jeśli masz pytania dotyczące tej polityki prywatności lub przetwarzania Twoich danych osobowych, 
                    skontaktuj się z nami:
                </p>
                <p>
                    <strong>Email:</strong> <?php echo h($companyEmail); ?><br>
                    <strong>Telefon:</strong> <?php echo h($companyPhone); ?><br>
                    <strong>Adres:</strong> <?php echo h($companyAddress); ?>
                </p>
                <p>
                    Odpowiemy na Twoje zapytanie w ciągu 30 dni.
                </p>
            </div>
            
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>