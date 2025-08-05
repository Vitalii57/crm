<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- ШАГ 1: ПОДКЛЮЧЕНИЕ К БД ---
// Этот блок можно просто скопировать из backend_lab.php

require_once 'db_connection.php';
require_once 'Client.php';

// --- ШАГ 2: ПОЛУЧЕНИЕ И ПРОВЕРКА ID КЛИЕНТА ---
// 1. Получи id из GET-запроса и преврати его в целое число.

//    Присвой его переменной $client_id.

$client_id = (int)($_GET['id'] ?? 0);

// echo $client_id;

// 2. Проверь, что $client_id > 0. Если нет, нужно прервать выполнение скрипта.
//    Можно использовать die("Некорректный ID клиента.");

if($client_id <= 0) {
    die("Ошибка: ID клиента не указан.");
}


// --- ШАГ 3: ПОЛУЧЕНИЕ ДАННЫХ КЛИЕНТА ИЗ БД ---


// --- ПОЛУЧЕНИЕ ДАННЫХ КЛИЕНТА (НОВЫЙ ООП-СПОСОБ) ---
$client = new Client($pdo); // Создаем объект (конструктор пустой)
// Вызываем метод find() И СРАЗУ ЖЕ проверяем его результат (то, что он вернет: true или false)
if ( !$client->find($client_id, $pdo) ) {
    // Если find() вернул false, то заходим сюда
    die("Клиент с ID $client_id не найден.");
}

// --- ПОЛУЧАЕМ ВСЕ СТАТУСЫ ДЛЯ ВЫПАДАЮЩЕГО СПИСКА ---
// (Этот код оставляем, так как для него мы еще не создали класс)
$statuses = [];
try {
    $statuses_stmt = $pdo->query("SELECT * FROM pipeline_statuses ORDER BY sort_order ASC");
    $statuses = $statuses_stmt->fetchAll();
} catch (\PDOException $e) {
    // Обработка ошибок
}
?>



<!-- --- ШАГ 4: HTML-ФОРМА С ЗАПОЛНЕННЫМИ ДАННЫМИ --- -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование клиента</title>
</head>
<body>

    <h1>Редактирование клиента #<?php echo htmlspecialchars($client->id); ?></h1>

    <!-- Форма будет отправлять данные на ДРУГОЙ файл-обработчик или на этот же,
         но мы пока сделаем только отображение -->
    <form action="update_logic.php" method="POST"> <!-- (update_logic.php мы создадим позже) -->
        
        <input type="hidden" name="client_id" value="<?php echo $client -> id; ?>/>">

        <!-- 7. Выведи в атрибуты value полей формы текущие данные клиента,
             которые хранятся в массиве $client. Не забудь про htmlspecialchars(). -->
        <p>
            <label for="name_id">Имя:</label><br>
            <input type="text" name="user_name" id="name_id" value="<?php echo htmlspecialchars($client -> name); ?>"> <!-- ... твой код вывода имени ... -->
        </p>
        <p>
            <label for="email_id">Email:</label><br>
            <input type="text" name="user_email" id="email_id" value="<?php echo htmlspecialchars($client ->email); ?>"> <!-- ... твой код вывода email ... -->
        </p>
         <p>
            <label for="phone_id">Телефон:</label><br>
            <input type="tel" name="user_phone" id="phone_id" value="<?php echo htmlspecialchars($client -> phone); ?>"> <!-- ... твой код вывода телефона ... -->
        </p>
        <p>
    <label for="status_id">Статус:</label><br>
    <select name="status_id" id="status_id">
        <?php foreach ($statuses as $status): ?>
    <option 
        value="<?php echo $status['id']; ?>"
        <?php if ($client->status_id == $status['id']) echo 'selected'; ?>
    >
        <?php echo htmlspecialchars($status['name']); ?>
    </option>
<?php endforeach; ?>
    </select>
</p>
        <p>
            <button type="submit">Сохранить изменения</button>
        </p>
    </form>
    
    <br>
    <a href="backend_lab.php">Вернуться к списку</a>

</body>
</html>