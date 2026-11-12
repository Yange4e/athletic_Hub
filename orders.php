<?php
session_start();
// Проверка авторизации
if(!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// Коннект к правильной БД
$conn = new mysqli("localhost","root","","athletic_hub");
$conn->set_charset("utf8mb4");

$user_id_session = $_SESSION['user_id'] ?? null;

// === ОБРАБОТКА УДАЛЕНИЯ ЗАКАЗА ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    header('Content-Type: application/json');

    $order_id = intval($_POST['order_id'] ?? 0);

    if (!$user_id_session) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: Пользователь не авторизован.']);
        exit();
    }
    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: Неверный ID заказа.']);
        exit();
    }

    // 1. Проверяем, принадлежит ли заказ пользователю и имеет ли он статус 'pending'
    $delete_stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    
    if ($delete_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка подготовки SQL: ' . $conn->error]);
        exit();
    }

    $delete_stmt->bind_param("ii", $order_id, $user_id_session);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Заказ №' . $order_id . ' успешно удален.']);
        } else {
            // Либо не найден, либо статус уже не 'pending'
            echo json_encode(['success' => false, 'message' => 'Заказ не найден, не принадлежит вам, либо его статус уже не "В ожидании" и отменить нельзя.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при выполнении удаления: ' . $delete_stmt->error]);
    }

    exit();
}
// === КОНЕЦ ОБРАБОТКИ УДАЛЕНИЯ ЗАКАЗА ===

// Выборка заказов для отображения
$stmt = $conn->prepare("SELECT id, created_at, status, total_amount, items_json FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id_session);
$stmt->execute();
$result = $stmt->get_result();

// Вспомогательная функция для форматирования валюты
function formatCurrency($amount) {
    return number_format($amount, 0, '', ' ') . ' ₸';
}

// Функция для получения стилей статуса
function getStatusDisplay($status) {
    $statusText = [
        'pending' => ['В ожидании', 'status-pending'],
        'processing' => ['В обработке', 'status-processing'],
        'shipped' => ['Отправлен', 'status-shipped'],
        'delivered' => ['Доставлен', 'status-delivered'],
        'cancelled' => ['Отменен', 'status-cancelled'],
    ];
    $display = $statusText[$status] ?? ['Неизвестно', 'status-unknown'];
    return "<span class='order-status {$display[1]}'>{$display[0]}</span>";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы — ATHLETIC HUB</title>
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
            --accent: #2C7873;
            --grad-start: #ffffff;
            --grad-end: #000000;
            --success: #4CAF50;
        }

        * { box-sizing: border-box; }
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
            z-index: 1200;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.2rem; color: #000; }
        .logo i { color: var(--accent); }
        .nav-menu { display: flex; gap: 22px; }
        .nav-link { font-weight: 600; padding: 8px 12px; border-radius: 10px; transition: 0.2s; color: #000; }
        .nav-link:hover { background: rgba(255,255,255,0.3); }
        .nav-link.active { background: rgba(0,0,0,0.4); color: #000000; }
        .auth-area { display: flex; gap: 10px; align-items: center; }
        .auth-btn { padding: 8px 14px; border-radius: 10px; background: #000; color: #fff; font-weight: 600; font-size: 14px; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; transition: 0.2s; }
        .auth-btn:hover { background: #333; }
        .auth-btn.register { background: #333; }
        .burger { display: none; border: none; background: none; font-size: 24px; cursor: pointer; color: #fff; }

        /* ОСНОВНОЙ КОНТЕНТ */
        main {
            max-width: 1000px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        h1 { 
            font-size: 32px; 
            margin-bottom: 30px; 
            color: var(--primary);
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        
        /* Стили для таблицы */
        .order-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        .order-table th {
            text-align: left;
            padding: 15px 20px;
            font-size: 14px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            background: #fff;
            position: sticky; top: 72px; z-index: 100;
        }
        .order-table td {
            padding: 20px;
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
            vertical-align: middle;
            font-size: 15px;
            font-weight: 500;
            border: none;
        }
        .order-table td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .order-table td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        
        /* Стили для статусов */
        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
            display: inline-block;
        }
        .status-pending { background: #ffeb3b; color: #a37200; } 
        .status-processing { background: #2196F3; color: #fff; } 
        .status-shipped { background: #ff9800; color: #fff; } 
        .status-delivered { background: #4CAF50; color: #fff; } 
        .status-cancelled { background: #f44336; color: #fff; } 

        .detail-link {
            color: var(--accent);
            font-weight: 600;
            transition: color 0.2s;
            margin-right: 15px;
        }
        .detail-link:hover { text-decoration: underline; color: #333; }

        .no-orders {
            background: var(--card-bg);
            padding: 50px;
            text-align: center;
            border-radius: 14px;
            box-shadow: var(--card-shadow);
            font-size: 18px;
            color: var(--muted);
        }

        /* НОВАЯ КНОПКА УДАЛЕНИЯ */
        .delete-btn {
            background: none;
            border: none;
            color: var(--danger); /* Красный цвет */
            font-size: 16px;
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: 0.2s;
            line-height: 1;
        }
        .delete-btn:hover {
            background: rgba(244, 67, 54, 0.1);
            color: #d32f2f;
        }
        .action-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
    </style>
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
    <a href="orders.php"class="nav-link active">Мои Заказы</a>
    <a href="about.php" class="nav-link ">О нас</a>
    <a href="contacts.php" class="nav-link">Контакты</a>
  </nav>

  <div class="auth-area">
    <?php if (! isset($_SESSION['user_id'])): ?>
      <a href="auth.php" class="auth-btn">Войти</a>
      <a href="register.php" class="auth-btn register">Регистрация</a>
    <?php else: ?>
      <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Пользователь'); ?></span>
      <a href="profile.php" class="auth-btn">Профиль</a>
      <a href="logout.php" class="auth-btn logout">Выйти</a>
    <?php endif; ?>
  </div>

  <button class="burger" id="burger" aria-label="Открыть меню">
    <i class="fa-solid fa-bars"></i>
  </button>
</header>

<main>
    <h1><i class="fa-solid fa-box-archive"></i> Мои заказы</h1>
    <?php if ($result->num_rows == 0): ?>
        <div class="no-orders">
            У вас пока нет оформленных заказов.
            <p><a href="catalog.php" class="detail-link" style="font-size: 16px;">Начать покупки в каталоге</a></p>
        </div>
    <?php else: ?>
        <table class="order-table">
            <thead>
                <tr>
                    <th style="width:10%;">Заказ №</th>
                    <th style="width:25%;">Создан</th>
                    <th style="width:20%;">Статус</th>
                    <th style="width:25%; text-align:right;">Сумма</th>
                    <th style="width:20%; text-align:center;">Действия</th> 
                </tr>
            </thead>
            <tbody>
            <?php while($order = $result->fetch_assoc()): ?>
                <tr id="order-row-<?php echo $order['id']; ?>">
                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                    <td><?php echo getStatusDisplay($order['status']); ?></td>
                    <td style="text-align:right; font-weight:700; color:var(--accent);"><?php echo formatCurrency($order['total_amount']); ?></td>
                    <td style="text-align:center;">
                        <div class="action-cell">
                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="detail-link">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <?php if ($order['status'] === 'pending'): ?>
                            <button type="button" class="delete-btn" title="Удалить заказ" onclick="deleteOrder(<?php echo $order['id']; ?>)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<script>
    /**
     * Отправляет запрос на удаление заказа.
     * @param {number} orderId - ID заказа для удаления.
     */
    function deleteOrder(orderId) {
        // Запрашиваем подтверждение
        if (!confirm(`Вы уверены, что хотите отменить/удалить заказ №${orderId}? Это действие необратимо и возможно только для заказов со статусом "В ожидании".`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_order');
        formData.append('order_id', orderId);

        fetch('orders.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Проверяем, что ответ — JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                // Если не JSON, значит, произошла ошибка PHP, которую мы не поймали
                throw new Error('Получен не-JSON ответ. Возможно, критическая ошибка PHP.');
            }
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Перезагружаем страницу, чтобы обновить список заказов
                window.location.reload(); 
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Ошибка при удалении:', error);
            alert('Произошла ошибка сети или сервера. См. консоль для деталей.');
        });
    }
</script>
</body>
</html>