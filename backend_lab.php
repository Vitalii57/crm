<?php 

// --- НАСТРОЙКИ ПОДКЛЮЧЕНИЯ К БД ---

require_once 'db_connection.php';

// --- НАШ "ОХРАННИК" ---
// Проверяем, что в сессии НЕ СУЩЕСТВУЕТ ключа 'user_id'
if (!isset($_SESSION['user_id'])) {
    // Если ключа нет, значит, пользователь не вошел.
    // Перенаправляем его на страницу входа.
    header('Location: login.php');
    exit(); // И немедленно останавливаем выполнение скрипта
}
// --- КОНЕЦ "ОХРАННИКА" ---

require_once 'Client.php';

// --- НОВЫЙ БЛОК: ОБРАБОТКА GET-ЗАПРОСОВ (на удаление) ---

// Проверяем, что в GET-запросе есть 'action', и он равен 'delete'
// if(isset($_GET['action']) && $_GET['action'] === 'delete') {
 
//     $id_to_delete = (int)$_GET['id'] ?? 0;

//     if($id_to_delete > 0) {
//         $sql = "DELETE FROM clients WHERE id = ? ";
    
//         // Подготовка выражения
//         $stmt = $pdo->prepare($sql);
//         // Выполняем запрос, передавая реальный id в массиве.
//         $stmt->execute([$id_to_delete]);
        
//         $_SESSION['success_message'] = "Клиент с ID #" . $id_to_delete . " успешно удален.";

//         header('Location: backend_lab.php');
//         exit();
        

//         // $errors[] = 'у удаляемого эдемента нет id';
//     }

// }

// --- ОБРАБОТКА GET-ЗАПРОСОВ (ООП-СПОСОБ) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {

    $id_to_delete = (int)($_GET['id'] ?? 0);

    if ($id_to_delete > 0) {
        // Вызываем статический метод и проверяем результат
        if (Client::deleteById($pdo, $id_to_delete)) {
            $_SESSION['success_message'] = "Клиент с ID #" . $id_to_delete . " успешно удален.";
        } else {
            // Это необязательно, но хорошая практика на случай ошибки
            $_SESSION['error_message'] = "Не удалось удалить клиента с ID #" . $id_to_delete . ".";
        }
        
        // В любом случае делаем редирект
        header('Location: backend_lab.php');
        exit();
    }
}




// --- Блок обработки данных ---
// Этот код будет выполняться, только если форма отправлена методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ПОЛУЧЕНИЕ ДАННЫХ
    $name = $_POST['user_name'] ?? '';
    $email = $_POST['user_email'] ?? '';
    $phone = $_POST['user_phone'] ?? '';
    $user_id = $_SESSION['user_id']; 
    
    // 2. ОЧИСТКА ДАННЫХ (Sanitization)
    // Удаляем пробелы в начале и в конце строки
    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);


    // Удаляем HTML и PHP теги из имени
    $name = strip_tags($name);

    // Специальная очистка для email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Специальная очистка для phone
    $phone = preg_replace('/\D/', '', $phone);

    // 3. ВАЛИДАЦИЯ ДАННЫХ (Validation)
    $errors = []; // Массив для хранения сообщений об ошибках

    // Проверка имени
    if (empty($name)) {
        $errors[] = 'Имя не может быть пустым.';
    } elseif (mb_strlen($name) < 2) { // mb_strlen для правильной работы с кириллицей
        $errors[] = 'Имя слишком короткое.';
    }

    // Проверка email
    if ( !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        $errors[] = 'Некорректный формат email.';
    }

    // Проверка phone
    if (empty($phone)) {
        $errors[] = 'Phone не может быть пустым.';
    } elseif (strlen($phone) < 10 || strlen($phone) > 15) {  
        $errors[] = 'Телефон должен содержать от 10 до 15 цифр.';
    }
    
    // 4. ВЫПОЛНЕНИЕ ДЕЙСТВИЯ (пока просто выводим результат)
    if (empty($errors)) {
        // Если ошибок нет
        // 1. Подготовка SQL-запроса с плейсхолдерами (?)

//     $sql = "INSERT INTO clients (name, email, phone) VALUES (?, ?, ?)";
    
//     // 2. Подготовка выражения
//     $stmt = $pdo->prepare($sql);
    
//     // 3. Выполнение запроса с передачей реальных данных

//     try {
//     $stmt->execute([$name, $email, $phone]); 
    
//     // Записываем сообщение в сессию
//     $_SESSION['success_message'] = "Новый клиент '" . htmlspecialchars($name) . "' успешно добавлен.";

//     // Делаем редирект
//     header('Location: backend_lab.php');
//     exit();

// }  catch (\PDOException $e) {
//         // Если произошла ошибка при выполнении запроса
//         echo "<h2>Ошибка при добавлении клиента: " . $e->getMessage() . "</h2>";
//     }
// 1. Создаем объект Client
    $client = new Client($pdo);

    // 2. Наполняем объект данными из формы и сессии
    $client->name = $name;
    $client->email = $email;
    $client->phone = $phone;
    $client->status_id = 1; // Новый клиент всегда получает статус 1
    $client->user_id = $user_id; // Привязываем к текущему пользователю

    // 3. Вызываем метод save(). Он сам сделает INSERT.
    if ($client->save()) {
        $_SESSION['success_message'] = "Новый клиент '" . htmlspecialchars($client->name) . "' успешно добавлен.";
        header('Location: backend_lab.php');
        exit();
    } else {
        die("Произошла ошибка при добавлении клиента.");
    }
    // --- КОНЕЦ НОВОГО КОДА ---

    } else {
        // Если есть ошибки, выводим их
        echo "<h2>Обнаружены ошибки:</h2>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
    }

    echo "<hr>"; // Разделитель, чтобы отделить результат от формы
}



// --- НОВЫЙ БЛОК: ВЫВОД СПИСКА КЛИЕНТОВ ---

// 1. Готовим SQL-запрос на получение всех данных из таблицы clients

// ORDER BY id DESC - чтобы новые клиенты были сверху
// $sql_select = "SELECT * FROM clients ORDER BY id DESC";

// $sql_select = "SELECT
//     clients.*, 
//     pipeline_statuses.name AS status_name 
// FROM 
//     clients 
// LEFT JOIN 
//     pipeline_statuses ON clients.status_id = pipeline_statuses.id
// ORDER BY 
//     clients.id DESC";

// 2. Выполняем запрос
// $stmt_select = $pdo->query($sql_select);

// 3. Получаем все строки результата в виде ассоциативного массива
// fetchAll() забирает все строки сразу
// $clients = $stmt_select->fetchAll();

// --- НОВЫЙ ООП-СПОСОБ ---
// $clients = Client::findAll($pdo);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; 
$clients = Client::findAll($pdo, $user_id, $user_role);




?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лаборатория PHP</title>
</head>
<body>
    <div style="padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; background-color: #f4f4f4;">
        Вы вошли как: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
    |
        <a href="logout.php">Выйти</a>
    </div>

    <h1>Форма для тестирования</h1>

    <form action="backend_lab.php" method="POST">
        <p>
            <label for="name_id">Имя:</label><br>
            <input type="text" name="user_name" id="name_id">
        </p>
        <p>
            <label for="email_id">Email:</label><br>
            <input type="text" name="user_email" id="email_id">
        </p>
         <p>
            <label for="phone_id">Phone:</label><br>
            <input type="tel" name="user_phone" id="phone_id">
        </p>
        <p>
            <button type="submit">Отправить</button>
        </p>
    </form>

    <hr>

    <?php 
     // Проверяем, есть ли сообщение в сессии
    if (isset($_SESSION['success_message'])) {
         // Выводим сообщение
        echo '<div style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px;">';
        echo htmlspecialchars($_SESSION['success_message']);
        echo '</div>';

        // Удаляем сообщение из сессии, чтобы оно не появилось снова
        unset($_SESSION['success_message']);

    }
    ?>

    <h2>Список клиентов</h2>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Статус</th>
                <th>Добавлен</th>
                <th>Действия</th> 
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?php echo $client['id']; ?></td>
                    <td><?php echo htmlspecialchars($client['name']); ?></td>
                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                    <td><?php echo htmlspecialchars($client['phone']); ?></td>
                    <td><?php echo htmlspecialchars($client['status_name']); ?></td>
                    <td><?php echo $client['created_at']; ?></td>
                    <td> 
                        <a href="backend_lab.php?action=delete&id=<?php echo $client['id']; ?>" onclick="...">Удалить</a>
                            |  
                        <a href="edit.php?id=<?php echo $client['id']; ?>">Изменить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>