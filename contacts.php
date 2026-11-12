<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Контакты — ATHLETIC HUB</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="top-nav">
  <a href="index. php" class="logo" id="logo">
    <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
  </a>

  <nav class="nav-menu" aria-label="Главное меню">
    <a href="index.php" class="nav-link">Главная</a>
    <a href="catalog.php" class="nav-link">Каталог</a>
    <a href="cart.php" class="nav-link">Корзина</a>
    <a href="about.php" class="nav-link">О нас</a>
    <a href="contacts.php" class="nav-link active">Контакты</a>
  </nav>

  <div class="auth-area">
    <?php if (!isset($_SESSION['user_id'])): ?>
      <a href="auth.php" class="auth-btn">Войти</a>
      <a href="register.php" class="auth-btn register">Регистрация</a>
    <?php else: ?>
      <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="profile.php" class="auth-btn">Профиль</a>
      <a href="logout.php" class="auth-btn logout">Выйти</a>
    <?php endif; ?>
  </div>

  <button class="burger" id="burger" aria-label="Открыть меню">
    <i class="fa-solid fa-bars"></i>
  </button>
</header>

<div class="main-content glass">
  <header><h1>Свяжитесь с нами</h1></header>
    <section class="contacts-section">
      <p><i class="fas fa-phone"></i> +7 (705) 544-71-82</p>
      <p><i class="fas fa-envelope"></i> info@athletichub.kz</p>
      <p><i class="fas fa-map-marker-alt"></i> г. Қызылорда, проспект Нурсултана Назарбаева, 25</p>
      <p><i class="fas fa-clock"></i> Пн–Сб 09:00 – 20:00</p>
    </section>
    <footer>© 2025 ATHLETIC HUB — Все права защищены. </footer>
  </div>
</body>
</html>