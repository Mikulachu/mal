<?php
/**
 * HEADER - wspólny nagłówek dla wszystkich stron
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pobierz ustawienia
$settings = getSettings();
$companyName = $settings['company_name'] ?? 'Maltechnik';
$companyPhone = $settings['company_phone'] ?? '+48 784 607 452';
$companyEmail = $settings['company_email'] ?? 'maltechnik.chojnice@gmail.com';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Profesjonalne elewacje i wykończenia wnętrz premium. Zdejmiemy Ci stres z remontu - kompleksowa obsługa od A do Z.">
    <meta name="keywords" content="elewacje, malowanie elewacji, wykończenia wnętrz, remonty premium, malarz Chojnice">
    <meta name="author" content="<?php echo h($companyName); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo h($companyName); ?> - Zdejmiemy Ci stres z remontu">
    <meta property="og:description" content="Profesjonalne usługi remontowe premium. Elewacje, wnętrza, kompleksowe remonty.">
    <meta property="og:type" content="website">
    
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' - ' : ''; ?><?php echo h($companyName); ?></title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
</head>
<body>
    
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header__wrapper">
                
                <!-- Logo -->
                <div class="header__logo">
                    <a href="/">
                        <span class="logo__text"><?php echo h($companyName); ?></span>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="header__nav" id="mainNav">
                    <ul class="nav__list">
                        <li class="nav__item">
                            <a href="/" class="nav__link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                                Start
                            </a>
                        </li>
                        <!-- <li class="nav__item">
                            <a href="/oferta.php" class="nav__link <?php echo basename($_SERVER['PHP_SELF']) == 'oferta.php' ? 'active' : ''; ?>">
                                Oferta
                            </a>
                        </li>-->
                        <li class="nav__item">
                            <a href="/cennik.php" class="nav__link <?php echo basename($_SERVER['PHP_SELF']) == 'cennik.php' ? 'active' : ''; ?>">
                                Kalkulator cen
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="/realizacje.php" class="nav__link <?php echo basename($_SERVER['PHP_SELF']) == 'realizacje.php' ? 'active' : ''; ?>">
                                Realizacje
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="/faq.php" class="nav__link <?php echo basename($_SERVER['PHP_SELF']) == 'faq.php' ? 'active' : ''; ?>">
                                FAQ
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="/o-nas.php" class="nav__link <?php echo basename($_SERVER['PHP_SELF']) == 'o-nas.php' ? 'active' : ''; ?>">
                                O nas
                            </a>
                        </li>
                        <li class="nav__item nav__item--cta">
                            <a href="/kontakt.php" class="nav__link nav__link--cta">
                                Kontakt
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Mobile menu toggle -->
                <button class="header__burger" id="burgerBtn" aria-label="Menu">
                    <span class="burger__line"></span>
                    <span class="burger__line"></span>
                    <span class="burger__line"></span>
                </button>
                
            </div>
        </div>
    </header>
    
    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav__wrapper">
            <ul class="mobile-nav__list">
                <li><a href="/">Start</a></li>
                 <!--  <li><a href="/oferta.php">Oferta</a></li>-->
                <li><a href="/cennik.php">Kalkulator cen</a></li>
                <li><a href="/realizacje.php">Realizacje</a></li>
                <li><a href="/faq.php">FAQ</a></li>
                <li><a href="/o-nas.php">O nas</a></li>
                <li><a href="/kontakt.php" class="cta">Kontakt</a></li>
            </ul>
            <div class="mobile-nav__footer">
                <a href="tel:<?php echo h($companyPhone); ?>" class="mobile-nav__phone"><?php echo h($companyPhone); ?></a>
                <a href="mailto:<?php echo h($companyEmail); ?>" class="mobile-nav__email"><?php echo h($companyEmail); ?></a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="main-content">