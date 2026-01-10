/**
 * BEFORE-AFTER SLIDER - JavaScript (FINAL!)
  
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Znajdź wszystkie suwaki
    const sliders = document.querySelectorAll('.before-after-slider');
    
    sliders.forEach(slider => {
        initBeforeAfterSlider(slider);
    });
    
});

function initBeforeAfterSlider(slider) {
    const beforeImage = slider.querySelector('.before-image');
    const sliderHandle = slider.querySelector('.slider-handle');
    const sliderButton = slider.querySelector('.slider-button');
    const parentLink = slider.closest('a');  // Link rodzica
    
    if (!beforeImage || !sliderHandle) return;
    
    let isDragging = false;
    let hasDragged = false;  // Czy użytkownik przeciągał?
    
    // Mouse events
    sliderButton.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
    
    // Touch events (mobile)
    sliderButton.addEventListener('touchstart', startDrag, { passive: false });
    document.addEventListener('touchmove', drag, { passive: false });
    document.addEventListener('touchend', stopDrag);
    
    // Zapobiegaj kliknięciu linku podczas przeciągania
    if (parentLink) {
        parentLink.addEventListener('click', function(e) {
            if (hasDragged) {
                e.preventDefault();
                hasDragged = false;  // Reset
            }
        });
    }
    
    function startDrag(e) {
        isDragging = true;
        hasDragged = false;
        slider.style.cursor = 'ew-resize';
        e.preventDefault();
        e.stopPropagation();
    }
    
    function drag(e) {
        if (!isDragging) return;
        
        hasDragged = true;  // Użytkownik przeciągnął
        
        const rect = slider.getBoundingClientRect();
        let x;
        
        if (e.type === 'touchmove') {
            x = e.touches[0].clientX - rect.left;
        } else {
            x = e.clientX - rect.left;
        }
        
        // Ogranicz do 0-100%
        let percentage = (x / rect.width) * 100;
        percentage = Math.max(0, Math.min(100, percentage));
        
        updateSlider(percentage);
        
        if (e.type === 'touchmove') {
            e.preventDefault();
        }
    }
    
    function stopDrag(e) {
        if (isDragging) {
            setTimeout(() => {
                isDragging = false;
                slider.style.cursor = '';
            }, 50);  // Małe opóźnienie żeby kliknięcie nie przeszło
        }
    }
    
    function updateSlider(percentage) {
        // BEFORE jest na wierzchu z clip-path
        beforeImage.style.clipPath = `inset(0 ${100 - percentage}% 0 0)`;
        
        // Przesuń handle
        sliderHandle.style.left = `${percentage}%`;
    }
}

// Uruchom animację gdy użytkownik scrolluje do sekcji
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.dataset.animated) {
            entry.target.dataset.animated = 'true';
            
            const slider = entry.target.querySelector('.before-after-slider');
            if (slider) {
                // Mini animacja
                const beforeImage = slider.querySelector('.before-image');
                const sliderHandle = slider.querySelector('.slider-handle');
                
                setTimeout(() => {
                    beforeImage.style.transition = 'clip-path 0.8s ease-in-out';
                    sliderHandle.style.transition = 'left 0.8s ease-in-out';
                    
                    // Animacja: 50% → 70% → 50%
                    beforeImage.style.clipPath = 'inset(0 30% 0 0)';
                    sliderHandle.style.left = '70%';
                    
                    setTimeout(() => {
                        beforeImage.style.clipPath = 'inset(0 50% 0 0)';
                        sliderHandle.style.left = '50%';
                        
                        setTimeout(() => {
                            beforeImage.style.transition = '';
                            sliderHandle.style.transition = '';
                        }, 800);
                    }, 800);
                }, 300);
            }
        }
    });
}, { threshold: 0.3 });

// Obserwuj wszystkie portfolio items z suwakiem
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.portfolio-item .before-after-container').forEach(item => {
        observer.observe(item);
    });
});