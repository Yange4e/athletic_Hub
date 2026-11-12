<?php
session_start();
?>
<! doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ATHLETIC HUB — Главная</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare. com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2? family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />

  <!-- NAV -->
  <header class="top-nav" role="banner">
    <a class="logo" href="index.php" aria-label="На главную"><i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span></a>

    <nav class="nav-menu" role="navigation" aria-label="Основное меню">
      <a class="nav-link active" href="index.php">Главная</a>
      <a class="nav-link" href="catalog.php">Каталог</a>
      <a class="nav-link" href="cart.php">Корзина</a>
      <a href="orders.php"class="nav-link ">Мои Заказы</a>
      <a class="nav-link" href="about.php">О нас</a>
      <a class="nav-link" href="contacts.php">Контакты</a>
    </nav>

    <div style="display:flex;gap:12px;align-items:center">
      <button class="burger" id="burgerBtn" aria-label="Открыть меню"><i class="fa-solid fa-bars"></i></button>
      <div class="auth-area">
        <?php if(isset($_SESSION['user_id'])): ?>
          <!-- АВТОРИЗОВАННЫЙ ПОЛЬЗОВАТЕЛЬ -->
          <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Пользователь'); ?></span>
          <a href="profile.php" class="auth-btn">Профиль</a>
          <a href="logout.php" class="auth-btn">Выйти</a>
        <?php else: ?>
          <!-- НЕАВТОРИЗОВАННЫЙ ПОЛЬЗОВАТЕЛЬ -->
          <a href="auth.php" class="auth-btn">Войти</a>
          <a href="register.php" class="auth-btn register">Регистрация</a>
        <?php endif; ?>
      </div>
      <div class="mobile-menu" id="mobileMenu">
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="cart.php">Корзина</a>
        <a href="about.php">О нас</a>
        <a href="contacts.php">Контакты</a>
      </div>
    </div>
  </header>

  <!-- MAIN -->
  <main>
    <!-- HERO -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-left">
        <img class="hero-logo" src="img/Gemini_Generated_Image_bm0arlbm0arlbm0a.png" alt="ATHLETIC HUB логотип ">
      </div>
      <div class="hero-right">
        <h1 id="hero-title">Добро пожаловать в ATHLETIC HUB</h1>
        <p class="lead">Лучшее — для твоих тренировок.  Подборки, тренды и персональные рекомендации.</p>

        <div class="controls" style="margin-top:14px">
          <div class="search" role="search">
            <i class="fa-solid fa-magnifying-glass" style="margin-right:10px;color:var(--muted)"></i>
            <input id="searchInput" placeholder="Поиск по названию..." aria-label="Поиск">
          </div>

          <select id="categoryFilter" class="filter" aria-label="Фильтр по категории">
            <option value="all">Все категории</option>
            <option value="sila">Сила</option>
            <option value="cardio">Кардио</option>
            <option value="yoga">Йога</option>
            <option value="aksessuary">Аксессуары</option>
            <option value="obuv">Обувь</option>
          </select>

          <select id="sortSelect" class="sort" aria-label="Сортировка">
            <option value="popular">По популярности</option>
            <option value="alpha">По алфавиту</option>
          </select>
        </div>

        <div style="margin-top:12px" class="hero-ctas">
          <a class="btn btn-ghost" href="#trends"><i class="fa-solid fa-fire"></i> Тренды недели</a>
        </div>
      </div>
    </section>

    <!-- 🔥 НОВАЯ СЕКЦИЯ ПОСТЕРА 🔥 -->
    <section class="poster-section">
      <img src="img/Gemini_Generated_Image_lzv36elzv36elzv3.png" alt="Рекламный баннер">
    </section>

    <!-- Тренды недели -->
    <section id="trends" class="section">
      <h2>🔥 Тренды недели</h2>
      <div class="product-slider" id="trendsSlider" role="region" aria-label="Тренды недели">
        <!-- Карточки: заменяй src на свои изображения -->
        <article class="product-card" data-name="Кроссовки для бега" data-tag="obuv" data-pop="120">
          <img src="https://cdn.intertop.com/load/mp516977/412x550/MAIN.jpg" alt="Кроссовки">
          <h3>Кроссовки для бега</h3>
          <p class="meta">Лёгкие, для длинных дистанций</p>
          <div class="card-controls">
            <button class="view-btn" title="Просмотр"><i class="fa-solid fa-eye"></i></button>
          </div>
        </article>

        <article class="product-card" data-name="Регулируемые гантели" data-tag="sila" data-pop="95">
          <img src="https://images.satu.kz/161813259_w640_h640_161813259.jpg" alt="Гантели">
          <h3>Регулируемые гантели</h3>
          <p class="meta">Набор весов от 2 до 20 кг</p>
          <div class="card-controls">
            <button class="view-btn"><i class="fa-solid fa-eye"></i></button>
          </div>
        </article>

        <article class="product-card" data-name="Беговая дорожка складная" data-tag="cardio" data-pop="87">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQEbsow0B1l7D7U9sLS7J3Fu3OsYJvQAUWs5g&s" alt="Беговая дорожка">
          <h3>Беговая дорожка</h3>
          <p class="meta">Мощный мотор и складная конструкция</p>
          <div class="card-controls">
            <button class="view-btn"><i class="fa-solid fa-eye"></i></button>
          </div>
        </article>

        <article class="product-card" data-name="Набор эспандеров" data-tag="aksessuary" data-pop="56">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1AQ0Mo4bfaGyIS8rbOE9NlW0sjaBbpq_YyQ&s" alt="Эспандеры">
          <h3>Набор эспандеров</h3>
          <p class="meta">Разные уровни сопротивления</p>
          <div class="card-controls">
            <button class="view-btn"><i class="fa-solid fa-eye"></i></button>
          </div>
        </article>
      </div>
    </section>

    <!-- Популярные категории -->
    <section class="section">
      <h2>Популярные категории</h2>
      
      <div class="categories-grid">
        <a href="catalog3.php">
        <div class="category" data-cat="sila">
          <img src="img/3.jpg" alt="Тяжёлая атлетика">
          <h4>Тяжёлая атлетика</h4>
          <div class="sub-menu" aria-hidden="true">
            <ul>
              <li>Станции силовые</li>
              <li>Штанги и блины</li>
              <li>Гири и гири-индивиду</li>
              <li>Скамьи и стойки</li>
              <li>Аксессуары для силы</li>
            </ul>
          </div>
        </div>
        </a>

        <a href="catalog2.php">
        <div class="category" data-cat="cardio">
          <img src="img/2.jpg" alt="Кардио">
          <h4>Кардио</h4>
          <div class="sub-menu" aria-hidden="true">
            <ul>
              <li>Беговые дорожки</li>
              <li>Велотренажёры</li>
              <li>Эллипсы</li>
              <li>Степперы</li>
              <li>Компактный кардио</li>
            </ul>
          </div>
        </div>
        </a>

        <a href="catalog4.php">
        <div class="category" data-cat="yoga">
          <img src="img/4.jpg" alt="Йога">
          <h4>Йога и пилатес</h4>          
          <div class="sub-menu" aria-hidden="true">
            <ul>
              <li>Коврики</li>
              <li>Блоки и ремни</li>
              <li>Фитболы</li>
              <li>Одежда для йоги</li>
              <li>Аксессуары</li>
            </ul>
          </div>
        </div>
        </a>
        <a href="catalog5.php">
        <div class="category" data-cat="aksessuary">
          <img src="img/5.jpg" alt="Аксессуары">
          <h4>Аксессуары</h4>
          <div class="sub-menu" aria-hidden="true">
            <ul>
              <li>Ремни и пояса</li>
              <li>Эспандеры</li>
              <li>Скакалки</li>
              <li>Ролики</li>
              <li>Массаж</li>
            </ul>
          </div>
        </div>
        </a>
      </div>
    </section>

    <!-- Преимущества -->
    <section class="section perks">
      <div class="perk"><i class="fa-solid fa-truck-fast"></i><h4>Доставка по Казахстану</h4><p>Быстрая отправка и надёжная упаковка. </p></div>
      <div class="perk"><i class="fa-solid fa-shield-halved"></i><h4>Гарантия качества</h4><p>Только проверенные бренды и сертификаты.</p></div>
      <div class="perk"><i class="fa-solid fa-headset"></i><h4>Поддержка 7/24</h4><p>Поможем выбрать и ответим на вопросы.</p></div>
    </section>

    <footer>© 2025 ATHLETIC HUB — Все права защищены. </footer>
  </main>

<!-- МОДАЛ -> карточка товара -->
<div id="productModal" class="modal" aria-hidden="true">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="modal-close" aria-label="Закрыть">&times;</button>
    <img id="modalImg" src="" alt="">
    <div class="modal-info">
      <h3 id="modalTitle"></h3>
      
      <div class="modal-rating">
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <span class="rating-text">4.5 / 5. 0</span>
      </div>

      <p id="modalMeta"></p>

      <div class="modal-features">
        <div class="modal-feature">
          <i class="fa-solid fa-check"></i>
          <span>Гарантия: 2 года</span>
        </div>
        <div class="modal-feature">
          <i class="fa-solid fa-check"></i>
          <span>Доставка: бесплатная</span>
        </div>
        <div class="modal-feature">
          <i class="fa-solid fa-check"></i>
          <span>Установка: включена</span>
        </div>
      </div>

      <a id="modalGo" href="#" class="btn btn-ghost">Открыть в каталоге</a>
      </div>
    </div>
  </div>
</div>

  <!-- Скрипты -->
  <script>
  // GSAP динамически (ускорённые анимации)
  (function loadGSAP(){ if(! window.gsap){ const s=document.createElement('script'); s.src='https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min. js'; document.head.appendChild(s);} })();

  // Управление мобильным меню
  const burgerBtn = document.getElementById('burgerBtn'), mobileMenu = document.getElementById('mobileMenu');
  burgerBtn?. addEventListener('click', ()=> mobileMenu.classList.toggle('open'));

  // Фильтр / Поиск / Сортировка
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const sortSelect = document.getElementById('sortSelect');
  const cards = Array.from(document.querySelectorAll('.product-card'));

  function filterCatalog(){
    const q = searchInput.value.trim(). toLowerCase();
    const cat = categoryFilter.value;
    const sort = sortSelect.value;

    let visible = cards. filter(card=>{
      const title = card.dataset.name.toLowerCase();
      const tag = card.dataset.tag || '';
      const okSearch = ! q || title.includes(q);
      const okCat = cat === 'all' || tag === cat;
      return okSearch && okCat;
    });

    // сортировка
    if(sort === 'alpha') visible.sort((a,b)=> a.dataset.name.localeCompare(b.dataset.name));
    else visible.sort((a,b)=> (parseInt(b.dataset.pop||0) - parseInt(a.dataset.pop||0)));

    // рендер (set display)
    cards.forEach(c=> c.style.display='none');
    visible.forEach((c,i)=> {
      c.style.display = 'block';
      // небольшая анимация появления
      c.style.opacity = 0;
      c.style. transform = 'translateY(8px)';
      setTimeout(()=>{ c.style.opacity = 1; c.style.transform = ''; }, i*30);
    });
  }

  searchInput?. addEventListener('input', filterCatalog);
  categoryFilter?.addEventListener('change', filterCatalog);
  sortSelect?.addEventListener('change', filterCatalog);

  // Избранное (localStorage)
  const STORAGE_KEY = 'athletic_favorites';
  const loadFavs = ()=> JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
  const saveFavs = arr => localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));

  function updateFavIcons(){
    document.querySelectorAll('.product-card').forEach(card=>{
      const name = card.dataset.name;
      const btn = card.querySelector('.fav-btn');
      if(! btn) return;
      const isFav = loadFavs(). some(i=> i.name === name);
      const icon = btn.querySelector('i');
      if(isFav){ btn.classList.add('active'); icon.classList.replace('fa-regular','fa-solid'); }
      else { btn.classList.remove('active'); icon.classList.replace('fa-solid','fa-regular'); }
    });
  }

  // Обработчик кликов на карточках: избранное и просмотр
  document.addEventListener('click', e=>{
    const fav = e.target.closest('. fav-btn');
    if(fav){
      const card = fav.closest('.product-card'); const name = card.dataset.name;
      let arr = loadFavs();
      if(arr.some(i=>i.name===name)) arr = arr.filter(i=> i.name !== name);
      else arr.push({name, img: card.querySelector('img')?.src || '', tag: card.dataset.tag || ''});
      saveFavs(arr); updateFavIcons();
      return;
    }

    const view = e.target.closest('.view-btn');
    if(view){
      const card = view. closest('.product-card');
      openModal(card);
      return;
    }
  });

  // Модалка
  const modal = document.getElementById('productModal');
  const modalImg = document.getElementById('modalImg');
  const modalTitle = document. getElementById('modalTitle');
  const modalMeta = document.getElementById('modalMeta');
  const modalClose = document.querySelector('.modal-close');
  const modalFav = document.getElementById('modalFav');
  const modalGo = document.getElementById('modalGo');

  function openModal(card){
    modalImg.src = card.querySelector('img')?.src || '';
    modalTitle.textContent = card.dataset.name || '';
    modalMeta.textContent = card.querySelector('. meta')?.textContent || '';
    modal.classList.add('open'); modal.setAttribute('aria-hidden','false');
    // modal action
    modalGo.href = 'catalog. html'; // можно подставлять ссылку на товар
  }
  function closeModal(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); }

  modalClose?. addEventListener('click', closeModal);
  modal?.addEventListener('click', e=>{ if(e.target===modal) closeModal(); });
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModal(); });

  // Инициализация
  updateFavIcons();
  filterCatalog();

  // Небольшая GSAP анимация для "hero" при загрузке
  document.addEventListener('DOMContentLoaded', ()=> {
    setTimeout(()=> {
      if(window.gsap) {
        gsap.from('.hero-right h1',{y:16,opacity:0,duration:0.45});
        gsap.from('. hero-left img',{scale:0.96,opacity:0,duration:0.45});
      }
    },120);
  });
  </script>
</body>
</html>