<?php
session_start();
if(!isset($_SESSION['user_id'])) {header("Location: auth.php");exit();}
$conn = new mysqli("localhost","root","","athletic_hub");
$conn->set_charset("utf8mb4");
$order_id = intval($_GET['order_id'] ?? 0);
$user_id = $_SESSION['user_id'];
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id")->fetch_assoc();
if(!$order) { echo "Заказ не найден"; exit();}
$items = json_decode($order['items_json'], true);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><title>Заказ #<?php echo $order['id']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="top-nav">
     <a href="index.php" class="logo" id="logo">
    <i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span>
  </a>

  <nav class="nav-menu" aria-label="Главное меню">
    <a href="index.php" class="nav-link">Главная</a>
    <a href="catalog.php" class="nav-link">Каталог</a>
    <a href="cart.php" class="nav-link">Корзина</a>
    <a href="orders.php"class="nav-link active">Мои Заказ</a>
    <a href="about. php" class="nav-link ">О нас</a>
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
</header>
<main style="max-width:700px;margin:100px auto 40px;">
    <h2>Заказ #<?php echo $order['id']; ?></h2>
    <div>Дата: <?php echo date('d.m.Y H:i',strtotime($order['created_at'])); ?></div>
    <div>Статус: <b><?php echo htmlspecialchars($order['status']); ?></b></div>
    <div>Сумма: <b><?php echo number_format($order['total_amount'], 0, '', ' '); ?> ₸</b></div>
    <h3>Товары:</h3>
    <ul>
        <?php foreach($items as $it): ?>
            <li>
                <?php
                $vinfo = $conn->query("SELECT variant_name FROM product_variants WHERE id=".(int)$it['variant_id'])->fetch_assoc();
                ?>
                <?php echo htmlspecialchars($vinfo['variant_name'] ?? 'Вариант'); ?> — <?php echo (int)$it['quantity'];?> шт.
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="orders.php">← Назад к заказам</a>
</main>
</body>
</html>