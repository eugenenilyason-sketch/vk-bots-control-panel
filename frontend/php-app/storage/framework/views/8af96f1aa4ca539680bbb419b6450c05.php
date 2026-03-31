<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'VK Neuro-Agents'); ?></title>
    <link rel="stylesheet" href="/styles.css">
    <style>
        /* ===== DESKTOP (>1024px) ===== */
        @media (min-width: 1025px) {
            .sidebar {
                display: block !important;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 260px;
                background: var(--bg-primary);
                border-right: 1px solid var(--border-color);
                overflow-y: auto;
                z-index: 100;
            }
            .main-content {
                margin-left: 260px;
                padding: 24px;
                min-height: 100vh;
            }
            .mobile-header,
            .mobile-overlay,
            .close-btn {
                display: none !important;
            }
        }
        
        /* ===== MOBILE (≤1024px) ===== */
        @media (max-width: 1024px) {
            .sidebar {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px;
                background: var(--bg-primary);
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0) !important;
            }
            .mobile-header {
                display: flex !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 60px;
                background: var(--bg-secondary);
                border-bottom: 1px solid var(--border-color);
                z-index: 999;
                align-items: center;
                justify-content: space-between;
                padding: 0 16px;
            }
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 998;
            }
            .mobile-overlay.show {
                display: block;
            }
            .main-content {
                margin-left: 0;
                padding: 24px;
                padding-top: 84px;
                min-height: 100vh;
            }
            .menu-btn,
            .close-btn {
                background: none;
                border: none;
                color: var(--text-primary);
                font-size: 24px;
                cursor: pointer;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <header class="mobile-header">
        <button class="menu-btn" onclick="openMenu()">☰</button>
        <div style="font-weight: 700;">VK Neuro-Agents</div>
        <div style="width: 40px;"></div>
    </header>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="overlay" onclick="closeMenu()"></div>
    
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <button class="close-btn" onclick="closeMenu()">✕</button>
        
        <div class="sidebar-content">
            <div class="menu-logo">
                <span class="menu-logo-icon">🤖</span>
                <div class="menu-logo-text">VK Neuro-Agents</div>
            </div>
            <nav class="nav-menu">
                <a href="/dashboard" class="nav-item <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
                    <span class="nav-icon">📊</span><span>Dashboard</span>
                </a>
                <a href="/bots" class="nav-item <?php echo e(request()->is('bots') ? 'active' : ''); ?>">
                    <span class="nav-icon">🤖</span><span>Боты</span>
                </a>
                <a href="/payments" class="nav-item <?php echo e(request()->is('payments') ? 'active' : ''); ?>">
                    <span class="nav-icon">💳</span><span>Оплата</span>
                </a>
                <a href="/settings" class="nav-item <?php echo e(request()->is('settings') ? 'active' : ''); ?>">
                    <span class="nav-icon">⚙️</span><span>Настройки</span>
                </a>
                <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                <a href="/admin" class="nav-item <?php echo e(request()->is('admin') ? 'active' : ''); ?>">
                    <span class="nav-icon">🛡️</span><span>Админка</span>
                </a>
                <?php endif; ?>
            </nav>
            <div class="menu-footer">
                <form action="/logout" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-logout">
                        <span>🚪</span><span>Выйти</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    
    <script>
        function openMenu() {
            document.getElementById('sidebar').classList.add('show');
            document.getElementById('overlay').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMenu() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('overlay').classList.remove('show');
            document.body.style.overflow = '';
        }
    </script>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>