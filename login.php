<?php
// Подключаем наш файл с подключением к БД и запуском сессии
require_once 'db_connection.php';

// Массив для хранения ошибок
$errors = [];

// Проверяем, была ли форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Получаем данные из формы
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 2. Валидация (простая)
    if (empty($username) || empty($password)) {
        $errors[] = 'Логин и пароль не могут быть пустыми.';
    } else {
        // --- ЛОГИКА ПРОВЕРКИ ---
        try {
            // а) Ищем пользователя в БД по его логину
            //    Подготовь SQL-запрос SELECT, который ищет пользователя по `username`.
            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // б) Проверяем, найден ли пользователь И совпадает ли пароль
            //    password_verify() - функция PHP для безопасного сравнения пароля с хешем.
            //    Она принимает: 1. Что ввел пользователь. 2. Хеш из БД.
            if ($user && password_verify($password, $user['password'])) {
                // ПАРОЛЬ ВЕРНЫЙ!
                
                // в) Записываем информацию о пользователе в сессию.
                //    Это "ключ", по которому мы будем знать, что пользователь вошел.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; 


                // г) Перенаправляем на главную страницу CRM
                header('Location: backend_lab.php');
                exit();
                
            } else {
                // Пользователь не найден или пароль неверный
                $errors[] = 'Неверный логин или пароль.';
            }

        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в CRM</title>
</head>
<body>

    <h1>Вход в систему</h1>

    <?php 
    // Выводим ошибки, если они есть
    if (!empty($errors)) {
        echo '<div style="color: red;">';
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . '<br>';
        }
        echo '</div>';
    }
    ?>

    <form action="login.php" method="POST">
        <p>
            <label for="username">Логин:</label><br>
            <input type="text" name="username" id="username">
        </p>
        <p>
            <label for="password">Пароль:</label><br>
            <input type="password" name="password" id="password">
        </p>
        <p>
            <button type="submit">Войти</button>
        </p>
    </form>

</body>
</html>