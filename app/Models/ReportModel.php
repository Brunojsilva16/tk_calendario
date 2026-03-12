<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class ReportModel
{

    private $conn;
    private $colunaData = 'data_cadastro';

    public function __construct()
    {
        // Conecta ao banco diretamente
        $this->conn = Connection::getInstance();
    }

    public function getReportData($startDate, $endDate, $profId = null, $filters = [])
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $sql = "SELECT t.id_token, 
                       t.token, 
                       t.paciente, 
                       t.responsavel_f,
                       t.modalidade,
                       t.origem,
                       t.obs,
                       t.vencimento,
                       t.nome_banco,
                       t.valor, 
                       t.formapag, 
                       t.{$this->colunaData} as data_registro,
                       p.nome as nome_profissional, 
                       t.porcentagem
                FROM tokens t
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof
                WHERE t.{$this->colunaData} BETWEEN :start AND :end";

        $params = [
            ':start' => $start,
            ':end' => $end
        ];

        if (!empty($profId)) {
            $sql .= " AND t.id_prof = :profId";
            $params[':profId'] = $profId;
        }

        if (!empty($filters['responsavel_f'])) {
            $sql .= " AND t.responsavel_f LIKE :resp";
            $params[':resp'] = "%" . $filters['responsavel_f'] . "%";
        }

        if (!empty($filters['formapag'])) {
            $sql .= " AND t.formapag = :formapag";
            $params[':formapag'] = $filters['formapag'];
        }

        if (!empty($filters['nome_banco'])) {
            $sql .= " AND t.nome_banco = :banco";
            $params[':banco'] = $filters['nome_banco'];
        }

        if (!empty($filters['origem'])) {
            $sql .= " AND t.origem = :origem";
            $params[':origem'] = $filters['origem'];
        }

        $sql .= " ORDER BY t.{$this->colunaData} ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }


 public function getRelatorioQuantitativo($dtInicio, $dtFim, $idProf = '', $origem = '', $primeiroAt = '', $motivo = '')
    {
        // Ajusta datas para cobrir o dia inteiro
        $start = $dtInicio . ' 00:00:00';
        $end   = $dtFim . ' 23:59:59';

        // QUERY REVISADA
        $sql = "SELECT t.id_token, 
                       t.paciente, 
                       t.origem, 
                       t.data_cadastro,
                       t.primeiro_at,          /* Garante que esta coluna venha do Token */
                       t.motivo_ag as motivo,  /* Alias padronizado */
                       t.outro,                /* Traz o campo outro caso precise */
                       pac.cpf as cpf_paciente,
                       pac.data_cadastro as datacad,
                       p.nome as prof_nome
                FROM tokens t
                LEFT JOIN pacientes pac ON t.id_paciente = pac.id_paciente
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof
                WHERE t.data_cadastro BETWEEN :start AND :end";

        $params = [
            ':start' => $start,
            ':end'   => $end
        ];

        // 1. Filtro Profissional
        if (!empty($idProf)) {
            $sql .= " AND t.id_prof = :idProf";
            $params[':idProf'] = $idProf;
        }

        // 2. Filtro Origem
        if (!empty($origem)) {
            $sql .= " AND t.origem = :origem";
            $params[':origem'] = $origem;
        }

        // 3. Filtro Primeiro Atendimento
        // Verifica estritamente se não é vazio, pois '0' é um valor válido
        if ($primeiroAt !== '' && $primeiroAt !== null) {
            $sql .= " AND t.primeiro_at = :primeiroAt";
            $params[':primeiroAt'] = $primeiroAt;
        }

        // 4. Filtro Motivo
        if (!empty($motivo)) {
            $sql .= " AND t.motivo_ag = :motivo";
            $params[':motivo'] = $motivo;
        }

        // ALTERADO: Prioriza primeiro_at DESC para garantir que se o paciente tiver 
        // um registro 'Sim' (1) e outro 'Não' (0), o 'Sim' seja capturado na lógica de paciente único.
        $sql .= " ORDER BY t.primeiro_at DESC, t.data_cadastro ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Em caso de erro na query, retorna vazio para não quebrar a tela
            return [];
        }

        // --- LÓGICA DE PACIENTE ÚNICO ---
        $relatorioUnico = [];
        $cpfsProcessados = [];
        $nomesProcessados = [];

        foreach ($result as $token) {
            $cpf = $token['cpf_paciente'];
            $nome = mb_strtoupper(trim($token['paciente']));
            
            $jaProcessado = false;

            if (!empty($cpf)) {
                if (in_array($cpf, $cpfsProcessados)) $jaProcessado = true;
                else $cpfsProcessados[] = $cpf;
            } else {
                if (in_array($nome, $nomesProcessados)) $jaProcessado = true;
                else $nomesProcessados[] = $nome;
            }

            if (!$jaProcessado) {
                $relatorioUnico[] = [
                    'paciente'    => $token['paciente'],
                    'origem'      => $token['origem'] ?: 'Não Inf.',
                    'data'        => $token['data_cadastro'],
                    'datacad'     => $token['datacad'],
                    'primeiro_at' => $token['primeiro_at'], 
                    'motivo'      => $token['motivo'],
                    'outro'       => $token['outro'],
                    'prof_nome'   => $token['prof_nome']
                ];
            }
        }

        usort($relatorioUnico, function ($a, $b) {
            return strcasecmp($a['paciente'], $b['paciente']);
        });

        return $relatorioUnico;
    }
}
