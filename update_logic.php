<?php  
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- ШАГ 1: ПОДКЛЮЧЕНИЕ К БД ---
 
require_once 'db_connection.php';

// --- 2. ПРОВЕРКА, ЧТО ЭТО POST-ЗАПРОС ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      // --- 3. ПОЛУЧЕНИЕ И ОЧИСТКА ДАННЫХ ИЗ ФОРМЫ ---
    // Получи name, email, phone и, самое важное, client_id из $_POST.

    $name = $_POST['user_name'] ?? '';
    $email = $_POST['user_email'] ?? '';
    $phone = $_POST['user_phone'] ?? '';
    $status_id = $_POST['status_id'] ?? '';
    // Используй ?? '' и trim() для очистки.

    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    $status_id = trim($status_id);

    // Не забудь получить client_id и преобразовать его в (int).
    
    $client_id = (int)$_POST['client_id'] ?? 0;



    // 4. ВАЛИДАЦИЯ ДАННЫХ (Validation)
    $errors = []; // Массив для хранения сообщений об ошибках

    // Проверка имени
    if (empty($name)) {
        $errors[] = 'Имя не может быть пустым.';
    } 

    // Проверка email
    if (empty($email)) {
        $errors[] = 'Email не может быть пустым.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный формат email.';
    }

    // Проверка phone
    if (empty($phone)) {
        $errors[] = 'Phone не может быть пустым.';
    } elseif (strlen($phone) < 10 || strlen($phone) > 15) {  
        $errors[] = 'Телефон должен содержать от 10 до 15 цифр.';
    }

    // Проверка status_id

if ($status_id <= 0) {
    $errors[] = 'Необходимо выбрать корректный статус.';
}



    if (empty($errors) && $client_id > 0) {

        // 1. Создаем строку SQL-запроса с плейсхолдерами
    $sql = "UPDATE clients SET name = ?, email = ?, phone = ?, status_id = ? WHERE id = ?";
    
    // 2. Подготавливаем выражение
    $stmt = $pdo->prepare($sql);
    
    // 3. Выполняем, передавая массив с данными.
    // ВАЖНО: порядок элементов в массиве должен соответствовать порядку '?' в запросе!
    $stmt->execute([$name, $email, $phone, $status_id, $client_id]);

    $_SESSION['success_message'] = "Клиент с ID #" . $client_id . " успешно обновлен.";

     // 4. После успешного обновления перенаправляем пользователя на главный список
    header('Location: backend_lab.php');
    exit();
    } else {
    // Если ошибки есть, нужно их показать пользователю.
    // Можно просто вывести их или сделать более красивую страницу с ошибкой.
    echo "<h1>Обнаружены ошибки:</h1>";
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . "<br>";
    }
    // И даем ссылку, чтобы он мог вернуться и исправить
    echo '<br><a href="edit.php?id=' . $client_id . '">Вернуться к редактированию</a>';
}
}

?>