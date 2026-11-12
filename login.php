<?php
require 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Ищем пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Успешная авторизация
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_id'] = $user['id'];

        header("Location: profile.php");
        exit();
    } else {
        $error = "Неверный email или пароль.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
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

        .error-message i { 
            font-size: 18px; 
        }

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

        .success-message i { 
            font-size: 18px; 
        }

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

  <div class="auth-container">
    <h2>Вход в аккаунт</h2>

    <form method="post">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Пароль" required />
      <button type="submit">Войти</button>
    </form>

    <p>Нет аккаунта? <a href="register.php">Регистрация</a></p>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  </div>
</body>
</html>
