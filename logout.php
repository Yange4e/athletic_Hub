<?php
session_start();
// Удаляем все переменные сессии
$_SESSION = [];
// Если сессия использует куки — удаляем кукию сессии в браузере
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}


session_destroy();
header("Location: login.php");
exit();
?>