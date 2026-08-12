<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    // Configuración directa de los parámetros de tu base de datos sigco_esteban_alone
    private $host = 'localhost';
    private $db_name = 'sigco_esteban_alone';
    private $username = 'root';
    private $password = ''; // Pon aquí tu contraseña si usas una diferente en XAMPP

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            // Configurar PDO para que lance excepciones en caso de errores de SQL
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Configurar zona horaria de MySQL para que coincida con Colombia (Bogotá)
            $this->connection->exec("SET time_zone = '-05:00';");
        } catch (PDOException $e) {
            die("Error crítico de conexión: " . $e->getMessage());
        }
    }

    // Método para obtener la instancia única de la clase
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Método para obtener la conexión PDO
    public function getConnection() {
        return $this->connection;
    }
}