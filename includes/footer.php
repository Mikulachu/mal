</main>
    
    <?php
    // Pobierz ustawienia firmy z bazy
    $settings = getSettings();
    $company = [
        'name' => $settings['company_name'] ?? 'Maltechnik',
        'phone' => $settings['company_phone'] ?? '+48 784 607 452',
        'email' => $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com',
        'address' => $settings['company_address'] ?? '89-600 Chojnice, Ul. Tischnera 8',
        'description' => $settings['company_description'] ?? 'Profesjonalne usługi remontowe premium. Zdejmiemy Ci stres z remontu.',
        'nip' => $settings['company_nip'] ?? '5552130861'
    ];
    ?>
    
    <!-- Footer -->
    <footer class="footer">
        
        <!-- Newsletter section -->
        <div class="footer__newsletter">
            <div class="container">
                <div class="newsletter">
                    <div class="newsletter__content">
                        <h3 class="newsletter__title">Bądź na bieżąco</h3>
                        <p class="newsletter__desc">Porady, trendy i inspiracje prosto na Twoją skrzynkę</p>
                    </div>
                    <form class="newsletter__form" id="newsletterForm">
                        <input type="email" 
                               name="email" 
                               placeholder="Twój adres e-mail" 
                               class="newsletter__input"
                               required>
                        <button type="submit" class="newsletter__btn">Zapisz się</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main footer -->
        <div class="footer__main">
            <div class="container">
                <div class="footer__grid">
                    
                    <!-- About column -->
                    <div class="footer__col">
                        <div class="footer__logo">
                            <span class="logo__text"><?php echo h($company['name']); ?></span>
                        </div>
                        <p class="footer__about">
                            <?php echo h($company['description']); ?>
                        </p>
                        <?php if (!empty($company['nip'])): ?>
                        <p class="footer__nip" style="font-size: 13px; color: var(--text-secondary); margin-top: 12px;">
                            NIP: <?php echo h($company['nip']); ?>
                        </p>
                        <?php endif; ?>
                        <div class="footer__social">
                            <a href="#" class="social__link" aria-label="Facebook">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                                </svg>
                            </a>
                            <a href="#" class="social__link" aria-label="Instagram">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                                </svg>
                            </a>
                            <a href="#" class="social__link" aria-label="YouTube">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/>
                                    <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Services column -->
                    <div class="footer__col">
                        <h4 class="footer__heading">Dla klienta</h4>
                        <ul class="footer__list">
                            <li><a href="/cennik.php">Kalkulator cen</a></li>
                            <li><a href="/realizacje.php">Portfolio realizacji</a></li>
                            <li><a href="/faq.php">Najczęstsze pytania</a></li>
                            <li><a href="/kontakt.php">Kontakt</a></li>
                        </ul>
                    </div>
                    
                    <!-- Info column -->
                    <div class="footer__col">
                        <h4 class="footer__heading">Informacje</h4>
                        <ul class="footer__list">
                            <li><a href="/o-nas.php">O nas</a></li>
                            <li><a href="/wspolpraca.php">Współpraca medialna</a></li>
                            <li><a href="/polityka-prywatnosci.php">Polityka prywatności</a></li>
                            <li><a href="/regulamin.php">Regulamin</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact column -->
                    <div class="footer__col">
                        <h4 class="footer__heading">Kontakt</h4>
                        <ul class="footer__contact">
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span><?php echo h($company['address']); ?></span>
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <a href="tel:<?php echo h($company['phone']); ?>"><?php echo h($company['phone']); ?></a>
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <a href="mailto:<?php echo h($company['email']); ?>"><?php echo h($company['email']); ?></a>
                            </li>
                        </ul>
                        <a href="/kontakt.php" class="footer__cta">Napisz do nas</a>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Bottom footer -->
        <div class="footer__bottom">
            <div class="container">
                <div class="footer__bottom-content">
                    <p class="footer__copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo h($company['name']); ?>. Wszelkie prawa zastrzeżone.
                    </p>
                    <div class="footer__legal">
                        <a href="/polityka-prywatnosci.php">Polityka prywatności</a>
                        <a href="/regulamin.php">Regulamin</a>
                    </div>
                </div>
            </div>
        </div>
        
    </footer>
    
    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
    
    <!-- Additional page scripts -->
    <?php if (isset($pageScript)) { ?>
        <script src="/assets/js/<?php echo h($pageScript); ?>"></script>
    <?php } ?>
    
</body>
</html>
