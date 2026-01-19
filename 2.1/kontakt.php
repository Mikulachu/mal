<?php
/**
 * KONTAKT.PHP - Strona kontaktowa
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

// Pobierz ustawienia
$settings = getSettings();
$companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
$companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';

$pageTitle = 'Kontakt';
?>
<?php include 'includes/header.php'; ?>

<!-- Dodatkowe style -->
<link rel="stylesheet" href="/assets/css/kontakt.css">

<style>
.form-status {
    display: none;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-top: 1.25rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
}
.form-status.form-status--success {
    display: flex !important;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: #d4edda;
    color: #155724;
    border: 1px solid #16A34A;
}
.form-status.form-status--success i {
    color: #16A34A;
    font-size: 1.25rem;
}
.form-status.form-status--error {
    display: flex !important;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #DC2626;
}
.form-status.form-status--error i {
    color: #DC2626;
    font-size: 1.25rem;
}
</style>

<!-- ============================================
     HERO KONTAKT
     ============================================ -->
<section class="hero-contact">
    <div class="container">
        <div class="hero-contact__content">
            <h1 class="hero-contact__title">Skontaktuj się z nami</h1>
            <p class="hero-contact__subtitle">
                Odpowiadamy w ciągu 24 godzin. Pierwsza konsultacja nic nie kosztuje.
            </p>
        </div>
    </div>
</section>

<!-- ============================================
     CONTACT INFO & FORM
     ============================================ -->
<section class="section contact-section">
    <div class="container">
        <div class="contact-wrapper">
            
            <!-- Contact Info -->
            <div class="contact-info">
                <h2 class="contact-info__title">Dane kontaktowe</h2>
                
                <div class="contact-info__items">
                    <div class="info-item">
                        <div class="info-item__icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div class="info-item__content">
                            <h4>Telefon</h4>
                            <a href="tel:<?php echo h($companyPhone); ?>"><?php echo h($companyPhone); ?></a>
                            <p class="info-item__note">Kontakt telefoniczny: 8:00–16:00 (pn–pt). Po godzinach nie oddzwaniamy i nie prowadzimy rozmów. Jeśli coś jest ważne — napisz lub nagraj wiadomość na WhatsApp i dołącz zdjęcia. Odpiszemy w następnym dniu roboczym.</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item__icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="info-item__content">
                            <h4>Email</h4>
                            <a href="mailto:<?php echo h($companyEmail); ?>"><?php echo h($companyEmail); ?></a>
                            <p class="info-item__note">Odpowiadamy w ciągu 24h</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item__icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="info-item__content">
                            <h4>Obszar działania</h4>
                            <p>Województwo pomorskie i kujawsko-pomorskie</p>
                            <p class="info-item__note">Przyjedziemy na bezpłatną wycenę</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item__icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="info-item__content">
                            <h4>Czas reakcji</h4>
                            <p>Odpowiedź w ciągu 24 godzin</p>
                            <p class="info-item__note">Odpowiadamy w godzinach pracy</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info__alternative">
                    <h3>Inne sposoby kontaktu</h3>
                    <div class="alternative-methods">
                        <a href="https://wa.me/48784607452" class="method-btn" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            WhatsApp
                        </a>
                        <a href="https://m.me/MALTECHNIK.Chojnice" class="method-btn" target="_blank" rel="noopener">
                            <i class="bi bi-messenger"></i>
                            Messenger
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <div class="contact-form-card">
                    <h2 class="contact-form__title">Wyślij wiadomość</h2>
                    <p class="contact-form__desc">
                        Wolisz opisać temat na spokojnie? Napisz w formularzu i dołącz zdjęcia. Odpowiadamy w godzinach pracy. Jeśli piszesz po 16:00 — odpiszemy następnego dnia roboczego.
                    </p>
                    
                    <form class="contact-form" id="contactForm" enctype="multipart/form-data">

                        <!-- Temat wiadomości -->
                        <div class="form-group">
                            <label for="temat_wiadomosci" class="form-label">Temat wiadomości <span class="required">*</span></label>
                            <select name="temat_wiadomosci" id="temat_wiadomosci" class="form-input" required>
                                <option value="">-- Wybierz temat --</option>
                                <option value="remont">Remont / wykończenie wnętrz</option>
                                <option value="elewacja">Elewacja (malowanie / naprawy)</option>
                                <option value="agregat">Malowanie wielkopowierzchniowe agregatem (hale/magazyny)</option>
                                <option value="instytucje">Instytucje i firmy (kosztorys / harmonogram)</option>
                                <option value="prowadzenie_budowy">Prowadzenie budowy / organizacja ekip</option>
                                <option value="deweloper">Realizacja projektu deweloperskiego</option>
                                <option value="konsultacja">Konsultacja online (45 min / 200 zł)</option>
                                <option value="wspolpraca">Współpraca medialna</option>
                                <option value="inne">Inne</option>
                            </select>
                        </div>

                        <!-- Imię i nazwisko -->
                        <div class="form-group">
                            <label for="imie_nazwisko" class="form-label">Imię i nazwisko <span class="required">*</span></label>
                            <input type="text" name="imie_nazwisko" id="imie_nazwisko" class="form-input" placeholder="Jan Kowalski" required>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" id="email" class="form-input" placeholder="twoj@email.pl" required>
                        </div>

                        <!-- WhatsApp / Telefon (opcjonalnie) -->
                        <div class="form-group">
                            <label for="telefon" class="form-label">WhatsApp / telefon</label>
                            <input type="tel" name="telefon" id="telefon" class="form-input" placeholder="+48 784 607 452">
                            <p class="form-help"><i class="bi bi-info-circle"></i> Opcjonalnie – jeśli chcesz odpowiedź też na WhatsApp</p>
                        </div>

                        <!-- Lokalizacja -->
                        <div class="form-group">
                            <label for="lokalizacja" class="form-label">Lokalizacja</label>
                            <input type="text" name="lokalizacja" id="lokalizacja" class="form-input" placeholder="Np. Chojnice, ul. Przykładowa 12">
                            <p class="form-help"><i class="bi bi-geo-alt"></i> Gdzie znajduje się obiekt?</p>
                        </div>

                        <!-- Opis -->
                        <div class="form-group">
                            <label for="opis" class="form-label">Opis <span class="required">*</span></label>
                            <textarea name="opis" id="opis" class="form-textarea" rows="8" placeholder="Opisz szczegółowo, czego potrzebujesz. Im więcej informacji, tym lepiej przygotujemy się do rozmowy." required></textarea>
                            <p class="form-help"><i class="bi bi-chat-left-text"></i> Opisz szczegółowo, w czym możemy Ci pomóc</p>
                        </div>

                        <!-- Termin -->
                        <div class="form-group">
                            <label for="termin" class="form-label">Termin / kiedy chcesz startować</label>
                            <input type="text" name="termin" id="termin" class="form-input" placeholder="Np. za miesiąc, wiosna 2026, jak najszybciej">
                            <p class="form-help"><i class="bi bi-calendar"></i> Kiedy planujesz rozpoczęcie prac?</p>
                        </div>

                        <!-- Zdjęcia -->
                        <div class="form-group">
                            <label for="zdjecia" class="form-label">Zdjęcia (załączniki)</label>
                            <input type="file" name="zdjecia[]" id="zdjecia" class="form-input" multiple accept="image/*,.pdf">
                            <p class="form-help"><i class="bi bi-image"></i> Możesz dodać kilka zdjęć (opcjonalnie)</p>
                        </div>
                        
                        <!-- Zgody -->
                        <div class="form-group">
                            <div class="form-checkbox">
                                <input type="checkbox" name="zgoda_rodo" id="zgoda_rodo" required>
                                <label for="zgoda_rodo">
                                    Akceptuję <a href="/polityka-prywatnosci.php" target="_blank">politykę prywatności</a> i wyrażam zgodę na przetwarzanie moich danych osobowych <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-checkbox">
                                <input type="checkbox" name="zgoda_marketing" id="zgoda_marketing">
                                <label for="zgoda_marketing">
                                    Wyrażam zgodę na otrzymywanie informacji handlowych i marketingowych
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn--large btn--full" id="submitBtn">
                                <i class="bi bi-send"></i> Wyślij wiadomość
                            </button>
                        </div>
                        
                        <!-- Status message -->
                        <div class="form-status" id="formStatus"></div>
                        
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- ============================================
     ALTERNATIVE CONTACT
     ============================================ -->
<section class="section section--alt alternative-contact">
    <div class="container">
        <div class="alternative-contact__content">
            <h2>Wolisz zadzwonić?</h2>
            <p>Kontakt telefoniczny: 8:00–16:00 (pn–pt). Po godzinach napisz przez formularz lub WhatsApp.</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
                <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--primary btn--large">
                    <i class="bi bi-telephone"></i> Zadzwoń: <?php echo h($companyPhone); ?>
                </a>
                <a href="https://wa.me/48784607452" class="btn btn--secondary btn--large" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i> Napisz na WhatsApp (ze zdjęciami)
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="/assets/js/contact-form.js"></script>

<?php include 'includes/footer.php'; ?>