<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../backend/controllers/ProduccionController.php';

$c = new ProduccionController();
$id = $_GET['id'] ?? null;

//se procesa lo que se desea hacer 

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (($_GET['ref'] ?? '') === 'opciones') {
            $c->opciones();
        } else {
            $c->listar();
        }//else
        break;

    case 'POST':
        $c->crear();
        break;

    case 'PUT':
        $c->actualizar($id);
        break;

    case 'DELETE':
        $c->eliminar($id);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['ok'=>false, 'msg'=>'Este método no permitido']);
}//switch