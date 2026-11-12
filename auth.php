<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("localhost", "root", "", "athletic_hub");
    
    if ($conn->connect_error) {
        $error = "❌ Ошибка подключения: " . $conn->connect_error;
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = "⚠️ Пожалуйста, заполните все поля!  ";
        } else {
            // Ищем пользователя по email
            $query = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
            
            if (! $query) {
                $error = "❌ Ошибка запроса: " . $conn->error;
            } else {
                $query->bind_param("s", $email);
                $query->execute();
                $result = $query->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    // Проверяем пароль
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "❌ Неверный пароль! ";
                    }
                } else {
                    $error = "❌ Пользователь с email '$email' не найден!";
                }
                $query->close();
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
    <title>Вход — ATHLETIC HUB</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
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

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }

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
        }

        .error-message i {
            font-size: 18px;
        }

        .login-btn {
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

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #999;
        }

        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-section">
        <i class="fa-solid fa-dumbbell"></i>
        <h1>ATHLETIC HUB</h1>
        <p style="color: #999; margin: 0;">Вход в аккаунт</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-message">
            <i class="fa-solid fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="user@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" placeholder="••••••" required>
        </div>

        <button type="submit" class="login-btn">
            <i class="fa-solid fa-sign-in-alt"></i> Войти
        </button>
    </form>

    <div class="register-link">
        Нет аккаунта?  <a href="register.php">Создать</a>
    </div>
</div>

</body>
</html>