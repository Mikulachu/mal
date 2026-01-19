/**
 * PORTFOLIO.JS - Logika filtrowania portfolio
  
 */

(function() {
    'use strict';
    
    // ============================================
    // ZMIENNE
    // ============================================
    
    let currentFilter = 'wszystkie';
    const itemsPerPage = 12;
    let visibleItems = itemsPerPage;
    
    // Elementy DOM
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioGrid = document.getElementById('portfolioGrid');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    const resultsCount = document.getElementById('resultsCount');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreSection = document.getElementById('loadMoreSection');
    const noResults = document.getElementById('noResults');
    const showAllBtn = document.getElementById('showAllBtn');
    
    // ============================================
    // INICJALIZACJA
    // ============================================
    
    function init() {
        if (!portfolioGrid) return;
        
        // Event listeners dla filtrów
        filterButtons.forEach(btn => {
            btn.addEventListener('click', handleFilterClick);
        });
        
        // Load more button
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadMore);
        }
        
        // Show all button (w "no results")
        if (showAllBtn) {
            showAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showAll();
            });
        }
        
        // Sprawdź URL params
        checkUrlParams();
        
        // Początkowa aktualizacja
        updateDisplay();
    }
    
    // ============================================
    // FILTROWANIE
    // ============================================
    
    function handleFilterClick(e) {
        const btn = e.currentTarget;
        const filter = btn.getAttribute('data-filter');
        
        // Aktualizuj aktywny przycisk
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Ustaw filtr
        currentFilter = filter;
        visibleItems = itemsPerPage;
        
        // Aktualizuj URL (bez przeładowania strony)
        updateUrl(filter);
        
        // Aktualizuj widok
        updateDisplay();
        
        // Scroll do gridu
        portfolioGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    function updateDisplay() {
        let visibleCount = 0;
        let totalMatchingItems = 0;
        
        // Pokaż/ukryj items
        portfolioItems.forEach((item, index) => {
            const category = item.getAttribute('data-category');
            const matches = currentFilter === 'wszystkie' || category === currentFilter;
            
            if (matches) {
                totalMatchingItems++;
                
                if (totalMatchingItems <= visibleItems) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            } else {
                item.classList.add('hidden');
            }
        });
        
        // Aktualizuj licznik
        if (resultsCount) {
            resultsCount.textContent = totalMatchingItems;
        }
        
        // Pokaż/ukryj "Load more"
        if (loadMoreSection) {
            if (totalMatchingItems > visibleItems) {
                loadMoreSection.style.display = 'block';
            } else {
                loadMoreSection.style.display = 'none';
            }
        }
        
        // Pokaż/ukryj "No results"
        if (noResults) {
            if (totalMatchingItems === 0) {
                noResults.style.display = 'block';
                portfolioGrid.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                portfolioGrid.style.display = 'grid';
            }
        }
    }
    
    function loadMore() {
        visibleItems += itemsPerPage;
        updateDisplay();
        
        // Smooth scroll do pierwszego nowo pokazanego elementu
        const firstNewItem = document.querySelector(`.portfolio-item:nth-child(${visibleItems - itemsPerPage + 1})`);
        if (firstNewItem) {
            setTimeout(() => {
                firstNewItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    }
    
    function showAll() {
        // Reset filtru do "wszystkie"
        currentFilter = 'wszystkie';
        visibleItems = itemsPerPage;
        
        // Aktualizuj przyciski
        filterButtons.forEach(btn => {
            if (btn.getAttribute('data-filter') === 'wszystkie') {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Aktualizuj URL
        updateUrl('wszystkie');
        
        // Aktualizuj widok
        updateDisplay();
        
        // Scroll
        portfolioGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // ============================================
    // URL HANDLING
    // ============================================
    
    function updateUrl(filter) {
        const url = new URL(window.location);
        
        if (filter === 'wszystkie') {
            url.searchParams.delete('kategoria');
        } else {
            url.searchParams.set('kategoria', filter);
        }
        
        window.history.pushState({}, '', url);
    }
    
    function checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('kategoria');
        
        if (category) {
            // Znajdź i aktywuj odpowiedni filtr
            const targetBtn = Array.from(filterButtons).find(btn => 
                btn.getAttribute('data-filter') === category
            );
            
            if (targetBtn) {
                filterButtons.forEach(b => b.classList.remove('active'));
                targetBtn.classList.add('active');
                currentFilter = category;
            }
        }
    }
    
    // ============================================
    // LIGHTBOX / MODAL (opcjonalnie)
    // ============================================
    
    // Obsługa kliknięć w linki "Zobacz więcej"
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('portfolio-item__link')) {
            e.preventDefault();
            const href = e.target.getAttribute('href');
            
            // Możesz tutaj dodać logikę lightboxa lub modal
            // Na razie tylko console.log
            console.log('Opening portfolio item:', href);
            
            // Przykład: można otworzyć modal z większymi zdjęciami
            // openPortfolioModal(href);
        }
    });
    
    // ============================================
    // LAZY LOADING IMAGES
    // ============================================
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('.portfolio-item__image img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // ============================================
    // SEARCH (opcjonalnie - można dodać w przyszłości)
    // ============================================
    
    function addSearchFunctionality() {
        // Przykładowa funkcja wyszukiwania
        const searchInput = document.getElementById('portfolioSearch');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                
                portfolioItems.forEach(item => {
                    const title = item.querySelector('.portfolio-item__title').textContent.toLowerCase();
                    const desc = item.querySelector('.portfolio-item__desc').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || desc.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                updateDisplay();
            });
        }
    }
    
    // ============================================
    // INIT
    // ============================================
    
    init();
    
    // Eksportuj funkcje dla użytku globalnego (opcjonalnie)
    window.PortfolioFilter = {
        setFilter: function(filter) {
            currentFilter = filter;
            updateDisplay();
        },
        showAll: showAll
    };
    
})();
