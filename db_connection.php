<?php 
// session_start();

// // --- НАСТРОЙКИ ПОДКЛЮЧЕНИЯ К БД ---

// $db_host = 'localhost';
// $db_name = 'wp_test_site';
// $db_user = 'root';
// $db_pass = 'root';
// $charset = 'utf8mb4';

// // --- СОЗДАНИЕ ПОДКЛЮЧЕНИЯ PDO ---
// // DSN (Data Source Name) - строка с информацией для подключения

// $dsn =  "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// // Опции для PDO
// $options = [
//     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Включаем режим выбрасывания исключений при ошибках
//     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Устанавливаем режим выборки данных по умолчанию (ассоциативный массив)
//     PDO::ATTR_EMULATE_PREPARES   => false,                  // Отключаем эмуляцию подготовленных запросов для безопасности
// ];

// try {
//     $pdo = new PDO($dsn, $db_user, $db_pass, $options); 
// } catch (\PDOException $e) {
//      throw new \PDOException($e->getMessage(), (int)$e->getCode());
// }



// Файл: db_connection.php

// Включаем отображение ошибок и запускаем сессию
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Подключаем наш новый класс
require_once 'Database.php';

// --- Создаем объект PDO для использования во всем приложении ---

// 1. Создаем экземпляр нашего класса Database
$database = new Database();

// 2. Вызываем у него метод connect(), чтобы получить объект PDO
$pdo = $database->connect();

// Теперь в переменной $pdo лежит готовый к работе объект PDO,
// как и раньше. Весь остальной код будет работать без изменений.


?>
