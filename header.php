<?php
// Проверяем авторизацию
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['username'] ?? 'Пользователь';
?>
<! DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #000000;
            --accent: #007BFF;
            --grad-start: #ffffff;
            --grad-end: #000000;
        }

        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Poppins', sans-serif; }
        a { text-decoration: none; color: inherit; }
        
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            background: linear-gradient(90deg, var(--grad-start), var(--grad-end));
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1200;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
            gap: 22px;
            flex: 1;
            margin: 0 30px;
        }

        .nav-link {
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 10px;
            transition: 0.2s;
            color: #000;
        }

        .nav-link:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .nav-link.active {
            background: rgba(0, 0, 0, 0.2);
        }

/* Hide cart for unauthorized users */
.nav-link.cart-link {
    <?php echo !$isLoggedIn ? 'display: none;' : 'display: inline-flex;'; ?>
}

        .auth-area {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .auth-btn {
            padding: 8px 14px;
            border-radius: 10px;
            background: #000;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(255,255,255,0.2);
            cursor: pointer;
            transition: 0.2s;
        }

        .auth-btn:hover {
            background: #333;
        }

        .auth-btn.register {
            background: #fff;
            color: #000;
        }

        .user-info {
            color: #000;
            font-weight: 600;
            margin-right: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-info strong {
            color: #007BFF;
        }

        @media (max-width: 768px) {
            .nav-menu {
                gap: 10px;
                margin: 0 15px;
            }

            .nav-link {
                font-size: 14px;
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>

<!-- ШАПКА -->
<header class="top-nav" role="banner">
    <a class="logo" href="index.php" aria-label="На главную">
        <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
    </a>

    <nav class="nav-menu" role="navigation">
        <a class="nav-link active" href="index.php">Главная</a>
        <a class="nav-link" href="catalog.html">Каталог</a>
        
        <!-- 🔴 КОРЗИНА ТОЛЬКО ДЛЯ АВТОРИЗОВАННЫХ -->
        <a class="nav-link cart-link" href="cart.php">
            <i class="fa-solid fa-shopping-cart"></i> Корзина
        </a>
        
        <a class="nav-link" href="about.html">О нас</a>
        <a class="nav-link" href="contacts.html">Контакты</a>
    </nav>

    <div class="auth-area">
        <?php if ($isLoggedIn): ?>
            <!-- АВТОРИЗОВАННЫЙ ПОЛЬЗОВАТЕЛЬ -->
            <div class="user-info">
                👤 <strong><?php echo htmlspecialchars($userName); ?></strong>
            </div>
            <a href="profile.php" class="auth-btn">Профиль</a>
            <a href="logout.php" class="auth-btn">Выход</a>
        <?php else: ?>
            <!-- НЕ АВТОРИЗОВАННЫЙ -->
            <a href="auth.php" class="auth-btn">Войти</a>
            <a href="register.php" class="auth-btn register">Регистрация</a>
        <?php endif; ?>
    </div>
</header>