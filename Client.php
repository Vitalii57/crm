<?php

class Client 
{
    // --- Свойства ---
    public $id;
    public $name;
    public $email;
    public $phone;
    public $status_id;
    public $created_at;

    private $pdo; 

    // --- Конструктор ---
    public function __construct($db_connection) 
    {
        // Сохраняем подключение к БД, чтобы другие методы могли его использовать
        $this->pdo = $db_connection;
    }

    // --- Методы ---

    /**
     * Метод для поиска одного клиента по ID и заполнения объекта данными.
     * @param int $id - ID клиента для поиска
     * @return bool - true, если клиент найден, false - если нет
     */
    public function find($id) 
    {
        // 1. Готовим SQL-запрос
        $sql = "SELECT * FROM clients WHERE id = ?";

        // 2. Готовим и выполняем запрос, используя свойство $this->pdo
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // 3. Получаем данные
        $client_data = $stmt->fetch();

        // 4. Проверяем результат и заполняем свойства объекта
        if ($client_data) {
            $this->id = $client_data['id'];
            $this->name = $client_data['name'];
            $this->email = $client_data['email'];
            $this->phone = $client_data['phone'];
            $this->status_id = $client_data['status_id'];
            $this->created_at = $client_data['created_at'];

            return true; // Успех
        }

        return false; // Клиент не найден
    }

    /**
     * Статический метод для получения ВСЕХ клиентов из БД.
     * @param PDO $pdo - объект подключения к БД.
     * @return array - массив с данными всех клиентов.
     */
    public static function findAll($pdo) 
    {
        // 1. Готовим сложный SQL-запрос с JOIN'ом.
        $sql = "SELECT
                    clients.*, 
                    pipeline_statuses.name AS status_name 
                FROM 
                    clients 
                LEFT JOIN 
                    pipeline_statuses ON clients.status_id = pipeline_statuses.id
                ORDER BY 
                    clients.id DESC";
        
        // 2. Выполняем запрос
        $stmt = $pdo->query($sql);
        
        // 3. Возвращаем результат в виде массива ассоциативных массивов
        return $stmt->fetchAll();
    }
}
?>