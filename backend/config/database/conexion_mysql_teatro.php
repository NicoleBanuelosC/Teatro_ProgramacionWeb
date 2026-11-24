<?php
// Patrón Singleton: una sola conexión por petición

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'teatro_pleasantville');
//no root
define('DB_USER', 'teatro_user');
define('DB_PASS', 'Teatro2026!');       //¿contraseña de mi root o de quien?
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // protege contra SQL Injection
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            die("Error de conexión a la base de datos. Intente más tarde.");
        }//catch
    }//function__construct

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }//if
        return self::$instance->pdo;
    }//getInstance
}
?>