<?php
require_once 'db_connection.php';
require_once 'Client.php';

// --- ПРОВЕРКА, ЧТО ЭТО POST-ЗАПРОС ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. ПОЛУЧЕНИЕ И ОЧИСТКА ДАННЫХ ---
    $name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['user_email'] ?? '');
    $phone = trim($_POST['user_phone'] ?? '');
    $status_id = (int)($_POST['status_id'] ?? 0);
    $client_id = (int)($_POST['client_id'] ?? 0);

    // --- 2. ВАЛИДАЦИЯ ДАННЫХ ---
    $errors = [];
    // ... (весь твой код валидации для name, email, phone, status_id) ...
    if (empty($name)) { $errors[] = 'Имя не может быть пустым.'; }
    // и т.д.


    // --- 3. ПРОВЕРКА, ЧТО НЕТ ОШИБОК ВАЛИДАЦИИ ---
    if (empty($errors)) {
        
        // --- 4. ПРОВЕРКА ПРАВ ДОСТУПА НА РЕДАКТИРОВАНИЕ ---
        $client_to_check = new Client($pdo);
        $current_user_id = $_SESSION['user_id'];
        $current_user_role = $_SESSION['role'];

        if (!$client_to_check->find($client_id, $current_user_id, $current_user_role)) {
            die("Ошибка: Клиент не найден или у вас нет прав на его редактирование.");
        }

        // --- 5. СОХРАНЕНИЕ ДАННЫХ ЧЕРЕЗ ОБЪЕКТ ---
        $client_to_save = new Client($pdo);
        // Наполняем объект данными из формы
        $client_to_save->id = $client_id;
        $client_to_save->name = $name;
        $client_to_save->email = $email;
        $client_to_save->phone = $phone;
        $client_to_save->status_id = $status_id;
        // user_id мы не меняем, он остается за тем, кто создал клиента

        if ($client_to_save->save()) {
            $_SESSION['success_message'] = "Клиент с ID #" . $client_to_save->id . " успешно обновлен.";
            header('Location: backend_lab.php');
            exit();
        } else {
            die("Произошла ошибка при сохранении данных.");
        }

    } else {
        // --- ВЫВОД ОШИБОК ВАЛИДАЦИИ ---
        echo "<h1>Обнаружены ошибки:</h1>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
        echo '<br><a href="edit.php?id=' . $client_id . '">Вернуться к редактированию</a>';
    }
}
?>