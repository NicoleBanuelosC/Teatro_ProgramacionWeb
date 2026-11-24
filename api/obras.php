<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../backend/controllers/ObraController.php';

$c = new ObraController();
$id = $_GET['id'] ?? null;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $c->listar();
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
        echo json_encode(['ok'=>false, 'msg'=>'Método no permitido']);
}//switch