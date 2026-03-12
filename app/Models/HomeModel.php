<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class HomeModel
{

    private $conn;

    public function __construct()
    {
        // Conecta ao banco diretamente usando a classe Connection
        $this->conn = Connection::getInstance();
    }

    /**
     * Busca últimos tokens gerados para exibir na home
     */
    public function getRecentActivity()
    {
        // Ajustado para as colunas do seu banco:
        // - pacientes: id_paciente, nome
        // - tokens: id_paciente, data_criacao
        $sql = "SELECT t.*, p.nome as patient_name 
                FROM tokens t 
                LEFT JOIN pacientes p ON t.id_paciente = p.id_paciente 
                ORDER BY t.data_cadastro DESC LIMIT 5";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca tokens com paginação e busca, opcionalmente filtrando por profissional.
     */
    public function getAllWithDetails($limit, $search = '', $offset = 0, $idProf = null)
    {
        $sql = "SELECT t.*, 
                       p.nome as nome_profissional, 
                       u.nome as nome_usuario,
                       pac.nome as nome_paciente,
                       pac.cpf as cpf_paciente
                FROM tokens t
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof
                LEFT JOIN usuarios_a u ON t.id_user = u.id_user
                LEFT JOIN pacientes pac ON t.id_paciente = pac.id_paciente
                WHERE 1=1";

        $params = [];

        // Filtro por Profissional (Nível 1)
        if ($idProf !== null) {
            $sql .= " AND t.id_prof = :idProf";
            $params[':idProf'] = $idProf;
        }

        // Filtro de Busca (Search)
        if (!empty($search)) {
            $sql .= " AND (pac.nome LIKE :search OR t.token LIKE :search OR pac.cpf LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY t.id_token DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        // Bind limit e offset como inteiros
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta o total de registros para a paginação, opcionalmente filtrando por profissional.
     */
    public function countAll($search = '', $idProf = null)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM tokens t
                LEFT JOIN pacientes pac ON t.id_paciente = pac.id_paciente
                WHERE 1=1";

        $params = [];

        // Filtro por Profissional
        if ($idProf !== null) {
            $sql .= " AND t.id_prof = :idProf";
            $params[':idProf'] = $idProf;
        }

        if (!empty($search)) {
            $sql .= " AND (pac.nome LIKE :search OR t.token LIKE :search OR pac.cpf LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }
}
