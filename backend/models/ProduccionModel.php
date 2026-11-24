<?php

class ProduccionModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar($estado = null, $buscar = '') {
        $sql = "SELECT p.id_produccion, o.titulo AS obra, m.nombre, m.primer_apellido,
                       p.temporada, p.año, p.estado
                FROM produccion p
                JOIN obra o ON p.id_obra = o.id_obra
                JOIN miembro m ON p.id_productor = m.id_miembro
                WHERE 1=1";
        $params = [];
        if ($estado && $estado !== 'todas') { $sql .= " AND p.estado = ?"; $params[] = $estado; }
        if ($buscar !== '') { $sql .= " AND (o.titulo LIKE ? OR m.nombre LIKE ?)"; $params[] = "%$buscar%"; $params[] = "%$buscar%"; }
        $sql .= " ORDER BY p.año DESC, p.temporada";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }//listar

    public function crear($d) {
        $st = $this->pdo->prepare("INSERT INTO produccion (id_obra, temporada, año, id_productor, estado)
                                   VALUES (?,?,?,?,?)");
        $st->execute([$d['id_obra'], $d['temporada'], $d['anio'], $d['id_productor'], $d['estado']]);
        return $this->pdo->lastInsertId();
    }//Crear

    public function actualizar($id, $d) {
        $st = $this->pdo->prepare("UPDATE produccion SET id_obra=?, temporada=?, año=?, id_productor=?, estado=?
                                   WHERE id_produccion=?");
        $st->execute([$d['id_obra'], $d['temporada'], $d['anio'], $d['id_productor'], $d['estado'], $id]);
        return $st->rowCount();
    }//actualizar

    public function eliminar($id) {
        try {
            $this->pdo->beginTransaction();
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM boleto b
                                       JOIN representacion r ON b.id_representacion = r.id_representacion
                                       WHERE r.id_produccion = ?");
            $st->execute([$id]);
            if ($st->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['ok' => false, 'msg' => 'No se puede eliminar: tiene boletos vendidos.'];
            }//if

            $st = $this->pdo->prepare("DELETE FROM produccion WHERE id_produccion = ?");
            $st->execute([$id]);
            if ($st->rowCount() === 0) { $this->pdo->rollBack(); return ['ok'=>false,'msg'=>'No existe.']; }
            $this->pdo->commit();
            return ['ok' => true, 'msg' => 'Eliminada correctamente.'];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
        }//try-catch
    }//eliminar

    public function opciones() {
        $obras = $this->pdo->query("SELECT id_obra, titulo FROM obra ORDER BY titulo")->fetchAll();
        $miembros = $this->pdo->query("SELECT id_miembro, nombre, primer_apellido FROM miembro ORDER BY nombre")->fetchAll();
        return ['obras' => $obras, 'miembros' => $miembros];
    }//opciones
    
}