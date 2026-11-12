<?php
session_start();
$page_title = "Командные виды спорта - Athletic Hub";

// 1. Подключение к БД
$host = 'localhost';
$db   = 'athletic_hub';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    $raw_data = [];
}

// 2. Получение товаров для Каталога 11 (ID 2318 - 2347)
$sql = "
    SELECT 
        p.id, p.name, p.description, p.rating, p.reviews_count,
        v.id as variant_id, v.variant_name, v.price, v.image
    FROM products p
    LEFT JOIN product_variants v ON p.id = v.product_id
    WHERE p.id >= 2318 AND p.id <= 2347
    ORDER BY p.id ASC
";

try {
    $stmt = $pdo->query($sql);
    $raw_data = $stmt->fetchAll();
} catch (Exception $e) {
    $raw_data = [];
}

// 3. Группировка данных
$structured_products = [];
foreach ($raw_data as $row) {
    $p_id = $row['id'];
    if (!isset($structured_products[$p_id])) {
        $structured_products[$p_id] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'desc' => $row['description'],
            'price' => $row['price'], 
            'variants' => []
        ];
    }
    if ($row['variant_id']) {
        $structured_products[$p_id]['variants'][] = [
            'variant_id' => $row['variant_id'],
            'name' => $row['variant_name'],
            'price' => $row['price']
        ];
    }
}

$all_items = array_values($structured_products);

// 4. Подкатегории
$sub_names = [
    ['title' => 'Футбольный инвентарь', 'icon' => 'fa-soccer-ball', 'count' => 7],
    ['title' => 'Баскетбол', 'icon' => 'fa-basketball-hoop', 'count' => 6],
    ['title' => 'Волейбол', 'icon' => 'fa-volleyball', 'count' => 7],
    ['title' => 'Сетки и оборудование', 'icon' => 'fa-net', 'count' => 3],
    ['title' => 'Командная форма', 'icon' => 'fa-shirt', 'count' => 7]
];

$final_catalog = [];
$offset = 0;
foreach ($sub_names as $sub) {
    $slice = array_slice($all_items, $offset, $sub['count']);
    if (!empty($slice)) {
        $final_catalog[] = [
            'category' => $sub['title'],
            'icon' => $sub['icon'],
            'items' => $slice
        ];
    }
    $offset += $sub['count'];
}

$catalogData = json_encode($final_catalog, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root{
            --primary: #000000;
            --text: #000000;
            --bg: #f4f4f4;
            --card-bg: #ffffff;
            --card-shadow: 0 8px 25px rgba(0,0,0,0.1);
            --accent: #2C7873;
            --grad-start: #ffffff;
            --grad-end: #000000;
            --success: #4CAF50;
        }

        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); padding-top: 80px; }
        
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; height: 72px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 22px; background: linear-gradient(90deg, var(--grad-start), var(--grad-end));
            z-index: 1200; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.2rem; color: #000; text-decoration: none; }
        .nav-menu { display: flex; gap: 22px; }
        .nav-link { font-weight: 600; padding: 8px 12px; border-radius: 10px; transition: 0.2s; color: #000; text-decoration: none; }
        .nav-link:hover { background: rgba(0,0,0,0.05); }
        .nav-link.active { background: rgba(0,0,0,0.1); }
        
        .auth-area { display: flex; gap: 10px; align-items: center; }
        .auth-btn { padding: 8px 14px; border-radius: 10px; background: #000; color: #fff; font-weight: 600; font-size: 14px; border: 1px solid rgba(255,255,255,0.2); text-decoration: none; }

        .catalog-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .category-section { margin-bottom: 60px; }
        .category-title { 
            font-size: 28px; margin-bottom: 30px; border-left: 5px solid var(--accent); padding-left: 15px; 
            display: flex; align-items: center; gap: 15px; color: var(--primary); font-weight: 700;
        }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card { background: var(--card-bg); border-radius: 16px; box-shadow: var(--card-shadow); transition: 0.3s; cursor: pointer; overflow: hidden; border: 1px solid rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-8px); }
        .img-placeholder { width: 100%; height: 220px; background: #e8e8e8; display: flex; align-items: center; justify-content: center; color: #bbb; font-size: 60px; }
        .product-info { padding: 18px; display: flex; flex-direction: column; gap: 12px; }
        .product-info h3 { margin: 0; font-size: 16px; height: 45px; overflow: hidden; line-height: 1.4; font-weight: 700; }
        .product-price { font-weight: 800; color: var(--accent); font-size: 22px; }

        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2000; display: none; align-items: center; justify-content: center; padding: 20px; }
        .modal.open { display: flex; }
        .modal-content { background: #fff; width: 100%; max-width: 800px; border-radius: 20px; padding: 40px; display: flex; gap: 30px; position: relative; }
        
        .notification { position: fixed; bottom: 20px; right: 20px; background: var(--success); color: white; padding: 16px 24px; border-radius: 10px; z-index: 3000; display: none; }

        @media (max-width: 768px) {
            .nav-menu, .auth-area { display: none; }
            .modal-content { flex-direction: column; padding: 20px; }
            .modal-content > div { width: 100% !important; }
        }
    </style>
</head>
<body>

     <header class="top-nav">
        <a href="index.php" class="logo" id="logo">
            <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php" class="nav-link">Главная</a>
            <a href="catalog1.php" class="nav-link active">Каталог</a>
            <a href="cart.php" class="nav-link">Корзина</a>
            <a href="orders.php"class="nav-link ">Мои Заказы</a>
            <a href="about.php" class="nav-link">О нас</a>
            <a href="contacts.php" class="nav-link">Контакты</a>
        </nav>

        <div class="auth-area">
            <a href="auth.php" class="auth-btn">Войти</a>
            <a href="register.php" class="auth-btn register">Регистрация</a>
            <button class="burger"><i class="fa-solid fa-bars"></i></button>
        </div>
    </header>

    <div class="catalog-container">
        <h1 style="text-align: center; margin: 40px 0; font-weight: 800; font-size: 2.5rem;">Командные виды спорта</h1>
        <div id="catalogContainer"></div>
    </div>

    <div class="modal" id="productModal">
        <div class="modal-content">
            <span style="position:absolute; right:25px; top:15px; cursor:pointer; font-size:35px; color:#999;" onclick="closeModal()">&times;</span>
            <div id="modalImgPlaceholder" class="img-placeholder" style="width:45%; height:350px; border-radius:15px;"></div>
            <div style="width:55%">
                <h2 id="modalTitle" style="margin-top:0;"></h2>
                <p id="modalDesc" style="color:#666; font-size:14px; line-height:1.6; min-height:100px;"></p>
                <div style="margin-bottom: 20px;">
                    <label style="font-size:12px; color:#888; font-weight:600;">ПАРАМЕТРЫ ТОВАРА:</label>
                    <select id="variant-select" style="width:100%; padding:12px; margin-top:5px; border-radius:8px; border:1px solid #ddd;"></select>
                </div>
                <div class="product-price" id="modalPrice" style="font-size:32px; margin-bottom:25px;"></div>
                <button onclick="addToCart()" style="width:100%; padding:18px; background:#000; color:#fff; border:none; border-radius:12px; cursor:pointer; font-weight:bold; font-size:16px;">
                    <i class="fa-solid fa-cart-plus"></i> ДОБАВИТЬ В КОРЗИНУ
                </button>
            </div>
        </div>
    </div>

    <div class="notification" id="notification">
        <i class="fa-solid fa-check-circle"></i> Товар добавлен в корзину!
    </div>

    <script>
        const catalogData = <?php echo $catalogData; ?>;
        const container = document.getElementById('catalogContainer');
        let currentProduct = null;

        function formatPrice(val) {
            if(!val) return "0 ₸";
            let num = String(val).replace(/[^0-9]/g, '');
            return parseInt(num).toLocaleString('ru-RU') + " ₸";
        }

        function render() {
            catalogData.forEach(cat => {
                const section = document.createElement('div');
                section.className = 'category-section';
                section.innerHTML = `<h2 class="category-title"><i class="fa-solid ${cat.icon}"></i> ${cat.category}</h2><div class="products-grid"></div>`;
                const grid = section.querySelector('.products-grid');
                
                cat.items.forEach(item => {
                    const price = (item.variants && item.variants.length > 0) ? item.variants[0].price : item.price;
                    const card = document.createElement('div');
                    card.className = 'product-card';
                    card.innerHTML = `
                        <div class="img-placeholder"><i class="fa-solid ${cat.icon}"></i></div>
                        <div class="product-info">
                            <h3>${item.name}</h3>
                            <div class="product-price">${formatPrice(price)}</div>
                            <button style="margin-top:5px; width:100%; padding:10px; border:2px solid #000; background:none; cursor:pointer; border-radius:8px; font-weight:700;">ПОДРОБНЕЕ</button>
                        </div>
                    `;
                    card.onclick = () => openModal(item, cat.icon);
                    grid.appendChild(card);
                });
                container.appendChild(section);
            });
        }

        function openModal(item, icon) {
            currentProduct = item;
            document.getElementById('modalTitle').textContent = item.name;
            document.getElementById('modalDesc').textContent = item.desc || "Профессиональное оборудование для спорта.";
            document.getElementById('modalImgPlaceholder').innerHTML = `<i class="fa-solid ${icon}" style="font-size:100px;"></i>`;
            
            const sel = document.getElementById('variant-select');
            sel.innerHTML = '';
            
            if(item.variants && item.variants.length > 0) {
                item.variants.forEach(v => {
                    let opt = new Option(`${v.name} - ${formatPrice(v.price)}`, v.variant_id);
                    opt.dataset.price = v.price;
                    sel.add(opt);
                });
                sel.onchange = () => { document.getElementById('modalPrice').textContent = formatPrice(sel.options[sel.selectedIndex].dataset.price); };
                sel.onchange();
            } else {
                document.getElementById('modalPrice').textContent = formatPrice(item.price);
                sel.add(new Option("Стандарт", 0));
            }
            document.getElementById('productModal').classList.add('open');
        }

        function closeModal() { document.getElementById('productModal').classList.remove('open'); }

        // ИСПРАВЛЕННАЯ ФУНКЦИЯ ДОБАВЛЕНИЯ
        function addToCart() {
            const sel = document.getElementById('variant-select');
            
            // Чистое числовое значение цены
            const rawPrice = sel.options.length > 0 ? sel.options[sel.selectedIndex].dataset.price : currentProduct.price;
            const cleanPrice = parseInt(String(rawPrice).replace(/[^0-9]/g, '')) || 0;

            const cartItem = {
                product_id: parseInt(currentProduct.id),
                name: currentProduct.name,
                price: cleanPrice,
                variant_id: (sel.value && sel.value !== "0") ? parseInt(sel.value) : 0,
                quantity: 1,
                variant_name: sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : "Стандарт"
            };

            let cart = JSON.parse(localStorage.getItem('athletic_hub_cart') || '[]');
            
            // Проверка на дубликат
            let exist = cart.find(i => i.product_id === cartItem.product_id && i.variant_id === cartItem.variant_id);
            if (exist) {
                exist.quantity += 1;
            } else {
                cart.push(cartItem);
            }
            
            localStorage.setItem('athletic_hub_cart', JSON.stringify(cart));
            
            closeModal();
            const n = document.getElementById('notification');
            n.style.display = 'block';
            setTimeout(() => { n.style.display = 'none'; }, 2000);
        }

        render();
    </script>
</body>
</html>