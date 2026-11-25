<?php

//es el encargado de recibir la petición de login desde la pagina web y generar el token
//es como el puente entre el formulario de login.html y la base de datos

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../backend/config/database/conexion_mysql_teatro.php';
require_once __DIR__ . '/../backend/scripts/jwt_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Email y contraseña son obligatorios.']);
    exit;
}

try {
    $pdo = Database::getInstance();
    // Buscamos al miembro por email 
    $stmt = $pdo->prepare("SELECT id_miembro, nombre, primer_apellido, email FROM miembro WHERE email = ? LIMIT 1");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generamos el token
        $payload = [
            'id' => $user['id_miembro'],
            'nombre' => $user['nombre'] . ' ' . $user['primer_apellido'],
            'email' => $user['email'],
            'rol' => 'admin' //se puede hacer o se debe de hacer dinamico
        ];
        
        $token = JWT_Helper::generate($payload);
        
        echo json_encode([
            'ok' => true,
            'msg' => 'Login exitoso',
            'token' => $token,
            'user' => $payload
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['ok' => false, 'msg' => 'Credenciales inválidas.']);
    }//else

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor.']);
}//catch