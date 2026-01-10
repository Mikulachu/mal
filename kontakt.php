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
    padding: 16px 20px;
    border-radius: 8px;
    margin-top: 20px;
    font-size: 15px;
    font-weight: 500;
    text-align: center;
}
.form-status.form-status--success {
    display: block !important;
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.form-status.form-status--error {
    display: block !important;
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h4>Telefon</h4>
                            <a href="tel:<?php echo h($companyPhone); ?>"><?php echo h($companyPhone); ?></a>
                            <p class="info-item__note">Pon-Pt: 8:00-18:00, Sob: 9:00-14:00</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h4>Email</h4>
                            <a href="mailto:<?php echo h($companyEmail); ?>"><?php echo h($companyEmail); ?></a>
                            <p class="info-item__note">Odpowiadamy w ciągu 24h</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h4>Obszar działania</h4>
                            <p>Województwo pomorskie i kujawsko-pomorskie</p>
                            <p class="info-item__note">Przyjedziemy na bezpłatną wycenę</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
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
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            WhatsApp
                        </a>
                        <a href="https://m.me/yourpage" class="method-btn" target="_blank" rel="noopener">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.497 1.745 6.616 4.472 8.652V24l4.086-2.242c1.09.301 2.246.464 3.442.464 6.627 0 12-4.974 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8l3.131 3.259L19.752 8l-6.561 6.963z"/>
                            </svg>
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
                                    <span>Konsultacja online</span>
                                </label>
                                <label class="form-radio">
                                    <input type="radio" name="typ" value="pytanie">
                                    <span>Mam pytanie</span>
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
                            <p class="form-help">Opcjonalnie – jeśli wolisz kontakt telefoniczny</p>
                        </div>
                        
                        <!-- TEMAT KONSULTACJI (tylko dla konsultacji) -->
                        <div class="form-group" id="tematGroup">
                            <label for="temat" class="form-label">Temat konsultacji <span class="required">*</span></label>
                            <input type="text" name="temat" id="temat" class="form-input" placeholder="Np. Wybór koloru elewacji, dobór materiałów..." required>
                            <p class="form-help">O czym chcesz porozmawiać podczas konsultacji?</p>
                        </div>
                        
                        <!-- PYTANIE (tylko dla pytania) -->
                        <div class="form-group" id="pytanieGroup" style="display: none;">
                            <label for="pytanie" class="form-label">Twoje pytanie <span class="required">*</span></label>
                            <textarea name="pytanie" id="pytanie" class="form-textarea" rows="6" placeholder="Zadaj swoje pytanie..."></textarea>
                            <p class="form-help">Opisz szczegółowo, w czym możemy Ci pomóc</p>
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
                                Wyślij wiadomość
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
                📞 Zadzwoń teraz: <?php echo h($companyPhone); ?>
            </a>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="/assets/js/contact-form.js"></script>

<?php include 'includes/footer.php'; ?>