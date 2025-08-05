<?php 

    class Database {
        private $host = 'localhost';
        private $db_name = 'wp_test_site';
        private $username = 'root';
        private $password = 'root';
        private $charset = 'utf8mb4';

        private $conn;


        public function connect () {
            $this->conn = null;

            $dsn =  "mysql:host=$this->host;dbname=$this->db_name;charset=$this->charset";


            try {
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                    PDO::ATTR_EMULATE_PREPARES   => false,    ]     ;   
                    
                    
                $this->conn = new PDO($dsn, $this->username, $this->password, $options); 
            } catch(PDOException $e) {
            echo 'Ошибка подключения: ' . $e->getMessage();
            }

             return $this->conn;

        }
    }



?>