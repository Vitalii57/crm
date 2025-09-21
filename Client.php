<?php

class Client 
{
    // --- Свойства (Properties) ---
    // Public свойства хранят данные КОНКРЕТНОГО клиента.
    // Они будут заполняться либо из формы, либо из базы данных.
    public $id;
    public $name;
    public $email;
    public $phone;
    public $status_id;
    public $created_at;
    public $user_id;

    // Private свойство для внутреннего использования. Хранит объект подключения к БД.
    private $pdo; 

    // --- Конструктор ---
    // Вызывается при создании нового объекта: $client = new Client($pdo);
    public function __construct($db_connection) 
    {
        // При создании объекта, мы сразу "вручаем" ему подключение к БД,
        // чтобы все остальные методы могли им пользоваться.
        $this->pdo = $db_connection;
    }

    // --- Методы (Methods) ---

    /**
     * "Умный" метод для сохранения. Сам решает, делать UPDATE или INSERT.
     * @return bool - true в случае успеха, false в случае ошибки.
     */
    public function save() 
    {
        // Проверяем, есть ли у этого объекта ID. Если есть - значит, он уже существует в БД.
        if (isset($this->id) && $this->id > 0) {
            // --- ЛОГИКА ОБНОВЛЕНИЯ (UPDATE) ---
            $sql = "UPDATE clients SET name = ?, email = ?, phone = ?, status_id = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            // Выполняем, передавая массив со значениями из СВОЙСТВ этого объекта.
            // Порядок в массиве важен и соответствует порядку '?' в SQL.
            $result = $stmt->execute([
                $this->name, 
                $this->email, 
                $this->phone, 
                $this->status_id, 
                $this->id
            ]);
            return $result;
        } 
        // Если ID нет, значит, это новый клиент.
        else {
            // --- ЛОГИКА СОЗДАНИЯ (INSERT) ---
            $sql = "INSERT INTO clients (name, email, phone, status_id, user_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
             // Выполняем, передавая массив со значениями из СВОЙСТВ этого объекта.
            $result = $stmt->execute([
                $this->name, 
                $this->email, 
                $this->phone, 
                $this->status_id,
                $this->user_id
            ]);
            // (Опционально) После успешной вставки можно было бы получить ID нового клиента
            // и присвоить его $this->id. $this->id = $this->pdo->lastInsertId();
            return $result;
        }
    }

    /**
     * Находит клиента по ID и заполняет данными ТЕКУЩИЙ объект.
     * @param int $id - ID клиента для поиска.
     * @return bool - true, если клиент найден, false в противном случае.
     */
    public function find($id, $user_id, $user_role)
    {

    //         // --- НАШ ОТЛАДОЧНЫЙ БЛОК ---
    // echo "--- Отладка внутри Client::find() ---<br>";
    // echo "Ищем клиента с ID: ";
    // var_dump($id);
    // echo "Для пользователя с ID: ";
    // var_dump($user_id);
    // echo "------------------------------------<br>";
    // // --- КОНЕЦ ОТЛАДКИ ---

        // // 1. Готовим SQL-запрос
        // $sql = "SELECT * FROM clients WHERE id = ? AND user_id = ?";
        // // 2. Готовим и выполняем
        // $stmt = $this->pdo->prepare($sql);
        // $stmt->execute([$id, $user_id]);

        $sql = "SELECT * FROM clients WHERE id = ?";
    $params = [$id]; // Массив с параметрами для execute

    // Если пользователь НЕ админ, добавляем еще одно условие
    if ($user_role !== 'admin') {
        $sql .= " AND user_id = ?";
        $params[] = $user_id; // Добавляем второй параметр
    }
    // Если админ - этого условия не будет, он сможет найти любого клиента по id
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
        
        // 3. Получаем данные в виде ассоциативного массива
        $client_data = $stmt->fetch();

        // 4. Если данные получены (клиент найден)...
        if ($client_data) {
            // ...заполняем свойства ТЕКУЩЕГО объекта ($this)
            $this->id = $client_data['id'];
            $this->name = $client_data['name'];
            $this->email = $client_data['email'];
            $this->phone = $client_data['phone'];
            $this->status_id = $client_data['status_id'];
            $this->created_at = $client_data['created_at'];
            return true; // Возвращаем "успех"
        }

        return false; // Возвращаем "неудача", если клиент не найден
    }

    /**
     * Статический метод для получения ВСЕХ клиентов.
     * Статический, потому что он не относится к какому-то одному клиенту.
     * @param PDO $pdo - Принимает подключение, так как у него нет доступа к $this->pdo.
     * @return array - Возвращает массив с данными всех клиентов.
     */
    public static function findAll($pdo, $user_id, $user_role)
    {
//          $sql = "SELECT
//                 clients.*, 
//                 pipeline_statuses.name AS status_name 
//             FROM 
//                 clients 
//             LEFT JOIN 
//                 pipeline_statuses ON clients.status_id = pipeline_statuses.id
//             WHERE 
//                 clients.user_id = ?  -- СНАЧАЛА УСЛОВИЕ
//             ORDER BY 
//                 clients.id DESC"; 
        
//         $stmt = $pdo->prepare($sql);
// $stmt->execute([$user_id]);
// return $stmt->fetchAll();

          // Начинаем строить SQL-запрос
    $sql = "SELECT
                clients.*, 
                pipeline_statuses.name AS status_name 
            FROM 
                clients 
            LEFT JOIN 
                pipeline_statuses ON clients.status_id = pipeline_statuses.id";

    // Массив для параметров, которые пойдут в execute()
    $params = [];

    // Проверяем роль пользователя
    if ($user_role !== 'admin') {
        // Если это НЕ админ, добавляем условие WHERE
        $sql .= " WHERE clients.user_id = ?";
        $params[] = $user_id; // И добавляем user_id в массив параметров
    }
    // Если это админ, условие WHERE не добавляется, и он увидит всех

    // Добавляем сортировку в конец
    $sql .= " ORDER BY clients.id DESC";
    
    // Готовим и выполняем запрос
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Передаем наш массив с параметрами (он будет либо пустым, либо с одним user_id)
    
    return $stmt->fetchAll();
    }

    public static function deleteById($pdo, $id)
{
    // 1. Правильный SQL-запрос
    $sql = "DELETE FROM clients WHERE id = ?";
    
    // 2. Подготовка
    $stmt = $pdo->prepare($sql);
    // $stmt->execute([$user_id]);
    
    // 3. Выполнение и возврат результата
    return $stmt->execute([$id]); 
}
}
?>