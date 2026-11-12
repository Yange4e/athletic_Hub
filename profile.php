<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверка авторизации
if (empty($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "athletic_hub");

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$uid = (int)$_SESSION['user_id'];

// Получаем данные пользователя
$query = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?  LIMIT 1");
$query->bind_param('i', $uid);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();

if (! $user) {
    header('Location: auth.php');
    exit();
}

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $current_password = trim($_POST['current_password'] ??  '');

    // Валидация
    if (empty($name)) {
        $errors[] = '❌ Имя не может быть пустым. ';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '❌ Неверный email.';
    }

    // Проверка уникальности email (если изменили)
    if ($email !== $user['email']) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?  AND id != ?  LIMIT 1");
        $check->bind_param('si', $email, $uid);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $errors[] = '❌ Email уже используется.';
        }
        $check->close();
    }

    // Смена пароля
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = '❌ Новый пароль минимум 6 символов.';
        }

        if (empty($current_password)) {
            $errors[] = '❌ Введите текущий пароль для смены пароля.';
        } else {
            // Получаем хеш текущего пароля
            $pw_query = $conn->prepare("SELECT password FROM users WHERE id = ?  LIMIT 1");
            $pw_query->bind_param('i', $uid);
            $pw_query->execute();
            $pw_result = $pw_query->get_result()->fetch_assoc();
            $pw_query->close();

            if (!password_verify($current_password, $pw_result['password'])) {
                $errors[] = '❌ Текущий пароль неверен.';
            }
        }
    }

    // Если нет ошибок - сохраняем
    if (empty($errors)) {
        if (! empty($new_password)) {
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $update->bind_param('sssi', $name, $email, $new_password_hash, $uid);
        } else {
            $update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $update->bind_param('ssi', $name, $email, $uid);
        }

        if ($update->execute()) {
            $messages[] = '✓ Профиль обновлён успешно!';
            // Обновляем данные в переменной
            $user['name'] = $name;
            $user['email'] = $email;
            $_SESSION['user_name'] = $name;
        } else {
            $errors[] = '❌ Ошибка при сохранении: ' . $conn->error;
        }
        $update->close();
    }
}

$conn->close();
?>
<! DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль — ATHLETIC HUB</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #000000;
            --text: #000000;
            --accent: #007BFF;
            --bg: #f4f4f4;
            --card-bg: #ffffff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            background: linear-gradient(90deg, #ffffff, #000000);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1200;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
        }

        .profile-container {
            max-width: 900px;
            margin: 120px auto 40px;
            padding: 20px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .message.error {
            background: #ffe0e0;
            color: #d32f2f;
        }

        .profile-form {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent), #0056b3);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
        }

        hr {
            margin: 30px 0;
            border: none;
            border-top: 1px solid #e0e0e0;
        }

        h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--primary);
        }

        @media (max-width: 600px) {
            .profile-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- ШАПКА -->
    <header class="top-nav">
        <a class="logo" href="index.php"><i class="fa-solid fa-dumbbell"></i><span>ATHLETIC HUB</span></a>
        <a href="logout.php" style="color: #fff; font-weight: 600;">Выход</a>
    </header>

    <!-- ПРОФИЛЬ -->
    <main class="profile-container">
        <h1>👤 Мой профиль</h1>

        <?php foreach($messages as $msg): ?>
            <div class="message success">
                <i class="fa-solid fa-check-circle"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endforeach; ?>

        <?php foreach($errors as $err): ?>
            <div class="message error">
                <i class="fa-solid fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($err); ?>
            </div>
        <?php endforeach; ?>

        <div class="profile-form">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Полное имя</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?php echo htmlspecialchars($user['name']); ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($user['email']); ?>" 
                        required
                    >
                </div>

                <hr>

                <h3>🔐 Изменить пароль (опционально)</h3>

                <div class="form-group">
                    <label for="current_password">Текущий пароль</label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        placeholder="Введите текущий пароль"
                    >
                </div>

                <div class="form-group">
                    <label for="new_password">Новый пароль (минимум 6 символов)</label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        placeholder="Введите новый пароль"
                    >
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-save"></i> Сохранить изменения
                </button>
            </form>
        </div>
    </main>

</body>
</html>