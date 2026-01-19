/**
 * CONTACT-FORM.JS - Formularz kontaktowy z dynamicznymi polami
 * Konsultacja → temat
 * Pytanie → pytanie (textarea)
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const formStatus = document.getElementById('formStatus');
    
    if (!form) return;
    
    console.log('=== FORMULARZ KONTAKTOWY ===');
    
    // Obsługa zmiany typu zapytania
    const typeRadios = document.querySelectorAll('input[name="typ"]');
    const tematGroup = document.getElementById('tematGroup');
    const pytanieGroup = document.getElementById('pytanieGroup');
    const tematInput = document.getElementById('temat');
    const pytanieInput = document.getElementById('pytanie');
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const typ = this.value;
            console.log('Zmiana typu:', typ);
            
            if (typ === 'konsultacja') {
                // Pokaż temat, ukryj pytanie
                tematGroup.style.display = 'block';
                pytanieGroup.style.display = 'none';
                tematInput.required = true;
                pytanieInput.required = false;
                pytanieInput.value = '';
            } else if (typ === 'pytanie') {
                // Pokaż pytanie, ukryj temat
                tematGroup.style.display = 'none';
                pytanieGroup.style.display = 'block';
                tematInput.required = false;
                pytanieInput.required = true;
                tematInput.value = '';
            }
        });
    });
    
    // Submit formularza
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('📤 Wysyłanie formularza...');
        
        // Loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Wysyłanie...';
        formStatus.textContent = '';
        formStatus.className = 'form-status';
        
        try {
            const formData = new FormData(form);
            
            // Debug - wypisz dane
            console.log('📤 Wysyłam dane:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            const response = await fetch('/process-contact.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('📥 Odpowiedź serwera:', response.status, response.statusText);
            
            // Sprawdź czy odpowiedź jest JSON
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('❌ Serwer zwrócił HTML zamiast JSON:');
                console.error(text);
                throw new Error('Serwer zwrócił nieprawidłowy format odpowiedzi');
            }
            
            const result = await response.json();
            console.log('📦 Parsowany JSON:', result);
            
            if (result.success) {
                console.log('✅ Formularz wysłany!');
                
                // SUKCES
                formStatus.textContent = '✓ ' + result.message;
                formStatus.className = 'form-status form-status--success';
                formStatus.style.display = 'block';
                
                // Wyczyść formularz
                form.reset();
                
                // Przywróć domyślny widok (konsultacja)
                tematGroup.style.display = 'block';
                pytanieGroup.style.display = 'none';
                tematInput.required = true;
                pytanieInput.required = false;
                
                // Scroll do komunikatu
                formStatus.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
            } else {
                // BŁĄD
                console.error('❌ Błąd:', result.message);
                formStatus.textContent = '✗ ' + (result.message || 'Wystąpił błąd. Spróbuj ponownie.');
                formStatus.className = 'form-status form-status--error';
                formStatus.style.display = 'block';
            }
            
        } catch (error) {
            console.error('❌ Błąd połączenia:', error);
            console.error('Stack:', error.stack);
            formStatus.textContent = '✗ Błąd połączenia: ' + error.message;
            formStatus.className = 'form-status form-status--error';
            formStatus.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Wyślij wiadomość';
        }
    });
    
    // Walidacja email na żywo
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    console.log('✅ Formularz zainicjalizowany');
    
});