<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class SessaoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Busca as sessões dentro de um período específico
     * @param string $start Data de início (YYYY-MM-DD)
     * @param string $end Data de fim (YYYY-MM-DD)
     * @return array
     */
    public function getSessoesNoPeriodo($start, $end)
    {
        // A consulta foi ajustada para refletir a estrutura real do banco de dados:
        // sessoes -> tokens -> pacientes
        // Nota: Se a sua coluna de data na tabela sessoes chamar "data" ou "data_sessao" em vez de "data_agendamento",
        // basta alterar o nome na query abaixo.
        $sql = "SELECT 
                s.id_sessao as id, 
                s.data_agendamento as data_sessao, 
                s.hora_inicio, 
                s.hora_fim,
                p.nome as nome_paciente,
                s.status as tipo_sessao
            FROM sessoes s
            INNER JOIN tokens t ON s.id_token = t.id_token
            INNER JOIN pacientes p ON t.id_paciente = p.id_paciente
            WHERE s.data_agendamento BETWEEN :start AND :end
            ORDER BY s.data_agendamento ASC, s.hora_inicio ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}