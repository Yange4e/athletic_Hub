<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Каталог — ATHLETIC HUB</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare. com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css">
 
  <!-- TOP NAV -->
  <header class="top-nav">
     <a href="index.php" class="logo" id="logo">
    <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
  </a>

  <nav class="nav-menu" aria-label="Главное меню">
    <a href="index.php" class="nav-link">Главная</a>
    <a href="catalog.php" class="nav-link active">Каталог</a>
    <a href="cart.php" class="nav-link ">Корзина</a>
    <a href="orders.php"class="nav-link ">Мои Заказ</a>
    <a href="about.php" class="nav-link ">О нас</a>
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

  <!-- MAIN -->
  <main class="container">

    <div class="heading">
      <div>
        <h1>Каталог</h1>
        <p>Выберите раздел — я сделал фильтры и быстрый просмотр товара. </p>
      </div>

      <div class="controls">
        <div class="search">
          <i class="fa-solid fa-magnifying-glass" style="color:var(--muted)"></i>
          <input id="searchInput" placeholder="Поиск по названию..." />
        </div>

        <select id="categoryFilter" class="select">
          <option value="all">Все категории</option>
          <option value="home_fitness">Тренажёры для дома</option>
          <option value="cardio">Фитнес и кардио</option>
          <option value="strength">Тяжёлая атлетика</option>
          <option value="yoga">Йога и пилатес</option>
          <option value="accessories">Аксессуары</option>
          <option value="clothes">Одежда</option>
          <option value="shoes">Обувь</option>
          <option value="combat">Единоборства</option>
          <option value="outdoor">Туризм</option>
          <option value="swimming">Плавание</option>
          <option value="team">Командные</option>
          <option value="winter">Зимние</option>
          <option value="nutrition">Питание</option>
          <option value="bars">Турники</option>
          <option value="rehab">Реабилитация</option>
          <option value="gadgets">Гаджеты</option>
          <option value="kids">Детский спорт</option>
          <option value="climb">Скалолазание</option>
          <option value="running">Бег</option>
        </select>

        <select id="sortSelect" class="sort" title="Сортировка">
          <option value="popular">По популярности</option>
          <option value="az">По названию A–Z</option>
          <option value="za">По названию Z–A</option>
        </select>
      </div>
    </div>

    <!-- GRID -->
    <section id="grid" class="products">

<!-- === ПОЛНЫЙ КАТАЛОГ === -->

 <a href="catalog1.php" class="product-link">
<div class="product-card">
  <img src="img/1.jpg" alt="">
  <h3>Тренажёры для дома</h3>
  <p>Компактные тренажёры для тренировок дома. </p>
    <div class="sub-menu">
      <ul>
        <li>Силовые станции</li>
        <li>Многофункциональный тренажер</li>
        <li>Мини-Тренажёры</li>
        <li>Комплекс-станция</li>
        <li>Мультистанции</li>
      </ul>
    </div>
  </div>
</a>

<a href="catalog2.php" class="product-link">
<div class="product-card">
  <img src="img/2.jpg" alt="">
  <h3>Фитнес и кардио оборудование</h3>
  <p>Инвентарь для улучшения выносливости и формы.</p>
  <div class="sub-menu">
    <ul>
      <li>Беговые дорожки</li>
      <li>Эллипсы</li>
      <li>Велотренажёры</li>
      <li>Степперы</li>
      <li>Гребные тренажёры</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog3.php" class="product-link">
<div class="product-card">
  <img src="img/3.jpg" alt="">
  <h3>Тяжёлая атлетика</h3>
  <p>Инвентарь для силовых тренировок.</p>
  <div class="sub-menu">
    <ul>
      <li>Гантели</li>
      <li>Штанги</li>
      <li>Блины</li>
      <li>Пауэррэки</li>
      <li>Лавки жимовые</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog4.php" class="product-link">
<div class="product-card">
  <img src="img/4.jpg" alt="">
  <h3>Йога и пилатес</h3>
  <p>Инвентарь для растяжки и медитации.</p>
  <div class="sub-menu">
    <ul>
      <li>Коврики</li>
      <li>Йога-блоки</li>
      <li>Ремни</li>
      <li>Фитболы</li>
      <li>Пробковые бруски</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog5.php" class="product-link">
<div class="product-card">
  <img src="img/5.jpg" alt="">
  <h3>Аксессуары для тренировок</h3>
  <p>Дополнительные инструменты для развития силы.</p>
  <div class="sub-menu">
    <ul>
      <li>Скакалки</li>
      <li>Резинки</li>
      <li>Эспандеры</li>
      <li>Перчатки</li>
      <li>Утяжелители</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog6.php" class="product-link">
<div class="product-card">
  <img src="img/6.jpg" alt="">
  <h3>Одежда для тренировок</h3>
  <p>Спортивная одежда из дышащих тканей.</p>
  <div class="sub-menu">
    <ul>
      <li>Футболки</li>
      <li>Шорты</li>
      <li>Леггинсы</li>
      <li>Топы</li>
      <li>Толстовки</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog7.php" class="product-link">
<div class="product-card">
  <img src="img/7.jpg" alt="">
  <h3>Обувь спортивная</h3>
  <p>Удобная обувь для любых тренировок.</p>
  <div class="sub-menu">
    <ul>
      <li>Беговые кроссовки</li>
      <li>Тренировочные</li>
      <li>Бутсы</li>
      <li>Зимняя обувь</li>
      <li>Универсальные кеды</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog8.php" class="product-link">
<div class="product-card">
  <img src="img/8.jpg" alt="">
  <h3>Экипировка для единоборств</h3>
  <p>Всё для бокса и MMA.</p>
  <div class="sub-menu">
    <ul>
      <li>Перчатки</li>
      <li>Шлемы</li>
      <li>Бинты</li>
      <li>Лапы и макивары</li>
      <li>Боксерские мешки</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog9.php" class="product-link">
<div class="product-card">
  <img src="img/9.jpg" alt="">
  <h3>Туризм и активный отдых</h3>
  <p>Инвентарь для путешествий и походов.</p>
  <div class="sub-menu">
    <ul>
      <li>Палатки</li>
      <li>Спальники</li>
      <li>Рюкзаки</li>
      <li>Горелки</li>
      <li>Треккинг палки</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog10.php" class="product-link">
<div class="product-card">
  <img src="img/10.jpg" alt="">
  <h3>Плавание</h3>
  <p>Снаряжение для бассейна и водных видов спорта.</p>
  <div class="sub-menu">
    <ul>
      <li>Очки</li>
      <li>Ласты</li>
      <li>Шапочки</li>
      <li>Доски</li>
      <li>Лопатки</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog11.php" class="product-link">
<div class="product-card">
  <img src="img/11.jpg" alt="">
  <h3>Командные виды спорта</h3>
  <p>Спортинвентарь для игр и команд.</p>
  <div class="sub-menu">
    <ul>
      <li>Футбольные мячи</li>
      <li>Баскетбольные</li>
      <li>Волейбольные</li>
      <li>Сетки</li>
      <li>Форма</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog12.php" class="product-link">
<div class="product-card">
  <img src="img/12.jpg" alt="">
  <h3>Зимние виды спорта</h3>
  <p>Инвентарь для активной зимы.</p>
  <div class="sub-menu">
    <ul>
      <li>Лыжи</li>
      <li>Коньки</li>
      <li>Сноуборды</li>
      <li>Защита</li>
      <li>Термокостюмы</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog13.php" class="product-link">
<div class="product-card">
  <img src="img/13.jpg" alt="">
  <h3>Питание и добавки</h3>
  <p>Спортивное питание для роста и восстановления.</p>
  <div class="sub-menu">
    <ul>
      <li>Протеины</li>
      <li>BCAA</li>
      <li>Витамины</li>
      <li>Батончики</li>
      <li>Креатин</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog14.php" class="product-link">
<div class="product-card">
  <img src="img/14.jpg" alt="">
  <h3>Турники и брусья</h3>
  <p>Оборудование для тренировки дома.</p>
  <div class="sub-menu">
    <ul>
      <li>Настенные</li>
      <li>Напольные</li>
      <li>Потолочные</li>
      <li>Детские</li>
      <li>Профессиональные</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog15.php" class="product-link">
<div class="product-card">
  <img src="img/15.jpg" alt="">
  <h3>Растяжка и реабилитация</h3>
  <p>Инвентарь для восстановления и растяжки.</p>
  <div class="sub-menu">
    <ul>
      <li>Массажные ролики</li>
      <li>Мячики</li>
      <li>Терапевтические ленты</li>
      <li>Пояса</li>
      <li>Комплекты растяжки</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog16.php" class="product-link">
<div class="product-card">
  <img src="img/16.jpg" alt="">
  <h3>Умные гаджеты</h3>
  <p>Технологичные устройства для спорта.</p>
  <div class="sub-menu">
    <ul>
      <li>Фитнес-браслеты</li>
      <li>Смарт-часы</li>
      <li>Пульсометры</li>
      <li>Наушники</li>
      <li>GPS-датчики</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog17.php" class="product-link">
<div class="product-card">
  <img src="img/17.jpg" alt="">
  <h3>Детский спорт</h3>
  <p>Инвентарь для маленьких спортсменов.</p>
  <div class="sub-menu">
    <ul>
      <li>Мини-тренажёры</li>
      <li>Детская форма</li>
      <li>Мячи</li>
      <li>Игровые наборы</li>
      <li>Защита</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog18.php" class="product-link">
<div class="product-card">
  <img src="img/18.jpg" alt="">
  <h3>Скалолазание и кроссфит</h3>
  <p>Спортивный инвентарь для профессионалов.</p>
  <div class="sub-menu">
    <ul>
      <li>Магнезия</li>
      <li>Канаты</li>
      <li>Пояса</li>
      <li>Турники</li>
      <li>Grip-тренажёры</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog19.php" class="product-link">
<div class="product-card">
  <img src="img/19.jpg" alt="">
  <h3>Бег и лёгкая атлетика</h3>
  <p>Инвентарь для бегунов и легкоатлетов.</p>
  <div class="sub-menu">
    <ul>
      <li>Поясные сумки</li>
      <li>Лёгкие кроссовки</li>
      <li>Спортивные очки</li>
      <li>Бутылки</li>
      <li>Комплекты экипировки</li>
    </ul>
  </div>
</div>
</a>

<a href="catalog20.php" class="product-link">
<div class="product-card">
  <img src="img/20.jpg" alt="">
  <h3>Тренировочные комплекты</h3>
  <p>Готовые наборы для тренировок.</p>
  <div class="sub-menu">
    <ul>
      <li>Комплекты с гантелями</li>
      <li>Наборы для йоги</li>
      <li>Пилатес-комплекты</li>
      <li>Фитнес-наборы</li>
      <li>Резинки/петли</li>
    </ul>
  </div>
</div>
</a>

  </section>
  </main>

  <!-- Scripts -->
  <script>

(function loadGSAP(){
  if (!window.gsap) {
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js';
    s.onload = () => console.log('GSAP loaded');
    document.head.appendChild(s);
  }
})();

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const sortSelect = document.getElementById('sortSelect');
  const grid = document.getElementById('grid');
  // changed: use product-link containers so links remain intact
  const items = Array.from(document.querySelectorAll('.product-link'));

  function updateGrid(){
    const q = (searchInput.value || '').toLowerCase().trim();
    const cat = categoryFilter.value;
    items.forEach(item => {
      const title = (item.querySelector('h3')?.innerText || '').toLowerCase();
      const desc = (item.querySelector('p')?.innerText || '').toLowerCase();
      const matchQ = !q || title.includes(q) || desc.includes(q);

      // category filtering: read from data-category attribute on link
      const linkCat = item.dataset.category || 'all';
      const matchC = (cat === 'all') || (linkCat === cat);

      item.style.display = (matchQ && matchC) ? 'block' : 'none';
    });

    sortGrid();
  }

  function sortGrid(){
    const mode = sortSelect.value;
    const visibleItems = items.filter(i => i.style.display !== 'none');
    const sorted = visibleItems.slice();

    if (mode === 'az') {
      sorted.sort((a,b) => a.querySelector('h3').innerText.localeCompare(b.querySelector('h3').innerText, 'ru'));
    } else if (mode === 'za') {
      sorted.sort((a,b) => b.querySelector('h3').innerText.localeCompare(a.querySelector('h3').innerText, 'ru'));
    } else {
      // keep original order
    }
    // Append sorted items back to grid (preserves links)
    sorted.forEach(el => grid.appendChild(el));
  }

  searchInput.addEventListener('input', () => updateGrid());
  categoryFilter.addEventListener('change', () => updateGrid());

  sortSelect.addEventListener('change', () => sortGrid());

  document.getElementById('logo').addEventListener('click', () => {
    window.location.href = 'index.php';
  });

  const burger = document.getElementById('burger');
  burger.addEventListener('click', () => {
    const menu = document.querySelector('.nav-menu');
    if (menu.style.display === 'flex') menu.style.display = 'none';
    else menu.style.display = 'flex';
  });

  updateGrid();
});
  </script>
</body>
</html>