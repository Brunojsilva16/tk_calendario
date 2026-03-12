<?php
// app/Models/TokenModel.php

namespace App\Models;

use App\Database\Connection;
use PDO;
use Exception;

class TokenModel
{
    protected $conn;
    private $table = 'tokens';
    private $colunaData = 'data_cadastro';
    private $tableSessoes = 'sessoes';

    public function __construct()
    {
        $this->conn = Connection::getInstance();
    }

    public function find($id)
    {
        return $this->getById($id);
    } // Alias
    // Método auxiliar útil para o Edit
    public function getById($id)
    {
        $sql = "SELECT t.*,
                    p.nome as nome_profissional,
                    u.nome as nome_usuario,
                    pac.data_cadastro as data_cadastro_pac
        FROM {$this->table} t 
        LEFT JOIN profissionais p ON t.id_prof = p.id_prof 
        LEFT JOIN usuarios_a u ON t.id_user = u.id_user 
        LEFT JOIN pacientes pac ON t.id_paciente = pac.id_paciente
        WHERE t.id_token = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($userId, $limit = 25, $search = '', $offset = 0)
    {
        $sql = "SELECT t.id_token as id, t.token, t.paciente as nome_paciente, t.vencimento, t.valor, t.origem, t.nome_resp, t.{$this->colunaData} as data_registro, p.nome as nome_profissional 
                FROM {$this->table} t 
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof 
                WHERE t.id_user = :id_user";

        $params = [':id_user' => $userId];

        if (!empty($search)) {
            $sql .= " AND (t.paciente LIKE :search OR t.token LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Adicionado OFFSET
        $sql .= " ORDER BY t.{$this->colunaData} DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_user', $userId);

        if (!empty($search)) {
            $stmt->bindValue(':search', $params[':search']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT); // Bind do Offset

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Conta o total de registros por usuário (Contexto Meus Tokens)
     */
    public function countByUserId($userId, $search = '')
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} t WHERE t.id_user = :id_user";
        $params = [':id_user' => $userId];

        if (!empty($search)) {
            $sql .= " AND (t.paciente LIKE :search OR t.token LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }


    public function create($data)
    {
        // Gera um token aleatório de 6 dígitos
        $tokenCode = $this->generateUniqueCode();

        $sql = "INSERT INTO {$this->table} (id_prof, id_paciente, id_user, paciente, cpf, telefone, nome_resp, responsavel_f, nome_banco, valor, porcentagem, formapag, modalidade, vencimento, origem, primeiro_at, motivo_ag, outro, obs, reembolso, token) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_prof'],
            $data['id_paciente'] ?? null,
            $data['id_user'],
            $data['paciente'],
            $data['cpf'],
            $data['telefone'],
            $data['nome_resp'],
            $data['responsavel_f'],
            $data['nome_banco'],
            $data['valor'],
            $data['porcentagem'],
            $data['formapag'],
            $data['modalidade'],
            $data['vencimento'],
            $data['origem'],
            $data['primeiro_at'],
            $data['motivo_ag'],
            $data['outro'],
            $data['obs'],
            $data['reembolso'],
            $tokenCode
        ]);
        return $this->conn->lastInsertId();
    }

    /**
     * Busca todas as sessões vinculadas a um token
     */
    public function getSessoes($idToken)
    {
        try {
            // JOIN com tokens para pegar o id_user, depois JOIN com usuarios_a para pegar o nome
            $sql = "SELECT s.*, u.nome as nome_usuario 
                    FROM {$this->tableSessoes} s
                    INNER JOIN {$this->table} t ON s.id_token = t.id_token
                    LEFT JOIN usuarios_a u ON t.id_user = u.id_user
                    WHERE s.id_token = :id 
                    ORDER BY s.data_sessao ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $idToken);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function generateUniqueCode()
    {
        $limit = 0;
        do {
            $part1 = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $part2 = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $code = $part1 . $part2;
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE token = :token");
            $stmt->execute([':token' => $code]);
            $exists = $stmt->fetchColumn() > 0;
            $limit++;
        } while ($exists && $limit < 10);
        return $code;
    }

    public function update($id, $data)
    {
        // ... (Mesma lógica do anterior, mantendo id_paciente e porcentagem) ...
        try {
            $sql = "UPDATE {$this->table} SET 
                id_prof = ?, id_paciente = ?, paciente = ?, cpf = ?, telefone = ?,
                nome_resp = ?, responsavel_f = ?, nome_banco = ?, valor = ?, porcentagem = ?,
                formapag = ?, modalidade = ?, vencimento = ?, origem = ?, primeiro_at = ?, motivo_ag = ?, outro = ?, obs = ?
                WHERE id_token = ?";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['id_prof'],
                $data['id_paciente'] ?? null,
                $data['paciente'],
                $data['cpf'],
                $data['telefone'],
                $data['nome_resp'],
                $data['responsavel_f'],
                $data['nome_banco'],
                $data['valor'],
                $data['porcentagem'],
                $data['formapag'],
                $data['modalidade'],
                $data['vencimento'],
                $data['origem'],
                $data['primeiro_at'],
                $data['motivo_ag'],
                $data['outro'],
                $data['obs'],
                $id
            ]);
        } catch (Exception $e) {
            throw $e; // Lança o erro para saber o que aconteceu
        }
    }

    /**
     * Salva (Sincroniza) as sessões
     */
    public function saveSessoes($idToken, $sessoes)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Remove TODAS as sessões antigas deste token
            $delSql = "DELETE FROM {$this->tableSessoes} WHERE id_token = :id";
            $delStmt = $this->conn->prepare($delSql);
            $delStmt->bindValue(':id', $idToken);
            $delStmt->execute();

            // 2. Insere as novas sessões
            if (!empty($sessoes) && is_array($sessoes)) {
                // AQUI: Removi 'hora_sessao' e o bind :hora
                $insSql = "INSERT INTO {$this->tableSessoes} (id_token, data_sessao) VALUES (:id, :data)";
                $insStmt = $this->conn->prepare($insSql);

                foreach ($sessoes as $sessao) {
                    $dataSessao = null;

                    // Verifica o formato do dado recebido
                    if (is_array($sessao)) {
                        // Se vier array (do editar_token), pega a chave 'data'
                        $dataSessao = $sessao['data'] ?? null;
                    } else {
                        // Se vier string direta (do gerar_token ou debug), usa o valor direto
                        $dataSessao = $sessao;
                    }

                    // Só insere se a data for válida
                    if (!empty($dataSessao)) {
                        $insStmt->bindValue(':id', $idToken);
                        $insStmt->bindValue(':data', $dataSessao);
                        $insStmt->execute();
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function settle($idToken)
    {
        $sql = "UPDATE {$this->table} SET status = 'Baixado', data_baixa = NOW() WHERE id_token = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idToken]);
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_token = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
