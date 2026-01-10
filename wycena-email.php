<?php
/**
 * WYCENA-EMAIL.PHP - Formularz do wysyłki wyceny
 * NAPRAWIONE: BEZ pokazywania cen, tylko lista usług + metraż
 */

require_once 'includes/functions.php';
require_once 'includes/db.php';

$pageTitle = 'Wyślij wycenę na email';
?>
<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="/assets/css/kontakt.css">

<style>
.quote-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}
.quote-summary h3 {
    margin-top: 0;
    color: #2c3e50;
}
.quote-item {
    padding: 10px 0;
    border-bottom: 1px solid #dee2e6;
}
.quote-item:last-child {
    border-bottom: none;
}
.quote-item strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 5px;
}
.quote-item .meters {
    color: #6c757d;
    font-size: 14px;
}
.quote-note {
    margin-top: 15px;
    padding: 12px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 4px;
    font-size: 14px;
    color: #856404;
}
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

<section class="hero-contact">
    <div class="container">
        <div class="hero-contact__content">
            <h1 class="hero-contact__title">Wyślij wycenę na email</h1>
            <p class="hero-contact__subtitle">
                Wypełnij formularz, a otrzymasz szczegółową wycenę na swoją skrzynkę pocztową
            </p>
        </div>
    </div>
</section>

<section class="section contact-section">
    <div class="container">
        <div class="contact-wrapper">
            
            <!-- PODSUMOWANIE WYCENY (BEZ CEN) -->
            <div class="contact-info">
                <div class="quote-summary" id="quoteSummary">
                    <h3>Podsumowanie wyceny</h3>
                    <div id="quoteItems"></div>
                    <div class="quote-note">
                        <strong>Uwaga:</strong> Kwoty są orientacyjne. Ostateczna cena może się zmienić po ocenie na miejscu.
                    </div>
                </div>
            </div>
            
            <!-- FORMULARZ -->
            <div class="contact-form-wrapper">
                <div class="contact-form-card">
                    <h2 class="contact-form__title">Twoje dane</h2>
                    <p class="contact-form__desc">
                        Podaj swoje dane, a wyślemy wycenę na Twój email
                    </p>
                    
                    <form class="contact-form" id="quoteForm">
                        
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" id="email" class="form-input" placeholder="twoj@email.pl" required>
                        </div>
                        
                        <!-- Imię -->
                        <div class="form-group">
                            <label for="imie" class="form-label">Imię <span class="required">*</span></label>
                            <input type="text" name="imie" id="imie" class="form-input" placeholder="Twoje imię" required>
                        </div>
                        
                        <!-- Nazwisko -->
                        <div class="form-group">
                            <label for="nazwisko" class="form-label">Nazwisko</label>
                            <input type="text" name="nazwisko" id="nazwisko" class="form-input" placeholder="Twoje nazwisko">
                        </div>
                        
                        <!-- Telefon -->
                        <div class="form-group">
                            <label for="telefon" class="form-label">Telefon</label>
                            <input type="tel" name="telefon" id="telefon" class="form-input" placeholder="+48 123 456 789">
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
                        
                        <!-- Hidden field - calculation data -->
                        <input type="hidden" name="calculation_data" id="calculationData">
                        
                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn--large btn--full" id="submitBtn">
                                Wyślij wycenę na email
                            </button>
                        </div>
                        
                        <!-- Status -->
                        <div class="form-status" id="formStatus"></div>
                        
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('quoteForm');
    const submitBtn = document.getElementById('submitBtn');
    const formStatus = document.getElementById('formStatus');
    const quoteItems = document.getElementById('quoteItems');
    const calculationDataField = document.getElementById('calculationData');
    
    // Pobierz dane z localStorage
    const data = localStorage.getItem('calculatorData');
    
    if (!data) {
        window.location.href = '/cennik.php';
        return;
    }
    
    const calcData = JSON.parse(data);
    
    // Wypełnij podsumowanie (BEZ CEN!)
    let html = '';
    calcData.services.forEach(service => {
        html += `
            <div class="quote-item">
                <strong>${service.name}</strong>
                <span class="meters">Powierzchnia: ${formatNumber(service.meters)} m²</span>
            </div>
        `;
    });
    quoteItems.innerHTML = html;
    
    // Zapisz do hidden field
    calculationDataField.value = JSON.stringify(calcData);
    
    // Submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Wysyłanie...';
        formStatus.textContent = '';
        formStatus.className = 'form-status';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('/api/send-quote.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                formStatus.textContent = '✓ Wycena została wysłana na Twój email!';
                formStatus.className = 'form-status form-status--success';
                
                form.reset();
                localStorage.removeItem('calculatorData');
                
                setTimeout(() => {
                    window.location.href = '/';
                }, 3000);
            } else {
                formStatus.textContent = '✗ ' + (result.message || 'Wystąpił błąd');
                formStatus.className = 'form-status form-status--error';
            }
        } catch (error) {
            console.error(error);
            formStatus.textContent = '✗ Błąd połączenia';
            formStatus.className = 'form-status form-status--error';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Wyślij wycenę na email';
        }
    });
    
    function formatNumber(num) {
        return new Intl.NumberFormat('pl-PL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(num);
    }
});
</script>

<?php include 'includes/footer.php'; ?>