<?php
// test_conexion.php

require_once __DIR__ . '/backend/config/database/conexion_mysql_teatro.php';

try {
    $pdo = Database::getInstance();
    
    echo "<h1 style='color: green; font-family: sans-serif;'> ¡Conexión exitosa a la base de datos!</h1>";
    echo "<p>Conectado como: <b>teatro_user</b> a la base de datos <b>teatro_pleasantville</b>.</p>";

} catch (PDOException $e) {
    // Si hay error, lo mostramos 
    echo "<h1 style='color: red; font-family: sans-serif;'> Error de conexión</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>