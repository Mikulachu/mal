/**
 * CALCULATOR-NEW.JS - BEZ STANDARDÓW
 * Jedna cena, rozwijane kategorie
 */

(function() {
    'use strict';
    
    console.log('=== KALKULATOR START ===');
    
    // ============================================
    // ZMIENNE
    // ============================================
    
    const calculatorContainer = document.getElementById('calculatorContainer');
    const resetBtn = document.getElementById('resetCalculator');
    const orderQuoteBtn = document.getElementById('orderQuoteBtn');
    
    let calculationData = [];
    let priceList = [];
    
    // ============================================
    // POBIERANIE CEN Z API
    // ============================================
    
    async function fetchPrices() {
        try {
            const response = await fetch('/api/get-prices.php');
            const result = await response.json();
            
            if (result.success) {
                priceList = result.data;
                console.log('✅ Załadowano', priceList.length, 'pozycji cennika');
            } else {
                // Jeśli API nie działa, użyj przykładowych danych
                console.warn('⚠️ API niedostępne, używam przykładowych danych');
                priceList = getExamplePrices();
            }
        } catch (error) {
            console.warn('⚠️ Błąd API, używam przykładowych danych');
            priceList = getExamplePrices();
        }
    }
    
    // ============================================
    // PRZYKŁADOWE DANE (gdy API nie działa)
    // ============================================
    
    function getExamplePrices() {
        return [
            // ELEWACJE
            { id: 1, name: 'Elewacja ETICS (styropian)', price_standard: 250, labor_cost: 100, category: 'elewacje', description: 'System ociepleń z wykończeniem tynkiem' },
            { id: 2, name: 'Elewacja wełna mineralna', price_standard: 280, labor_cost: 100, category: 'elewacje', description: 'System ociepleń ognioodporny' },
            { id: 3, name: 'Malowanie elewacji', price_standard: 35, labor_cost: 15, category: 'elewacje', description: 'Farba akrylowa lub silikonowa' },
            { id: 4, name: 'Tynk cienkowarstwowy', price_standard: 45, labor_cost: 25, category: 'elewacje', description: 'Strukturalny lub baranek' },
            { id: 5, name: 'Mycie elewacji', price_standard: 12, labor_cost: 8, category: 'elewacje', description: 'Ciśnieniowe z impregnacją' },
            
            // WNĘTRZA
            { id: 11, name: 'Malowanie ścian', price_standard: 20, labor_cost: 15, category: 'wnetrza', description: 'Farba lateksowa, 2 warstwy' },
            { id: 12, name: 'Gładzie gipsowe', price_standard: 30, labor_cost: 25, category: 'wnetrza', description: 'Wyrównanie ścian pod malowanie' },
            { id: 13, name: 'Panele podłogowe', price_standard: 45, labor_cost: 35, category: 'wnetrza', description: 'Z montażem i listwami' },
            { id: 14, name: 'Płytki ceramiczne - podłoga', price_standard: 80, labor_cost: 60, category: 'wnetrza', description: 'Z fugowaniem i spoinowaniem' },
            { id: 15, name: 'Płytki ceramiczne - ściana', price_standard: 70, labor_cost: 50, category: 'wnetrza', description: 'Łazienka lub kuchnia' },
            
            // REMONTY
            { id: 21, name: 'Remont mieszkania - standard', price_standard: 800, labor_cost: 400, category: 'remonty', description: 'Kompleksowe wykończenie' },
            { id: 22, name: 'Remont mieszkania - premium', price_standard: 1500, labor_cost: 600, category: 'remonty', description: 'Wysokiej jakości materiały' },
            { id: 23, name: 'Remont łazienki', price_standard: 1200, labor_cost: 500, category: 'remonty', description: 'Z hydrauliką i elektryka' },
            { id: 24, name: 'Remont kuchni', price_standard: 1000, labor_cost: 450, category: 'remonty', description: 'Z zabudową meblową' },
            
            // DODATKOWE
            { id: 31, name: 'Projekt i wizualizacja 3D', price_standard: 500, labor_cost: 0, category: 'dodatkowe', description: 'Koncepcja kolorystyczna elewacji' },
            { id: 32, name: 'Koordynacja branż', price_standard: 2000, labor_cost: 0, category: 'dodatkowe', description: 'Nadzór nad realizacją' },
            { id: 33, name: 'Pakiet premium', price_standard: 3000, labor_cost: 0, category: 'dodatkowe', description: 'Materiały najwyższej jakości' },
            { id: 34, name: 'Realizacja ekspresowa', price_standard: 5000, labor_cost: 0, category: 'dodatkowe', description: 'Priorytetowe terminy' }
        ];
    }
    
    // ============================================
    // RENDEROWANIE RZĘDÓW TABELI
    // ============================================
    
    function renderRows() {
        if (!priceList || priceList.length === 0) return;
        
        const categories = {
            'elewacje': priceList.filter(item => item.category === 'elewacje'),
            'wnetrza': priceList.filter(item => item.category === 'wnetrza'),
            'remonty': priceList.filter(item => item.category === 'remonty'),
            'dodatkowe': priceList.filter(item => item.category === 'dodatkowe')
        };
        
        Object.keys(categories).forEach(category => {
            const tbody = document.getElementById(`category${capitalize(category)}`);
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (categories[category].length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = '<td colspan="4" style="text-align: center; padding: 30px; color: #6c757d;">Brak usług w tej kategorii</td>';
                tbody.appendChild(emptyRow);
                return;
            }
            
            categories[category].forEach(item => {
                const row = document.createElement('tr');
                row.className = 'calculator-row';
                row.dataset.id = item.id;
                row.dataset.name = item.name;
                row.dataset.price = item.price_standard;
                row.dataset.laborCost = item.labor_cost || 0;
                
                const totalPrice = parseFloat(item.price_standard) + parseFloat(item.labor_cost || 0);
                
                row.innerHTML = `
                    <td class="service-checkbox">
                        <input type="checkbox" 
                               class="service-check" 
                               data-id="${item.id}" 
                               id="service_${item.id}">
                    </td>
                    <td class="service-name">
                        <label for="service_${item.id}">${item.name}</label>
                        ${item.description ? `<small>${item.description}</small>` : ''}
                    </td>
                    <td class="service-price">${formatPrice(totalPrice)} zł/m²</td>
                    <td class="service-meters">
                        <input type="number" 
                               class="meter-input" 
                               min="0" 
                               step="0.01" 
                               placeholder="0"
                               disabled
                               data-id="${item.id}">
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        });
        
        console.log('✅ Tabele wyrenderowane');
    }
    
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    // ============================================
    // EVENT HANDLERS
    // ============================================
    
    function handleCheckboxChange(e) {
        const checkbox = e.target;
        const row = checkbox.closest('tr');
        const input = row.querySelector('.meter-input');
        
        if (checkbox.checked) {
            input.disabled = false;
            input.focus();
        } else {
            input.disabled = true;
            input.value = '';
        }
        
        checkIfCanSubmit();
    }
    
    function handleInputChange(e) {
        checkIfCanSubmit();
    }
    
    function checkIfCanSubmit() {
        let hasValidService = false;
        
        document.querySelectorAll('.service-check:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const input = row.querySelector('.meter-input');
            const meters = parseFloat(input.value) || 0;
            
            if (meters > 0) {
                hasValidService = true;
            }
        });
        
        if (hasValidService) {
            orderQuoteBtn.style.display = 'inline-flex';
        } else {
            orderQuoteBtn.style.display = 'none';
        }
    }
    
    function resetCalculator() {
        document.querySelectorAll('.service-check').forEach(cb => {
            cb.checked = false;
            cb.disabled = false;
        });
        
        document.querySelectorAll('.meter-input').forEach(input => {
            input.value = '';
            input.disabled = true;
        });
        
        orderQuoteBtn.style.display = 'none';
        calculationData = [];
    }
    
    function collectCalculationData() {
        calculationData = [];
        
        document.querySelectorAll('.service-check:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const input = row.querySelector('.meter-input');
            const meters = parseFloat(input.value) || 0;
            
            if (meters > 0) {
                const id = row.dataset.id;
                const name = row.dataset.name;
                const price = parseFloat(row.dataset.price) || 0;
                const laborCost = parseFloat(row.dataset.laborCost) || 0;
                const pricePerM2 = price + laborCost;
                const total = meters * pricePerM2;
                
                calculationData.push({
                    id: id,
                    name: name,
                    meters: meters,
                    material_price: price,
                    labor_cost: laborCost,
                    price_per_m2: pricePerM2,
                    total: total,
                    standard: 'standard'
                });
            }
        });
        
        return calculationData;
    }
    
    function orderQuote() {
        const data = collectCalculationData();
        
        if (data.length === 0) {
            showNotification('Zaznacz usługi i wpisz metraż', 'error');
            return;
        }
        
        const total = data.reduce((sum, item) => sum + item.total, 0);
        
        const formData = {
            services: data,
            total: total,
            standard: 'standard'
        };
        
        localStorage.setItem('calculatorData', JSON.stringify(formData));
        window.location.href = '/wycena-email.php';
    }
    
    function attachEventListeners() {
        document.querySelectorAll('.service-check').forEach(checkbox => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });
        
        document.querySelectorAll('.meter-input').forEach(input => {
            input.addEventListener('input', handleInputChange);
        });
        
        if (resetBtn) {
            resetBtn.addEventListener('click', resetCalculator);
        }
        
        if (orderQuoteBtn) {
            orderQuoteBtn.addEventListener('click', orderQuote);
        }
    }
    
    function formatPrice(price) {
        return new Intl.NumberFormat('pl-PL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(price);
    }
    
    function showNotification(message, type = 'info') {
        alert(message);
    }
    
    // ============================================
    // INIT
    // ============================================
    
    async function init() {
        console.log('🚀 INIT kalkulatora...');
        
        await fetchPrices();
        renderRows();
        attachEventListeners();
        
        console.log('=== KALKULATOR GOTOWY ===');
    }
    
    init();
    
})();