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
                            <p class="info-item__note">Pon-Pt: 8:00-18:00, Sob: 9:00-14:00</p>
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
                            <p class="info-item__note">W pilnych sprawach dzwoń</p>
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
                        Wypełnij formularz, a odezwiemy się w ciągu 24 godzin
                    </p>
                    
                    <form class="contact-form" id="contactForm">
                        
                        <!-- Typ zapytania -->
                        <div class="form-group">
                            <label class="form-label">Czego dotyczy zapytanie?</label>
                            <div class="form-radio-group">
                                <label class="form-radio">
                                    <input type="radio" name="typ" value="konsultacja" checked required>
                                    <span><i class="bi bi-camera-video"></i> Konsultacja online</span>
                                </label>
                                <label class="form-radio">
                                    <input type="radio" name="typ" value="pytanie">
                                    <span><i class="bi bi-question-circle"></i> Mam pytanie</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Imię -->
                        <div class="form-group">
                            <label for="imie" class="form-label">Imię <span class="required">*</span></label>
                            <input type="text" name="imie" id="imie" class="form-input" placeholder="Twoje imię" required>
                        </div>
                        
                        <!-- Nazwisko (opcjonalne) -->
                        <div class="form-group">
                            <label for="nazwisko" class="form-label">Nazwisko</label>
                            <input type="text" name="nazwisko" id="nazwisko" class="form-input" placeholder="Twoje nazwisko">
                        </div>
                        
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" id="email" class="form-input" placeholder="twoj@email.pl" required>
                        </div>
                        
                        <!-- Telefon (opcjonalnie) -->
                        <div class="form-group">
                            <label for="telefon" class="form-label">Telefon</label>
                            <input type="tel" name="telefon" id="telefon" class="form-input" placeholder="+48 784 607 452">
                            <p class="form-help"><i class="bi bi-info-circle"></i> Opcjonalnie – jeśli wolisz kontakt telefoniczny</p>
                        </div>
                        
                        <!-- TEMAT KONSULTACJI (tylko dla konsultacji) -->
                        <div class="form-group" id="tematGroup">
                            <label for="temat" class="form-label">Temat konsultacji <span class="required">*</span></label>
                            <input type="text" name="temat" id="temat" class="form-input" placeholder="Np. Wybór koloru elewacji, dobór materiałów..." required>
                            <p class="form-help"><i class="bi bi-lightbulb"></i> O czym chcesz porozmawiać podczas konsultacji?</p>
                        </div>
                        
                        <!-- PYTANIE (tylko dla pytania) -->
                        <div class="form-group" id="pytanieGroup" style="display: none;">
                            <label for="pytanie" class="form-label">Twoje pytanie <span class="required">*</span></label>
                            <textarea name="pytanie" id="pytanie" class="form-textarea" rows="6" placeholder="Zadaj swoje pytanie..."></textarea>
                            <p class="form-help"><i class="bi bi-chat-left-text"></i> Opisz szczegółowo, w czym możemy Ci pomóc</p>
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
            <p>Rozumiemy – czasem rozmowa jest szybsza niż formularz</p>
            <a href="tel:<?php echo h($companyPhone); ?>" class="btn btn--secondary btn--large">
                <i class="bi bi-telephone"></i> Zadzwoń teraz: <?php echo h($companyPhone); ?>
            </a>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="/assets/js/contact-form.js"></script>

<?php include 'includes/footer.php'; ?>