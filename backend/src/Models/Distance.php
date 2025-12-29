<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Distance
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($cepOrigem, $cepDestino, $distancia)
    {
        $sql = "INSERT INTO distances (cep_origem, cep_destino, distancia, created_at, updated_at) 
                VALUES (:cep_origem, :cep_destino, :distancia, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cep_origem' => $cepOrigem,
            ':cep_destino' => $cepDestino,
            ':distancia' => $distancia
        ]);

        return $this->db->lastInsertId();
    }

    public function findAll($limit = 100, $offset = 0)
    {
        $sql = "SELECT * FROM distances ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function count()
    {
        $sql = "SELECT COUNT(*) as total FROM distances";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'];
    }

    public function findByCeps($cepOrigem, $cepDestino)
    {
        $sql = "SELECT * FROM distances 
                WHERE cep_origem = :cep_origem AND cep_destino = :cep_destino 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cep_origem' => $cepOrigem,
            ':cep_destino' => $cepDestino
        ]);
        
        return $stmt->fetch();
    }

    public function update($id, $distancia)
    {
        $sql = "UPDATE distances SET distancia = :distancia, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':distancia' => $distancia
        ]);
        
        return $stmt->rowCount() > 0;
    }
}

