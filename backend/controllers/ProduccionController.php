<?php

require_once __DIR__ . '/../config/database/conexion_mysql_teatro.php';
require_once __DIR__ . '/../models/ProduccionModel.php';

class ProduccionController {
    private $model;

    public function __construct() {
        $this->model = new ProduccionModel(Database::getInstance());
    }

    //convierte los datos de php a JSON para que el js del panel los pueda leer y enviar un codigo http    
    private function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }//json

    //aqui esta la validación PHP
    private function validar($d) {
        $errores = [];
        $temporadas = ['otoño','invierno','primavera','verano'];
        $estados = ['planeacion','activa','finalizada'];

        if (empty($d['id_obra'])) $errores[] = 'Debes seleccionar una obra.';
        if (!in_array($d['temporada'], $temporadas)) $errores[] = 'Temporada inválida.';
        if (!preg_match('/^\d{4}$/', (string)$d['anio']) || $d['anio'] < 2000 || $d['anio'] > 2100) $errores[] = 'Año inválido.';
        if (empty($d['id_productor'])) $errores[] = 'Debes seleccionar un productor.';
        if (!in_array($d['estado'], $estados)) $errores[] = 'Estado inválido.';

        return $errores;
    }//validar

    //consulta, pude al modelo todas las producciones, con filtros y busqueda
    public function listar() {
        $estado = $_GET['estado'] ?? 'todas';
        $buscar = trim($_GET['q'] ?? '');
        $this->json(['ok' => true, 'data' => $this->model->listar($estado, $buscar)]);
    }//listar

    //altas, recibe los datos y los pasa por el validar() y si estan bien, los pone en la bd
    public function crear() {
        $d = json_decode(file_get_contents('php://input'), true);
        $errs = $this->validar($d);
        if ($errs) $this->json(['ok'=>false,'errores'=>$errs], 422);
        $id = $this->model->crear($d);
        $this->json(['ok'=>true,'id'=>$id,'msg'=>'Producción registrada.']);
    }//crear

    //cambio, recibe los datos editados, los valuda y se actualiza el registro
    public function actualizar($id) {
        $d = json_decode(file_get_contents('php://input'), true);
        $errs = $this->validar($d);
        if ($errs) $this->json(['ok'=>false,'errores'=>$errs], 422);
        $this->model->actualizar($id, $d);
        $this->json(['ok'=>true,'msg'=>'Producción actualizada.']);
    }//actualizar

    //baja, le dice al modelo que elimine x produccion por su id
    //aqui se ejecuta una transaccion, si tiene boletos vendidos, se detiene y devuelve un menasje
    public function eliminar($id) {
        $r = $this->model->eliminar($id);
        $this->json($r, $r['ok'] ? 200 : 409);
    }//eliminar

    //llenar el menu desplegable
    public function opciones() {
        $this->json(['ok'=>true,'data'=>$this->model->opciones()]);
    }//opciones

}