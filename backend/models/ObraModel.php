<?php

class ObraModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar($buscar = '') {
        $sql = "SELECT id_obra, titulo, autor, tipo, numero_actos FROM obra WHERE 1=1";
        $params = [];
        if ($buscar !== '') { 
            $sql .= " AND (titulo LIKE ? OR autor LIKE ?)"; 
            $params[] = "%$buscar%"; 
            $params[] = "%$buscar%"; 
        }
        $sql .= " ORDER BY titulo";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }//listar

    public function crear($d) {
        $st = $this->pdo->prepare("INSERT INTO obra (titulo, autor, tipo, numero_actos) VALUES (?,?,?,?)");
        $st->execute([$d['titulo'], $d['autor'], $d['tipo'], $d['numero_actos']]);
        return $this->pdo->lastInsertId();
    }//crear

    public function actualizar($id, $d) {
        $st = $this->pdo->prepare("UPDATE obra SET titulo=?, autor=?, tipo=?, numero_actos=? WHERE id_obra=?");
        $st->execute([$d['titulo'], $d['autor'], $d['tipo'], $d['numero_actos'], $id]);
        return $st->rowCount();
    }//actualizar

    public function eliminar($id) {
        try {
            // Verificar si la obra tiene producciones asociadas 
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM produccion WHERE id_obra = ?");
            $st->execute([$id]);
            if ($st->fetchColumn() > 0) {
                return ['ok' => false, 'msg' => 'No se puede eliminar: la obra tiene producciones asociadas.'];
            }//if

            $st = $this->pdo->prepare("DELETE FROM obra WHERE id_obra = ?");
            $st->execute([$id]);
            return ['ok' => true, 'msg' => 'Obra eliminada correctamente.'];
        } catch (Exception $e) {
            return ['ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
        }//catch

    }//eliminar
}