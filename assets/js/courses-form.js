/**
 * COURSES-FORM.JS - Formularz zainteresowania kursami
  
 */

(function() {
    'use strict';
    
    const form = document.getElementById('interestForm');
    const submitBtn = document.getElementById('submitBtn');
    const formStatus = document.getElementById('formStatus');
    
    if (!form) return;
    
    // Submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Walidacja
        if (!validateForm()) {
            showStatus('Popraw błędy w formularzu', 'error');
            return;
        }
        
        // Zbierz dane
        const formData = new FormData(form);
        formData.append('action', 'save_course_interest');
        
        // Loading
        setLoadingState(true);
        hideStatus();
        
        // Wyślij
        fetch('/api/courses-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatus('Dziękujemy! Skontaktujemy się z Tobą wkrótce z informacjami o kursach.', 'success');
                form.reset();
                formStatus.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                showStatus(data.message || 'Wystąpił błąd. Spróbuj ponownie.', 'error');
            }
        })
        .catch(error => {
            console.error('Form error:', error);
            showStatus('Wystąpił błąd połączenia. Spróbuj ponownie później.', 'error');
        })
        .finally(() => {
            setLoadingState(false);
        });
    });
    
    // Walidacja
    function validateForm() {
        let isValid = true;
        
        const imie = form.querySelector('#imie');
        if (!imie.value.trim()) {
            markFieldError(imie);
            isValid = false;
        } else {
            markFieldValid(imie);
        }
        
        const email = form.querySelector('#email');
        if (!email.value.trim() || !window.AppUtils.isValidEmail(email.value)) {
            markFieldError(email);
            isValid = false;
        } else {
            markFieldValid(email);
        }
        
        const zgoda_rodo = form.querySelector('#zgoda_rodo');
        if (!zgoda_rodo.checked) {
            showStatus('Musisz zaakceptować politykę prywatności', 'error');
            isValid = false;
        }
        
        return isValid;
    }
    
    function markFieldError(field) {
        field.classList.add('error');
    }
    
    function markFieldValid(field) {
        field.classList.remove('error');
    }
    
    function setLoadingState(loading) {
        if (loading) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        } else {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    }
    
    function showStatus(message, type) {
        formStatus.textContent = message;
        formStatus.className = 'form-status show ' + type;
    }
    
    function hideStatus() {
        formStatus.className = 'form-status';
    }
    
})();
