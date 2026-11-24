<?php

require_once __DIR__ . '/../config/database/conexion_mysql_teatro.php';
require_once __DIR__ . '/../models/ObraModel.php';

class ObraController {
    private $model;

    public function __construct() {
        $this->model = new ObraModel(Database::getInstance());
    }

    //convierte el php en json y le devuelve al cliente un codigo http de respuesta (200 para exito, 422 error de validacion)
    private function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }//json

    // validación PHP 
    private function validar($d) {
        $errores = [];
        $tipos = ['drama','comedia','musical','infantil','romance','tragedia','comedia_romantica','opera','otro'];

        if (empty(trim($d['titulo']))) $errores[] = 'El título es obligatorio.';
        if (empty(trim($d['autor']))) $errores[] = 'El autor es obligatorio.';
        if (!in_array($d['tipo'], $tipos)) $errores[] = 'Tipo de obra inválido.';
        if (!is_numeric($d['numero_actos']) || $d['numero_actos'] < 1 || $d['numero_actos'] > 10) $errores[] = 'Número de actos inválido (1-10).';

        return $errores;
    }//validar

    //consulta
    public function listar() {
        $buscar = trim($_GET['q'] ?? '');
        $this->json(['ok' => true, 'data' => $this->model->listar($buscar)]);
    }//listar

    //alta
    public function crear() {
        $d = json_decode(file_get_contents('php://input'), true);
        $errs = $this->validar($d);
        if ($errs) $this->json(['ok'=>false,'errores'=>$errs], 422);
        $id = $this->model->crear($d);
        $this->json(['ok'=>true,'id'=>$id,'msg'=>'Obra registrada.']);
    }//crear

    //cambio
    public function actualizar($id) {
        $d = json_decode(file_get_contents('php://input'), true);
        $errs = $this->validar($d);
        if ($errs) $this->json(['ok'=>false,'errores'=>$errs], 422);
        $this->model->actualizar($id, $d);
        $this->json(['ok'=>true,'msg'=>'Obra actualizada.']);
    }//actualizar

    //baja
    public function eliminar($id) {
        $r = $this->model->eliminar($id);
        $this->json($r, $r['ok'] ? 200 : 409);
    }//eliminar
}