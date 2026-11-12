<?php
session_start();
$page_title = "Товары для плавания - Athletic Hub";

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

// 2. Получение товаров для Каталога 10 (Плавание: ID 2302 - 2317)
$sql = "
    SELECT 
        p.id, p.name, p.description, p.rating, p.reviews_count,
        v.id as variant_id, v.variant_name, v.price, v.image
    FROM products p
    LEFT JOIN product_variants v ON p.id = v.product_id
    WHERE p.id >= 2302 AND p.id <= 2317
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
            'price' => $row['price'] ?? 0,
            'rating' => $row['rating'],
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

// 4. Определение категорий для плавания (на основе ID из вашего INSERT)
$final_catalog = [
    [
        'category' => 'Очки для плавания',
        'icon' => 'fa-mask-ventilator',
        'items' => array_slice($all_items, 0, 6) // 2302 - 2307
    ],
    [
        'category' => 'Шапочки',
        'icon' => 'fa-head-side-mask',
        'items' => array_slice($all_items, 6, 4) // 2308 - 2311
    ],
    [
        'category' => 'Ласты',
        'icon' => 'fa-person-swimming',
        'items' => array_slice($all_items, 10, 3) // 2312 - 2314
    ],
    [
        'category' => 'Одежда для бассейна',
        'icon' => 'fa-shirt',
        'items' => array_slice($all_items, 13, 3) // 2315 - 2317
    ]
];

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
            --primary: #000; --text: #000; --bg: #f4f4f4; --card-bg: #fff;
            --accent: #2C7873; --grad-start: #fff; --grad-end: #000; --success: #4CAF50;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg); padding-top: 80px; }
        
        /* СТИЛЬ КАТАЛОГА 3: ШАПКА */
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; height: 72px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 22px; background: linear-gradient(90deg, var(--grad-start), var(--grad-end));
            z-index: 1200; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 800; color: #000; text-decoration: none; font-size: 1.2rem; }
        .nav-menu { display: flex; gap: 20px; }
        .nav-link { font-weight: 600; color: #000; text-decoration: none; padding: 8px 12px; border-radius: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(0,0,0,0.1); }

        /* КАРТОЧКИ ТОВАРОВ */
        .catalog-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .category-section { margin-bottom: 50px; }
        .category-header { 
            font-size: 26px; font-weight: 700; margin-bottom: 25px; 
            border-left: 5px solid var(--accent); padding-left: 15px; display: flex; align-items: center; gap: 12px;
        }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .product-card { 
            background: var(--card-bg); border-radius: 15px; overflow: hidden; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; cursor: pointer; border: 1px solid #eee;
        }
        .product-card:hover { transform: translateY(-7px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
        .img-box { height: 200px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; color: #ddd; font-size: 50px; }
        .p-info { padding: 20px; }
        .p-info h3 { margin: 0 0 10px; font-size: 16px; font-weight: 700; height: 40px; overflow: hidden; }
        .p-price { font-size: 22px; font-weight: 800; color: var(--accent); }

        /* MODAL */
        .modal { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 3000; display: none; align-items: center; justify-content: center; padding: 20px; }
        .modal.open { display: flex; }
        .modal-content { background: #fff; width: 100%; max-width: 850px; border-radius: 20px; display: flex; overflow: hidden; position: relative; }
        .modal-left { width: 45%; background: #f4f4f4; display: flex; align-items: center; justify-content: center; font-size: 100px; color: #ccc; }
        .modal-right { width: 55%; padding: 40px; }
        .close-btn { position: absolute; top: 20px; right: 25px; font-size: 30px; cursor: pointer; color: #999; }
        
        .btn-add { 
            width: 100%; padding: 15px; background: #000; color: #fff; border: none; 
            border-radius: 10px; font-weight: 700; cursor: pointer; margin-top: 20px; transition: 0.3s;
        }
        .btn-add:hover { opacity: 0.8; }
        .notification { position: fixed; bottom: 30px; right: 30px; background: var(--success); color: #fff; padding: 15px 30px; border-radius: 10px; display: none; z-index: 4000; }
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
        <h1 style="text-align:center; font-weight:800; margin: 40px 0;">ТОВАРЫ ДЛЯ ПЛАВАНИЯ</h1>
        <div id="catalogContent"></div>
    </div>

    <div class="modal" id="pModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-left" id="modalIcon"></div>
            <div class="modal-right">
                <h2 id="mName"></h2>
                <p id="mDesc" style="color: #666; line-height: 1.6; margin-bottom: 20px;"></p>
                <div id="vArea" style="margin-bottom: 20px;">
                    <label style="font-weight: 700; font-size: 12px; color: #999;">ВЫБЕРИТЕ ВАРИАНТ:</label>
                    <select id="vSelect" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; margin-top: 5px;"></select>
                </div>
                <div class="p-price" id="mPrice" style="font-size: 32px;"></div>
                <button class="btn-add" onclick="addToCart()">В КОРЗИНУ</button>
            </div>
        </div>
    </div>

    <div class="notification" id="toast">✅ Товар добавлен в корзину!</div>

    <script>
        const catalogData = <?php echo $catalogData; ?>;
        const container = document.getElementById('catalogContent');
        let currentP = null;

        function render() {
            catalogData.forEach(cat => {
                let html = `<section class="category-section">
                    <h2 class="category-header"><i class="fa-solid ${cat.icon}"></i> ${cat.category}</h2>
                    <div class="products-grid">`;
                
                cat.items.forEach(item => {
                    const displayPrice = item.variants.length > 0 ? item.variants[0].price : item.price;
                    html += `
                        <div class="product-card" onclick="openModal(${JSON.stringify(item).replace(/"/g, '&quot;')}, '${cat.icon}')">
                            <div class="img-box"><i class="fa-solid ${cat.icon}"></i></div>
                            <div class="p-info">
                                <h3>${item.name}</h3>
                                <div class="p-price">${parseInt(displayPrice).toLocaleString()} ₸</div>
                            </div>
                        </div>`;
                });
                html += `</div></section>`;
                container.innerHTML += html;
            });
        }

        function openModal(item, icon) {
            currentP = item;
            document.getElementById('mName').textContent = item.name;
            document.getElementById('mDesc').textContent = item.desc;
            document.getElementById('modalIcon').innerHTML = `<i class="fa-solid ${icon}"></i>`;
            
            const sel = document.getElementById('vSelect');
            sel.innerHTML = '';
            
            if(item.variants.length > 0) {
                item.variants.forEach(v => {
                    let opt = new Option(`${v.name} - ${parseInt(v.price).toLocaleString()} ₸`, v.variant_id);
                    opt.dataset.price = v.price;
                    sel.add(opt);
                });
                sel.onchange = () => { document.getElementById('mPrice').textContent = parseInt(sel.options[sel.selectedIndex].dataset.price).toLocaleString() + ' ₸'; };
                sel.onchange();
            } else {
                document.getElementById('mPrice').textContent = parseInt(item.price).toLocaleString() + ' ₸';
                sel.add(new Option("Стандарт", 0));
            }
            document.getElementById('pModal').classList.add('open');
        }

        function closeModal() { document.getElementById('pModal').classList.remove('open'); }

        function addToCart() {
            const sel = document.getElementById('vSelect');
            const cartItem = {
                id: currentP.id,
                name: currentP.name,
                price: sel.options[sel.selectedIndex].dataset.price || currentP.price,
                variant: sel.options[sel.selectedIndex].text,
                qty: 1
            };
            let cart = JSON.parse(localStorage.getItem('athletic_hub_cart') || '[]');
            cart.push(cartItem);
            localStorage.setItem('athletic_hub_cart', JSON.stringify(cart));
            
            closeModal();
            const t = document.getElementById('toast');
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 2000);
        }

        render();
    </script>
</body>
</html>