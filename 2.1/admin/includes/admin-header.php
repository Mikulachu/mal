<?php
/**
 * ADMIN-HEADER.PHP - Header panelu admina
 */

// $admin i $companyName muszą być już zdefiniowane w głównym pliku (przed include header)
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Panel Admina'; ?> - <?php echo htmlspecialchars($companyName ?? 'Maltechnik'); ?></title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="icon" type="image/x-icon" href="/..//assets/img/favicon.ico">
</head>
<body>
    <div class="admin-wrapper">
        
        <!-- SIDEBAR -->
        <?php include 'admin-sidebar.php'; ?>
        
        <!-- MAIN CONTENT -->
        <div class="admin-main">
            
            <!-- TOP BAR -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="btn-menu-toggle" id="menuToggle">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h2 class="topbar-title"><?php echo $pageTitle ?? 'Panel Admina'; ?></h2>
                </div>
                
                <div class="topbar-right">
                    <!-- User menu -->
                    <div class="topbar-user">
                        <button class="btn-user-menu" id="userMenuBtn">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($admin['full_name'] ?? $admin['name'] ?? 'A', 0, 1)); ?>
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars($admin['full_name'] ?? $admin['name'] ?? 'Admin'); ?></span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <a href="/admin/settings.php" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 1v6m0 6v6"/>
                                </svg>
                                Ustawienia
                            </a>
                            <a href="/" target="_blank" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                                Zobacz stronę
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/admin/logout.php" class="dropdown-item text-danger">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Wyloguj się
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- PAGE CONTENT -->
            <main class="admin-content">