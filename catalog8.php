<?php
session_start();
$page_title = "Единоборства - Athletic Hub";

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

// 2. Получение товаров для Каталога 8 (ID 2197 - 2224)
$sql = "
    SELECT 
        p.id, p.name, p.description, p.rating, p.reviews_count,
        v.id as variant_id, v.variant_name, v.price, v.image
    FROM products p
    LEFT JOIN product_variants v ON p.id = v.product_id
    WHERE p.id >= 2197 AND p.id <= 2231
    ORDER BY p.id ASC
";

try {
    $stmt = $pdo->query($sql);
    $raw_data = $stmt->fetchAll();
} catch (Exception $e) {
    $raw_data = [];
}

// 3. Группировка данных (если в базе пусто, используем ваши статические данные)
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

// Если БД вернула результаты, используем их, иначе используем ваш JSON-массив
if (!empty($structured_products)) {
    $all_items = array_values($structured_products);
    
    $sub_names = [
        ['title' => 'Перчатки', 'icon' => 'fa-hand-fist'],
        ['title' => 'Шлемы', 'icon' => 'fa-head-side-virus'],
        ['title' => 'Бинты', 'icon' => 'fa-bandage'],
        ['title' => 'Лапы и макивары', 'icon' => 'fa-hand-paper'],
        ['title' => 'Боксерские мешки', 'icon' => 'fa-droplet']
    ];

    $final_catalog = [];
    for ($i = 0; $i < count($sub_names); $i++) {
        $slice = array_slice($all_items, $i * 7, 7);
        if (!empty($slice)) {
            $final_catalog[] = [
                'category' => $sub_names[$i]['title'],
                'icon' => $sub_names[$i]['icon'],
                'items' => $slice
            ];
        }
    }
    $catalogData = json_encode($final_catalog, JSON_UNESCAPED_UNICODE);
} else {
    // Если данных в БД нет, подставляем ваш предоставленный JSON напрямую для визуала
    $catalogData = json_encode([
        [
            "category" => "Перчатки",
            "icon" => "fa-hand-fist",
            "items" => [
                ["id"=>2197,"name"=>"Боксерские перчатки 8 oz","price"=>18000,"desc"=>"Профессиональные перчатки для соревнований.","variants"=>[]],
                ["id"=>2198,"name"=>"Боксерские перчатки 12 oz","price"=>14500,"desc"=>"Универсальные тренировочные перчатки.","variants"=>[]],
                ["id"=>2199,"name"=>"Боксерские перчатки 16 oz","price"=>16000,"desc"=>"Для безопасного спарринга.","variants"=>[]],
                ["id"=>2200,"name"=>"Боксерские перчатки 20 oz","price"=>13000,"desc"=>"Для работы с мешком.","variants"=>[]],
                ["id"=>2201,"name"=>"Перчатки для ММА","price"=>8500,"desc"=>"С открытыми пальцами.","variants"=>[]],
                ["id"=>2202,"name"=>"Детские перчатки (4-6 oz)","price"=>7500,"desc"=>"Мягкие и безопасные.","variants"=>[]],
                ["id"=>2203,"name"=>"Кожаные премиум-класса","price"=>32000,"desc"=>"Натуральная кожа.","variants"=>[]]
            ]
        ],
        [
            "category" => "Шлемы",
            "icon" => "fa-head-side-virus",
            "items" => [
                ["id"=>2204,"name"=>"Наголовник открытый","price"=>14000,"desc"=>"Защита с открытым обзором.","variants"=>[]],
                ["id"=>2205,"name"=>"Наголовник закрытый","price"=>16500,"desc"=>"Полная защита лица.","variants"=>[]],
                ["id"=>2206,"name"=>"Шлем для кикбоксинга","price"=>18000,"desc"=>"С защитой челюсти.","variants"=>[]],
                ["id"=>2207,"name"=>"Шлем для ММА","price"=>15000,"desc"=>"Полуоткрытый тип.","variants"=>[]],
                ["id"=>2208,"name"=>"Шлем детский","price"=>9000,"desc"=>"Мягкая подкладка.","variants"=>[]],
                ["id"=>2209,"name"=>"Шлем профессиональный","price"=>22000,"desc"=>"Для соревнований.","variants"=>[]],
                ["id"=>2210,"name"=>"Шлем с регулировкой","price"=>12000,"desc"=>"Удобная посадка.","variants"=>[]]
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
}
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
        .nav-link.active { background: rgba(0,0,0,0.1); }
        
        .auth-area { display: flex; gap: 10px; align-items: center; }
        .auth-btn { padding: 8px 14px; border-radius: 10px; background: #000; color: #fff; font-weight: 600; font-size: 14px; border: 1px solid rgba(255,255,255,0.2); text-decoration: none; }

        .catalog-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .category-section { margin-bottom: 60px; }
        .category-title { 
            font-size: 28px; margin-bottom: 30px; border-left: 5px solid var(--accent); padding-left: 15px; 
            display: flex; align-items: center; gap: 15px; font-weight: 700;
        }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card { background: var(--card-bg); border-radius: 16px; box-shadow: var(--card-shadow); transition: 0.3s; cursor: pointer; overflow: hidden; border: 1px solid rgba(0,0,0,0.05); }
        .product-card:hover { transform: translateY(-8px); }
        .img-placeholder { width: 100%; height: 220px; background: #e8e8e8; display: flex; align-items: center; justify-content: center; color: #bbb; font-size: 60px; }
        
        .product-info { padding: 18px; display: flex; flex-direction: column; gap: 12px; }
        .product-info h3 { margin: 0; font-size: 16px; height: 45px; overflow: hidden; font-weight: 700; }
        .product-price { font-weight: 800; color: var(--accent); font-size: 22px; }

        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2000; display: none; align-items: center; justify-content: center; }
        .modal.open { display: flex; }
        .modal-content { background: #fff; width: 100%; max-width: 800px; border-radius: 20px; padding: 40px; display: flex; gap: 30px; position: relative; }
        
        .notification { position: fixed; bottom: 20px; right: 20px; background: var(--success); color: white; padding: 16px 24px; border-radius: 10px; z-index: 3000; display: none; }
    </style>
</head>
<body>

    <header class="top-nav">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
        </a>
        <nav class="nav-menu">
            <a href="index.php" class="nav-link">Главная</a>
            <a href="catalog8.php" class="nav-link active">Каталог</a>
            <a href="cart.php" class="nav-link">Корзина</a>
            <a href="orders.php" class="nav-link">Мои Заказы</a>
        </nav>
        <div class="auth-area">
            <a href="auth.php" class="auth-btn">Войти</a>
        </div>
    </header>

    <div class="catalog-container">
        <h1 style="text-align: center; margin: 40px 0; font-weight: 800; font-size: 2.5rem;">Единоборства</h1>
        <div id="catalogContainer"></div>
    </div>

    <div class="modal" id="productModal">
        <div class="modal-content">
            <span style="position:absolute; right:25px; top:15px; cursor:pointer; font-size:35px; color:#999;" onclick="closeModal()">&times;</span>
            <div id="modalImgPlaceholder" class="img-placeholder" style="width:45%; height:350px; border-radius:15px;"></div>
            <div style="width:55%">
                <h2 id="modalTitle"></h2>
                <p id="modalDesc" style="color:#666; font-size:14px; line-height:1.6; min-height:100px;"></p>
                <div style="margin-bottom: 20px;">
                    <label style="font-size:12px; color:#888; font-weight:600;">ВАРИАНТ:</label>
                    <select id="variant-select" style="width:100%; padding:12px; margin-top:5px; border-radius:8px; border:1px solid #ddd;"></select>
                </div>
                <div class="product-price" id="modalPrice" style="font-size:32px; margin-bottom:25px;"></div>
                <button onclick="addToCart()" style="width:100%; padding:18px; background:#000; color:#fff; border:none; border-radius:12px; cursor:pointer; font-weight:bold;">
                    ДОБАВИТЬ В КОРЗИНУ
                </button>
            </div>
        </div>
    </div>

    <div class="notification" id="notification">✅ Товар добавлен!</div>

    <script>
        const catalogData = <?php echo $catalogData; ?>;
        const container = document.getElementById('catalogContainer');
        let currentProduct = null;

        function render() {
            catalogData.forEach(cat => {
                const section = document.createElement('div');
                section.className = 'category-section';
                section.innerHTML = `<h2 class="category-title"><i class="fa-solid ${cat.icon}"></i> ${cat.category}</h2><div class="products-grid"></div>`;
                const grid = section.querySelector('.products-grid');
                
                cat.items.forEach(item => {
                    const price = item.variants && item.variants.length > 0 ? item.variants[0].price : item.price;
                    const card = document.createElement('div');
                    card.className = 'product-card';
                    card.innerHTML = `
                        <div class="img-placeholder"><i class="fa-solid ${cat.icon}"></i></div>
                        <div class="product-info">
                            <h3>${item.name}</h3>
                            <div class="product-price">${parseInt(price).toLocaleString()} ₸</div>
                            <button style="margin-top:5px; width:100%; padding:10px; border:2px solid #000; background:none; cursor:pointer; border-radius:8px; font-weight:700;">ПОДРОБНЕЕ</button>
                        </div>`;
                    card.onclick = () => openModal(item, cat.icon);
                    grid.appendChild(card);
                });
                container.appendChild(section);
            });
        }

        function openModal(item, icon) {
            currentProduct = item;
            document.getElementById('modalTitle').textContent = item.name;
            document.getElementById('modalDesc').textContent = item.desc;
            document.getElementById('modalImgPlaceholder').innerHTML = `<i class="fa-solid ${icon}" style="font-size:100px;"></i>`;
            const sel = document.getElementById('variant-select');
            sel.innerHTML = '';
            if(item.variants && item.variants.length > 0) {
                item.variants.forEach(v => {
                    let opt = new Option(`${v.name} - ${v.price} ₸`, v.variant_id);
                    opt.dataset.price = v.price;
                    sel.add(opt);
                });
                sel.onchange = () => { document.getElementById('modalPrice').textContent = parseInt(sel.options[sel.selectedIndex].dataset.price).toLocaleString() + ' ₸'; };
                sel.onchange();
            } else {
                document.getElementById('modalPrice').textContent = parseInt(item.price).toLocaleString() + ' ₸';
                sel.add(new Option("Стандарт", 0));
            }
            document.getElementById('productModal').classList.add('open');
        }

        function closeModal() { document.getElementById('productModal').classList.remove('open'); }

        function addToCart() {
            const sel = document.getElementById('variant-select');
            const cartItem = {
                product_id: currentProduct.id,
                name: currentProduct.name,
                price: sel.options.length > 0 && sel.options[sel.selectedIndex].dataset.price ? parseInt(sel.options[sel.selectedIndex].dataset.price) : parseInt(currentProduct.price),
                variant_id: sel.value || 0,
                quantity: 1
            };
            let cart = JSON.parse(localStorage.getItem('athletic_hub_cart') || '[]');
            let exist = cart.find(i => i.product_id === cartItem.product_id && i.variant_id == cartItem.variant_id);
            if(exist) exist.quantity++; else cart.push(cartItem);
            localStorage.setItem('athletic_hub_cart', JSON.stringify(cart));
            closeModal();
            const n = document.getElementById('notification');
            n.style.display = 'block';
            setTimeout(() => n.style.display = 'none', 2000);
        }
        render();
    </script>
</body>
</html>