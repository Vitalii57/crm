<?php
// Файл: logout.php

// 1. Запускаем сессию, чтобы получить к ней доступ
session_start();

// 2. Очищаем массив $_SESSION от всех данных
$_SESSION = [];

// 3. Уничтожаем сессию
session_destroy();

// 4. Перенаправляем пользователя на страницу входа
header('Location: login.php');
exit();
?>