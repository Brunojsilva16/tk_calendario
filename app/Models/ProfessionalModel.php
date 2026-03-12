<?php
// app/Models/ProfessionalModel.php

namespace App\Models;

use App\Database\Connection;
use PDO;

class ProfessionalModel
{
    protected $conn;

    public function __construct()
    {
        $this->conn = Connection::getInstance();
    }

    public function getAll()
    {
        // Trazendo a porcentagem também, caso precise usar no front-end via data-attributes
        $sql = "SELECT id_prof as id, nome, porcentagem FROM profissionais ORDER BY nome ASC"; 
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Busca um profissional pelo ID para pegar a porcentagem correta
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM profissionais WHERE id_prof = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}