/**
 * FAQ.JS - Logika accordion i wyszukiwania
  
 */

(function() {
    'use strict';
    
    // ============================================
    // ZMIENNE
    // ============================================
    
    const accordionItems = document.querySelectorAll('.accordion__item');
    const searchInput = document.getElementById('faqSearch');
    
    // ============================================
    // INICJALIZACJA
    // ============================================
    
    function init() {
        // Event listeners dla accordion
        accordionItems.forEach(item => {
            const header = item.querySelector('.accordion__header');
            if (header) {
                header.addEventListener('click', () => toggleAccordion(item));
            }
        });
        
        // Event listener dla search
        if (searchInput) {
            searchInput.addEventListener('input', handleSearch);
            
            // Opcjonalnie: clear button
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    clearSearch();
                }
            });
        }
        
        // Sprawdź anchor w URL
        checkUrlAnchor();
    }
    
    // ============================================
    // ACCORDION
    // ============================================
    
    function toggleAccordion(item) {
        const isActive = item.classList.contains('active');
        
        // Zamknij wszystkie (opcjonalnie - zakomentuj jeśli chcesz mieć wiele otwartych)
        // accordionItems.forEach(i => i.classList.remove('active'));
        
        // Toggle clicked item
        if (isActive) {
            item.classList.remove('active');
        } else {
            item.classList.add('active');
            
            // Scroll do item (opcjonalnie)
            setTimeout(() => {
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 300);
        }
    }
    
    function openAccordion(item) {
        item.classList.add('active');
    }
    
    function closeAccordion(item) {
        item.classList.remove('active');
    }
    
    function closeAllAccordions() {
        accordionItems.forEach(item => closeAccordion(item));
    }
    
    function openAllAccordions() {
        accordionItems.forEach(item => openAccordion(item));
    }
    
    // ============================================
    // SEARCH
    // ============================================
    
    function handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            clearSearch();
            return;
        }
        
        let resultsFound = false;
        
        accordionItems.forEach(item => {
            const title = item.querySelector('.accordion__title').textContent.toLowerCase();
            const content = item.querySelector('.accordion__content p').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || content.includes(searchTerm)) {
                // Znaleziono match
                item.classList.remove('hidden');
                item.classList.add('highlighted');
                openAccordion(item);
                
                // Highlight search term
                highlightSearchTerm(item, searchTerm);
                
                resultsFound = true;
                
                // Usuń highlight po animacji
                setTimeout(() => {
                    item.classList.remove('highlighted');
                }, 600);
            } else {
                // Nie znaleziono match
                item.classList.add('hidden');
                closeAccordion(item);
            }
        });
        
        // Pokaż/ukryj no results
        showNoResults(!resultsFound);
    }
    
    function clearSearch() {
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Pokaż wszystkie items
        accordionItems.forEach(item => {
            item.classList.remove('hidden');
            item.classList.remove('highlighted');
            removeHighlight(item);
            closeAccordion(item);
        });
        
        showNoResults(false);
    }
    
    function highlightSearchTerm(item, term) {
        const title = item.querySelector('.accordion__title');
        const content = item.querySelector('.accordion__content p');
        
        // Zapisz oryginalne teksty jeśli jeszcze nie ma
        if (!title.dataset.originalText) {
            title.dataset.originalText = title.textContent;
        }
        if (!content.dataset.originalText) {
            content.dataset.originalText = content.textContent;
        }
        
        // Highlight w title
        const titleText = title.dataset.originalText;
        const titleHighlighted = highlightText(titleText, term);
        title.innerHTML = titleHighlighted;
        
        // Highlight w content
        const contentText = content.dataset.originalText;
        const contentHighlighted = highlightText(contentText, term);
        content.innerHTML = contentHighlighted;
    }
    
    function removeHighlight(item) {
        const title = item.querySelector('.accordion__title');
        const content = item.querySelector('.accordion__content p');
        
        // Przywróć oryginalne teksty
        if (title.dataset.originalText) {
            title.textContent = title.dataset.originalText;
        }
        if (content.dataset.originalText) {
            content.innerHTML = content.dataset.originalText;
        }
    }
    
    function highlightText(text, term) {
        const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
    
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function showNoResults(show) {
        let noResults = document.querySelector('.faq-no-results');
        
        if (!noResults && show) {
            // Utwórz element no results
            noResults = document.createElement('div');
            noResults.className = 'faq-no-results';
            noResults.innerHTML = `
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <h3>Nie znaleziono wyników</h3>
                <p>Spróbuj innego słowa kluczowego lub <a href="#" id="clearSearchBtn">wyczyść wyszukiwanie</a>.</p>
            `;
            
            const faqSection = document.querySelector('.faq-section .container');
            if (faqSection) {
                faqSection.appendChild(noResults);
                
                // Event listener dla clear button
                const clearBtn = noResults.querySelector('#clearSearchBtn');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearSearch();
                    });
                }
            }
        }
        
        if (noResults) {
            if (show) {
                noResults.classList.add('show');
            } else {
                noResults.classList.remove('show');
            }
        }
    }
    
    // ============================================
    // URL ANCHOR
    // ============================================
    
    function checkUrlAnchor() {
        const hash = window.location.hash;
        if (hash) {
            // Sprawdź czy to pytanie
            const targetItem = document.querySelector(hash);
            if (targetItem && targetItem.classList.contains('accordion__item')) {
                setTimeout(() => {
                    openAccordion(targetItem);
                    targetItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }
    }
    
    // ============================================
    // KEYBOARD SHORTCUTS (opcjonalnie)
    // ============================================
    
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K = focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Escape = clear search
        if (e.key === 'Escape' && searchInput === document.activeElement) {
            clearSearch();
            searchInput.blur();
        }
    });
    
    // ============================================
    // HELPERS
    // ============================================
    
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // ============================================
    // INIT
    // ============================================
    
    init();
    
    // Eksportuj funkcje globalnie (opcjonalnie)
    window.FAQUtils = {
        openAll: openAllAccordions,
        closeAll: closeAllAccordions,
        search: function(term) {
            if (searchInput) {
                searchInput.value = term;
                searchInput.dispatchEvent(new Event('input'));
            }
        },
        clear: clearSearch
    };
    
})();
