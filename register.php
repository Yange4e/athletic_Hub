<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("localhost", "root", "", "athletic_hub");
    
    if ($conn->connect_error) {
        $error = "Ошибка подключения: " . $conn->connect_error;
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Валидация
        if (empty($name) || empty($email) || empty($password)) {
            $error = "Пожалуйста, заполните все поля!  ";
        } elseif ($password !== $password_confirm) {
            $error = "Пароли не совпадают! ";
        } elseif (strlen($password) < 6) {
            $error = "Пароль должен быть минимум 6 символов! ";
        } else {
            // Хеширование пароля
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            
            // Проверка на дубликат email (используем 'email', а не 'username')
            $check_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if (! $check_query) {
                $error = "Ошибка запроса: " . $conn->error;
            } else {
                $check_query->bind_param("s", $email);
                $check_query->execute();
                $check_result = $check_query->get_result();
                
                if ($check_result->num_rows > 0) {
                    $error = "Email уже зарегистрирован! ";
                } else {
                    // Вставка в БД (используем колонки: name, email, password)
                    $insert_query = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    if (!$insert_query) {
                        $error = "Ошибка запроса: " . $conn->error;
                    } else {
                        $insert_query->bind_param("sss", $name, $email, $password_hashed);
                        
                        if ($insert_query->execute()) {
                            $success = "✓ Регистрация успешна! Переводим на вход...  ";
                            header("refresh:2; url=auth.php");
                        } else {
                            $error = "Ошибка при регистрации: " . $insert_query->error;
                        }
                        $insert_query->close();
                    }
                }
                $check_query->close();
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — ATHLETIC HUB</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2? family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section i {
            font-size: 40px;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .logo-section h1 {
            font-size: 24px;
            margin: 10px 0;
            color: var(--primary);
        }

        .logo-section p {
            color: #999;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
            font-size: 14px;
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

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .error-message {
            background: #ffe0e0;
            color: #d32f2f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: shake 0.3s;
        }

        .error-message i { font-size: 18px; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .success-message i { font-size: 18px; }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .register-btn {
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

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #999;
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #0056b3;
        }

        .terms {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-top: 20px;
            line-height: 1.6;
        }

        .terms a {
            color: var(--accent);
            text-decoration: none;
        }

        @media (max-width: 500px) {
            .register-container {
                padding: 30px 20px;
            }

            .logo-section h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="logo-section">
        <i class="fa-solid fa-dumbbell"></i>
        <h1>ATHLETIC HUB</h1>
        <p>Создайте аккаунт</p>
    </div>

    <?php if (!  empty($error)): ?>
        <div class="error-message">
            <i class="fa-solid fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (! empty($success)): ?>
     <div class="success-message">
            <i class="fa-solid fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="form-group">
            <label for="name">Полное имя</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                placeholder="Иван Петров" 
                required
                minlength="3"
                maxlength="100"
            >
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="ivan@example.com" 
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="••••••" 
                required
                minlength="6"
            >
            <div class="password-hint">Минимум 6 символов</div>
        </div>

        <div class="form-group">
            <label for="password_confirm">Подтвердите пароль</label>
            <input 
                type="password" 
                id="password_confirm" 
                name="password_confirm" 
                placeholder="••••••" 
                required
                minlength="6"
            >
        </div>

        <button type="submit" class="register-btn">
            <i class="fa-solid fa-user-plus"></i> Создать аккаунт
        </button>
    </form>

    <div class="login-link">
        Уже есть аккаунт? <a href="auth. php">Войти</a>
    </div>

    <div class="terms">
        Создавая аккаунт, вы согласны с нашими<br>
        <a href="#">условиями использования</a> и <a href="#">политикой конфиденциальности</a>
    </div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;

        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Пароли не совпадают! ');
        }
    });
</script>

</body>
</html>