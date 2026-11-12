<?php
session_start();

// Если пользователь не авторизован, отправляем на вход
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// === ИСПРАВЛЕНО: Используем имя базы данных "athletic_hub" ===
$conn = new mysqli("localhost", "root", "", "athletic_hub"); 
$conn->set_charset("utf8mb4");

// Вспомогательная функция для возврата ошибки JSON
function fail($msg) { 
    echo json_encode(['success'=>false, 'message'=>$msg]); 
    exit(); 
}

// === ОБРАБОТКА СОЗДАНИЯ ЗАКАЗА (КРИТИЧЕСКАЯ ДИАГНОСТИКА) ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_order') {
    // Получаем ID пользователя из сессии
    $user_id = $_SESSION['user_id'] ?? null;

    // --- ПЕРВЫЙ ДИАГНОСТИЧЕСКИЙ БЛОК: Проверяем сессию ---
    if (empty($user_id) || !is_numeric($user_id) || $user_id < 1) {
        $error_message = 'Ошибка сессии: user_id не найден или недействителен. ';
        $error_message .= 'Полученное значение: ' . var_export($user_id, true) . '. ';
        fail($error_message); 
    }
    
    // --- ВТОРОЙ ДИАГНОСТИЧЕСКИЙ БЛОК: ПРЯМАЯ ПРОВЕРКА СУЩЕСТВОВАНИЯ ID В БАЗЕ ---
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    if ($check_user === false) {
        fail('Ошибка подготовки SQL-запроса (check user): ' . $conn->error);
    }
    
    // Пытаемся привязать user_id как целое число
    if (!$check_user->bind_param("i", $user_id)) {
        fail('Ошибка привязки параметра user_id: ' . $check_user->error);
    }
    
    $check_user->execute();
    $result = $check_user->get_result();

    if ($result->num_rows === 0) {
        // Это сообщение должно появиться, если ID не существует в users
        fail('КРИТИЧЕСКАЯ ОШИБКА БАЗЫ: User ID ' . $user_id . ' из сессии не найден в таблице users. Возможно, проблема с типом данных.');
    }
    // ----------------------------------------------------

    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $items_json = $_POST['items_json'] ?? '[]';
    $items = json_decode($items_json, true);

    if (!$phone || !$address || !$city) fail('Пожалуйста, заполните все поля!');
    if (!is_array($items) || count($items) == 0) fail('Корзина пуста.');

    foreach($items as $it) {
        if (!isset($it['variant_id']) || !isset($it['quantity']))
            fail('Ошибка структуры корзины (нет variant_id или quantity)');
    }

    $raw_variant_ids = array_column($items, 'variant_id');
    $variant_ids = array_map('intval', $raw_variant_ids);
    $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));
    $types = str_repeat('i', count($variant_ids));
    
    $stmt = $conn->prepare("SELECT id, price, stock, variant_name FROM product_variants WHERE id IN ($placeholders)");
    if ($stmt === false) {
        fail('Ошибка подготовки SQL-запроса (variants prepare): ' . $conn->error);
    }
    if (!$stmt->bind_param($types, ...$variant_ids)) {
        fail('Ошибка привязки параметров SQL-запроса (variants bind): ' . $stmt->error);
    }
    if (!$stmt->execute()) {
        fail('Ошибка выполнения SQL-запроса (variants execute): ' . $stmt->error);
    }
    
    $res = $stmt->get_result();
    $variants = [];
    while($v = $res->fetch_assoc()) {
        $variants[$v['id']] = $v;
    }

    $total = 0;
    foreach($items as $it) {
        $vid = (int)$it['variant_id'];
        $qty = max(1, (int)$it['quantity']);
        if (!isset($variants[$vid])) fail("Не найден вариант (ID $vid)");
        $total += $variants[$vid]['price'] * $qty;
    }

    $discount = $total > 150000 ? $total * 0.05 : 0;
    $total_amount = $total - $discount;

    // Вставка заказа
    $insert_order = $conn->prepare(
        "INSERT INTO orders (user_id, phone, address, city, postal_code, total_amount, items_json, status, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
    );
    
    if ($insert_order === false) {
        fail('Ошибка подготовки SQL-запроса (order prepare): ' . $conn->error);
    }

    $items_json_db = json_encode($items, JSON_UNESCAPED_UNICODE);
    
    // user_id привязывается как int
    if (!$insert_order->bind_param("isssids", $user_id, $phone, $address, $city, $postal_code, $total_amount, $items_json_db)) {
        fail('Ошибка привязки параметров SQL-запроса (order bind): ' . $insert_order->error);
    }

    if ($insert_order->execute()) {
        $order_id = $insert_order->insert_id;
        echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Заказ создан успешно!']);
    } else {
        // УЛУЧШЕННЫЙ ВЫВОД ОШИБКИ: включаем user_id в сообщение
        echo json_encode([
            'success' => false, 
            'message' => 'Ошибка при создании заказа: ' . $insert_order->error,
            'debug_user_id' => $user_id 
        ]);
    }
    exit();
}
// === КОНЕЦ ОБРАБОТКИ СОЗДАНИЯ ЗАКАЗА ===

// (Остальной код получения статуса заказа и HTML/JS остается без изменений)
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Корзина — ATHLETIC HUB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        :root{
            --primary: #000000;
            --text: #000000;
            --muted: #555555;
            --bg: #f4f4f4;
            --card-bg: #ffffff;
            --card-shadow: 0 8px 25px rgba(0,0,0,0.1);
            --accent: #000000;
            --accent-hover: #333333;
            --grad-start: #ffffff;
            --grad-end: #000000;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        a { text-decoration: none; color: inherit; }
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; height: 72px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 22px;
            background: linear-gradient(90deg, var(--grad-start), var(--grad-end));
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1200;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.2rem; color: var(--primary); }
        .nav-menu { display: flex; gap: 22px; }
        .nav-link { font-weight: 600; padding: 8px 12px; border-radius: 10px; transition: 0.2s; color: var(--primary);}
        .nav-link:hover { background: rgba(255,255,255,0.2); }
        .nav-link.active { background: rgba(255,255,255,0.3); color: #fff; }
        .auth-area { display: flex; gap: 10px; align-items: center; }
        .auth-btn { padding: 8px 14px; border-radius: 10px; background: #000; color: #fff; font-weight: 600; font-size: 14px; border: 1px solid rgba(255,255,255,0.2);}
        .auth-btn.register { background: #fff; color: #000; }
        .cart-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        h1 { font-size: 32px; margin-bottom: 25px; color: var(--primary); }
        .cart-items { display: flex; flex-direction: column; gap: 12px; }
        .item-card {
            display: flex; align-items: center;
            background: var(--card-bg); padding: 12px; border-radius: 12px; box-shadow: var(--card-shadow);
        }
        .item-checkbox { margin-right: 12px; transform: scale(1.1); }
        .item-card-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 16px; border: 1px solid #eee;}
        .item-details { flex-grow: 1; }
        .item-details h3 { margin: 0 0 5px; font-size: 16px; font-weight: 600; }
        .item-price-unit { font-size: 14px; color: var(--muted); }
        .item-controls { display: flex; align-items: center; gap: 16px; }
        .quantity-control { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; width: 110px;}
        .qty-btn { background: #f8f8f8; border: none; padding: 8px 12px; cursor: pointer; transition: 0.2s; font-weight: 600; color: var(--text);}
        .qty-btn:hover { background: #eee; }
        .item-qty { width: 36px; text-align: center; font-weight: 600; font-size: 14px; border-left: 1px solid #ddd; border-right: 1px solid #ddd;}
        .item-subtotal { font-size: 18px; font-weight: 700; color: var(--primary); width: 120px; text-align: right; }
        .remove-btn { background: none; border: none; color: #ff5252; font-size: 20px; cursor: pointer; transition: color 0.2s; padding: 6px; border-radius: 50%;}
        .remove-btn:hover { color: #d00; background: rgba(255,82,82,0.08);}
        .empty-cart-message { text-align: center; padding: 50px; background: var(--card-bg); border-radius: 14px; box-shadow: var(--card-shadow); margin-top: 30px; }
        .empty-cart-message i { font-size: 50px; color: var(--accent); margin-bottom: 20px; }
        .cart-summary { background: var(--card-bg); padding: 25px; border-radius: 14px; box-shadow: var(--card-shadow); position: sticky; top: 100px; height: fit-content; }
        .summary-title { font-size: 20px; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; font-weight: 600; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 16px; }
        .summary-row.total { font-size: 22px; font-weight: 700; color: var(--accent); margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ddd; }
        .checkout-btn { width: 100%; padding: 15px; background: var(--accent); color: #fff; border: none; border-radius: 10px; font-size: 18px; font-weight: 600; cursor: pointer; transition: background 0.2s, transform 0.1s; margin-top: 16px;}
        .checkout-btn:hover { background: var(--accent-hover); transform: translateY(-1px);}
        .checkout-btn:disabled { background: #ccc; cursor: not-allowed;}
        .secondary-btn { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-weight: 600; margin-top: 10px; }
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 3000; display: none; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.3s; }
        .modal.open { display: flex; opacity: 1; visibility: visible; }
        .modal-content { background: #fff; width: 90%; max-width: 500px; border-radius: 16px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); transform: scale(0.95); opacity: 0; max-height: 90vh; overflow-y: auto; }
        .modal.open .modal-content { transform: scale(1); opacity: 1; transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s; }
        .modal-header { font-size: 24px; font-weight: 700; margin-bottom: 20px; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text); font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif; }
        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn-submit { background: var(--accent); color: #fff; }
        .btn-cancel { background: #f0f0f0; color: var(--text); }
        @media (max-width: 900px) {
            .cart-container { grid-template-columns: 1fr; }
            .cart-summary { position: static; }
        }
    </style>
</head>
<body>
<header class="top-nav" role="banner">
     <a href="index.php" class="logo" id="logo">
    <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
  </a>

  <nav class="nav-menu" aria-label="Главное меню">
    <a href="index.php" class="nav-link">Главная</a>
    <a href="catalog.php" class="nav-link">Каталог</a>
    <a href="cart.php" class="nav-link active">Корзина</a>
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
<main class="cart-container">
    <section class="cart-content">
        <h1>Ваша корзина</h1>
        <div style="display:flex; gap:10px; margin-bottom:12px; align-items:center;">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <input id="selectAllCheckbox" type="checkbox" style="transform:scale(1.1);"/>
                Выбрать все
            </label>
            <button id="removeSelectedBtn" class="secondary-btn" style="width:auto;">Удалить выделенные</button>
        </div>
        <div id="cartItemsList" class="cart-items"></div>
        <div id="emptyCartMessage" class="empty-cart-message" style="display:none;">
            <i class="fa-solid fa-box-open"></i>
            <h2>Ваша корзина пуста</h2>
            <p>Добавьте товары из <a href="catalog.php" style="color: var(--accent); font-weight: 600;">каталога</a></p>
        </div>
    </section>
    <aside class="cart-summary">
        <h2 class="summary-title">Сводка заказа</h2>
        <div class="summary-body">
            <div class="summary-row">
                <span>Товары (<span id="totalItemsCount">0</span>)</span>
                <span id="subtotalValue">0 ₸</span>
            </div>
            <div class="summary-row">
                <span>Скидка</span>
                <span style="color: #4CAF50;">- <span id="discountValue">0 ₸</span></span>
            </div>
            <div class="summary-row">
                <span>Доставка</span>
                <span id="shippingValue">Бесплатно</span>
            </div>
            <div class="summary-row total">
                <span>Итого к оплате</span>
                <span id="totalPayable">0 ₸</span>
            </div>
            <button id="checkoutBtn" class="checkout-btn" onclick="openCheckoutModal()" disabled>
                Оформить заказ (по выделенным)
            </button>
        </div>
    </aside>
</main>

<div id="checkoutModal" class="modal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-clipboard-list"></i> Оформление заказа
        </div>
        <form id="checkoutForm" onsubmit="submitCheckout(event)">
            <div class="form-group">
                <label for="phone">Номер телефона</label>
                <input type="tel" id="phone" name="phone" placeholder="+7 (999) 999-99-99" required>
            </div>
            <div class="form-group">
                <label for="city">Город</label>
                <input type="text" id="city" name="city" placeholder="Алматы" required>
            </div>
            <div class="form-group">
                <label for="address">Адрес доставки</label>
                <input type="text" id="address" name="address" placeholder="Ул. Абая, дом 10, кв. 5" required>
            </div>
            <div class="form-group">
                <label for="postal_code">Почтовый индекс</label>
                <input type="text" id="postal_code" name="postal_code" placeholder="050000">
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeCheckoutModal()">Отменить</button>
                <button type="submit" class="btn-submit">Создать заказ</button>
            </div>
        </form>
    </div>
</div>

<script>
const CART_STORAGE_KEY = 'athletic_hub_cart';
let currentOrderId = null, selectedIds = new Set();

function formatCurrency(value) {
    const num = Math.round(value); return `${num.toLocaleString('ru-RU')} ₸`;
}
function getCart() {
    try {
        const cartData = JSON.parse(localStorage.getItem(CART_STORAGE_KEY));
        if (!cartData || !Array.isArray(cartData) || cartData.length === 0) return [];
        return cartData.map(item => ({
            ...item,
            price: parseInt(item.price,10) || 0,
            quantity: parseInt(item.quantity, 10) || 1,
            // Используем variant_id в качестве основного ID для логики корзины,
            // так как PHP-скрипт работает с variant_id
            id: item.variant_id ?? (Date.now() + Math.random()),
            variant_id: item.variant_id // Убедимся, что variant_id всегда есть
        }));
    } catch (e) { return []; }
}
function saveCart(cart) {
    try { localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart)); } catch(e){}
}

function renderCart() {
    const cart = getCart();
    const listContainer = document.getElementById('cartItemsList');
    listContainer.innerHTML = '';

    // Использование variant_id для selectedIds для корректной работы с логикой PHP
    if (cart.length > 0 && selectedIds.size === 0)
        cart.forEach(i => selectedIds.add(String(i.variant_id)));

    document.getElementById('selectAllCheckbox').checked = cart.length && cart.every(i => selectedIds.has(String(i.variant_id)));
    if (cart.length === 0) { calculateTotals(); return; }

    cart.forEach(item => {
        // Используем variant_id для идентификации элемента в DOM и логике выбора
        const itemId = item.variant_id; 
        const price = parseInt(item.price,10) || 0;
        const quantity = parseInt(item.quantity, 10) || 1;
        const itemTotal = price * quantity;
        const card = document.createElement('div'); card.className = 'item-card'; card.id = `item-${itemId}`;
        
        // checkbox
        const cb = document.createElement('input');
        cb.type = 'checkbox'; cb.className = 'item-checkbox';
        cb.checked = selectedIds.has(String(itemId));
        cb.addEventListener('change', (e) => toggleSelectItem(itemId, e.target.checked));
        card.appendChild(cb);
        
        // image
        const img = document.createElement('img');
        img.src = item.img || `https://placehold.co/100x100?text=No+Img`;
        img.alt = item.name;
        img.className = 'item-card-image';
        card.appendChild(img);
        
        // details
        const details = document.createElement('div');
        details.className = 'item-details';
        details.innerHTML = `<h3>${item.name}</h3>
            ${item.variant_name ? `<div class="item-price-unit">Вариант: ${item.variant_name}</div>` : ''}
            <div class="item-price-unit">${formatCurrency(price)} за шт.</div>`;
        card.appendChild(details);
        
        // controls container
        const controls = document.createElement('div');
        controls.className = 'item-controls';
        
        // quantity control
        const qtyCtrl = document.createElement('div'); qtyCtrl.className = 'quantity-control';
        const btnDec = document.createElement('button');
        btnDec.className = 'qty-btn'; btnDec.type = 'button'; btnDec.textContent = '-';
        btnDec.addEventListener('click', () => updateQuantity(itemId, -1));
        const spanQty = document.createElement('span');
        spanQty.className = 'item-qty'; spanQty.textContent = String(quantity);
        const btnInc = document.createElement('button');
        btnInc.className = 'qty-btn'; btnInc.type = 'button'; btnInc.textContent = '+';
        btnInc.addEventListener('click', () => updateQuantity(itemId, 1));
        qtyCtrl.appendChild(btnDec); qtyCtrl.appendChild(spanQty); qtyCtrl.appendChild(btnInc);
        controls.appendChild(qtyCtrl);
        
        // subtotal
        const subtotalEl = document.createElement('div');
        subtotalEl.className = 'item-subtotal'; subtotalEl.textContent = formatCurrency(itemTotal);
        controls.appendChild(subtotalEl);
        
        // remove button
        const remBtn = document.createElement('button');
        remBtn.className = 'remove-btn'; remBtn.type = 'button';
        remBtn.title = 'Удалить'; remBtn.innerHTML = '<i class="fa-solid fa-times-circle"></i>';
        remBtn.addEventListener('click', () => removeItem(itemId));
        controls.appendChild(remBtn);
        card.appendChild(controls);
        listContainer.appendChild(card);
    });
    calculateTotals();
}
function updateQuantity(itemId, change) {
    let cart = getCart();
    // Ищем по variant_id, который в этом JS коде используется как item.id
    const idx = cart.findIndex(i => String(i.variant_id) === String(itemId)); 
    if (idx !== -1) {
        cart[idx].quantity = Math.max(1, cart[idx].quantity + change);
        saveCart(cart); renderCart();
    }
}
function removeItem(itemId) {
    // Удаляем по variant_id
    let cart = getCart().filter(i => String(i.variant_id) !== String(itemId));
    selectedIds.delete(String(itemId));
    saveCart(cart); renderCart();
}
function toggleSelectItem(itemId, checked) {
    const idStr = String(itemId);
    if (checked) selectedIds.add(idStr); else selectedIds.delete(idStr);
    calculateTotals();
}
function selectAll(checked) {
    const cart = getCart();
    // Используем variant_id
    if (checked) cart.forEach(i => selectedIds.add(String(i.variant_id))); else selectedIds.clear();
    renderCart(); calculateTotals();
}
function removeSelected() {
    let cart = getCart();
    if (selectedIds.size === 0) return;
    if (!confirm('Удалить выделенные товары из корзины?')) return;
    // Удаляем из корзины те, чьи variant_id есть в selectedIds
    cart = cart.filter(i => !selectedIds.has(String(i.variant_id)));
    selectedIds.clear();
    saveCart(cart); renderCart();
}
function calculateTotals() {
    const cart = getCart();
    // Фильтруем по variant_id
    const selected = cart.filter(i => selectedIds.has(String(i.variant_id)));
    const useAllIfNoneSelected = selected.length === 0;
    const itemsForCalc = useAllIfNoneSelected ? cart : selected;
    let subtotal = 0, totalItems = 0;
    itemsForCalc.forEach(item => {
        const price = parseInt(item.price,10) || 0;
        const qty = parseInt(item.quantity,10) || 1;
        subtotal += price * qty; totalItems += qty;
    });
    const discount = subtotal > 150000 ? subtotal * 0.05 : 0;
    const totalPayable = subtotal - discount;
    document.getElementById('totalItemsCount').textContent = totalItems;
    document.getElementById('subtotalValue').textContent = formatCurrency(subtotal);
    document.getElementById('discountValue').textContent = formatCurrency(discount);
    document.getElementById('totalPayable').textContent = formatCurrency(totalPayable);
    document.getElementById('checkoutBtn').disabled = (selected.length === 0);
    document.getElementById('emptyCartMessage').style.display = (cart.length === 0 ? 'block' : 'none');
    document.getElementById('cartItemsList').style.display = (cart.length === 0 ? 'none' : 'flex');
}
window.onload = renderCart;
document.getElementById('selectAllCheckbox').addEventListener('change', (e) => selectAll(e.target.checked));
document.getElementById('removeSelectedBtn').addEventListener('click', removeSelected);

// ==== МОДАЛКА ОФОРМЛЕНИЯ ЗАКАЗА ====
const checkoutModal = document.getElementById('checkoutModal');
function openCheckoutModal() {
    const cart = getCart();
    // Фильтруем по variant_id
    const selected = cart.filter(i => selectedIds.has(String(i.variant_id)));
    if (selected.length === 0) {
        alert('Выберите хотя бы один товар для оформления заказа.');
        return;
    }
    checkoutModal.style.display = 'flex';
    setTimeout(() => checkoutModal.classList.add('open'), 10);
}
function closeCheckoutModal() {
    checkoutModal.classList.remove('open');
    setTimeout(() => checkoutModal.style.display = 'none', 300);
}
async function submitCheckout(e) {
    e.preventDefault();
    const cart = getCart();
    // Фильтруем по variant_id
    const selected = cart.filter(i => selectedIds.has(String(i.variant_id)));
    if (selected.length === 0) { alert('Корзина пуста или ничего не выделено!'); return; }
    const phone = document.getElementById('phone').value;
    const address = document.getElementById('address').value;
    const city = document.getElementById('city').value;
    const postal_code = document.getElementById('postal_code').value;
    const orderItems = selected.map(i => ({
        variant_id: i.variant_id, // PHP ожидает variant_id
        quantity: i.quantity
    }));
    const formData = new FormData();
    formData.append('action', 'create_order');
    formData.append('phone', phone);
    formData.append('address', address);
    formData.append('city', city);
    formData.append('postal_code', postal_code);
    formData.append('items_json', JSON.stringify(orderItems));
    try {
        const response = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            closeCheckoutModal();
            // Удаляем из корзины только оформленные товары (по variant_id)
            let remaining = getCart().filter(i => !selectedIds.has(String(i.variant_id)));
            if (remaining.length === 0) localStorage.removeItem(CART_STORAGE_KEY);
            else saveCart(remaining);
            selectedIds.clear();
            renderCart();
            alert('Заказ успешно создан! Номер заказа: ' + data.order_id);
        } else {
            // Сюда попадают детальные ошибки из исправленного PHP-кода
            alert('Ошибка при создании заказа: ' + data.message);
        }
    } catch (err) {
        console.error(err);
        // Сюда попадают ошибки сети или невозможности парсинга JSON
        alert('Произошла ошибка при отправке запроса.');
    }
}
</script>
</body>
</html>