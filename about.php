<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>О нас — ATHLETIC HUB</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2? family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="top-nav">
     <a href="index.php" class="logo" id="logo">
    <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
  </a>

  <nav class="nav-menu" aria-label="Главное меню">
    <a href="index.php" class="nav-link">Главная</a>
    <a href="catalog.php" class="nav-link">Каталог</a>
    <a href="cart.php" class="nav-link ">Корзина</a>
    <a href="orders.php"class="nav-link ">Мои Заказ</a>
    <a href="about. php" class="nav-link active">О нас</a>
    <a href="contacts.php" class="nav-link">Контакты</a>
  </nav>

  <div class="auth-area">
    <?php if (! isset($_SESSION['user_id'])): ?>
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

  <div class="main-content">
    <h1>О нас</h1>
    <section class="">
      <p><strong>ATHLETIC HUB</strong> — это студенческий проект, объединяющий спорт, технологии и креатив. </p>
      <p>Мы — команда студентов 3 курса университета <strong>Қорқыт Ата</strong>, обучающихся по направлению <em>Информационные технологии</em>.</p>
      <p>Наш проект создан для демонстрации навыков веб-разработки, дизайна и организации командной работы. Каждый участник внёс вклад: от фронтенда до базы данных и UX-дизайна.</p>
    </section>
<section class="team">

  <div class="team-card">
    <img src="img/Айдын.jpeg" alt="Айдын">
    <h3>Рысмағанбетов Айдын</h3>
    <p>Создаёт стиль, интерфейс и визуальную часть сайта, чтобы всё выглядело красиво и удобно для пользователей.</p>

    <div class="hover-info">
      <h4>Рысмағанбетов Айдын — Frontend</h4>
      <p>Студент 3 курса университета Қорқыт Ата, направление "Информационные технологии". 
      Отвечает за интерфейс, визуальную часть, анимацию и адаптивный стиль сайта. 
      Работал над дизайном, структурой и стилем ATHLETIC HUB.</p>
    </div>
  </div>

  <div class="team-card">
    <img src="img/1000163848_0x0_1080x1440.png" alt="Әсет">
    <h3>Жасыбаев Әсет</h3>
    <p>Отвечает за сервер, базу данных и внутреннюю логику — то, что делает сайт функциональным и надёжным.</p>

    <div class="hover-info">
      <h4>Жасыбаев Әсет — Backend</h4>
      <p>Студент 3 курса университета Қорқыт Ата. 
      Отвечает за серверную часть сайта, подключение к базе данных и систему авторизации.
      Разрабатывает надёжную структуру данных. </p>
    </div>
  </div>

  <div class="team-card">
    <img src="img/trello7692822330489030547_0x0_1080x1440.png" alt="Әліби">
    <h3>Сарыбай Әліби</h3>
    <p>Умеет совмещать работу frontend и backend, создавая полноценные и интегрированные веб-приложения.</p>

    <div class="hover-info">
      <h4>Сарыбай Әліби — Fullstack</h4>
      <p>Совмещает работу фронтенда и бэкенда, соединяя дизайн и функциональность. 
      Работал над взаимодействием сайта с сервером и оптимизацией проекта.</p>
    </div>
  </div>

  <div class="team-card">
    <img src="img/trello7223793713626048069.jpg" alt="Нұрмухаммед">
    <h3>Романқұлов Нұрмухаммед</h3>
    <p>Планирует, распределяет задачи и следит за качеством выполнения проекта — чтобы всё было вовремя и идеально.</p>

    <div class="hover-info">
      <h4>Романқұлов Нұрмухаммед — Менеджер проекта</h4>
      <p>Руководил проектом и командой, распределял задачи и следил за сроками.
      Контролировал все этапы и обеспечивал успешную реализацию проекта. </p>
    </div>
  </div>

</section>

  </div>
  <footer><p>© 2025 ATHLETIC HUB.  Все права защищены.</p></footer>
</body>
</html>