<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class PatientModel
{
    // ... métodos search (autocomplete) e find mantidos ...

    public function search($term)
    {
        $pdo = Connection::getInstance();
        $cleanTerm = preg_replace('/[^a-zA-Z0-9 ]/', '', $term);

        $sql = "SELECT id_paciente, nome, cpf, email, telefone, nome_responsavel, responsavel_financeiro, origem, data_cadastro 
                FROM pacientes 
                WHERE nome LIKE :nome OR cpf LIKE :cpf 
                LIMIT 20";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', '%' . $term . '%');
        $stmt->bindValue(':cpf', '%' . $term . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id_paciente = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- NOVOS MÉTODOS PARA PAGINAÇÃO ---

    /**
     * Conta o total de pacientes (com filtro opcional)
     */
    public function countAll($search = '')
    {
        $pdo = Connection::getInstance();
        $sql = "SELECT COUNT(*) FROM pacientes";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE nome LIKE :s OR cpf LIKE :s OR email LIKE :s";
            $params[':s'] = "%$search%";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca pacientes paginados
     */
    public function getAllPaginated($limit, $offset, $search = '')
    {
        $pdo = Connection::getInstance();
        $sql = "SELECT * FROM pacientes";

        // Filtro de Busca
        if (!empty($search)) {
            $sql .= " WHERE nome LIKE :s OR cpf LIKE :s OR email LIKE :s";
        }

        // Ordenação e Limites
        $sql .= " ORDER BY nome ASC LIMIT :lim OFFSET :off";

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $stmt->bindValue(':s', "%$search%");
        }

        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mantido para compatibilidade se usado em outros lugares sem paginação
    public function getAll()
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->query("SELECT * FROM pacientes ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $pdo = Connection::getInstance();

        $sql = "INSERT INTO pacientes (
                    nome, cpf, email, telefone, data_nascimento, genero, 
                    origem, data_cadastro, tags, nome_responsavel, responsavel_financeiro,
                    cep, logradouro, numero, complemento, bairro, cidade, estado, observacoes
                ) VALUES (
                    :nome, :cpf, :email, :telefone, :data_nascimento, :genero, 
                    :origem, :data_cadastro, :tags, :nome_responsavel, :responsavel_financeiro,
                    :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado, :observacoes
                )";

        $stmt = $pdo->prepare($sql);
        $this->bindCommonParams($stmt, $data);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $pdo = Connection::getInstance();

        $sql = "UPDATE pacientes SET 
                    nome = :nome, 
                    cpf = :cpf, 
                    email = :email, 
                    telefone = :telefone,
                    data_nascimento = :data_nascimento, 
                    genero = :genero,
                    origem = :origem,
                    data_cadastro = :data_cadastro,
                    tags = :tags,
                    nome_responsavel = :nome_responsavel, 
                    responsavel_financeiro = :responsavel_financeiro,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado,
                    observacoes = :observacoes
                WHERE id_paciente = :id";

        $stmt = $pdo->prepare($sql);
        $this->bindCommonParams($stmt, $data);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    private function bindCommonParams($stmt, $data)
    {
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':cpf', $data['cpf'] ?? null);
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':telefone', $data['telefone'] ?? null);

        $dtNasc = (isset($data['data_nascimento']) && $data['data_nascimento'] !== '') ? $data['data_nascimento'] : null;
        $stmt->bindValue(':data_nascimento', $dtNasc);

        $stmt->bindValue(':genero', $data['genero'] ?? null);
        $stmt->bindValue(':origem', $data['origem'] ?? null);
        $dtCad = (!empty($data['data_cadastro'])) ? $data['data_cadastro'] : date('Y-m-d H:i:s');
        $stmt->bindValue(':data_cadastro', $dtCad);
        $stmt->bindValue(':tags', $data['tags'] ?? null);
        $stmt->bindValue(':nome_responsavel', $data['nome_responsavel'] ?? null);
        $stmt->bindValue(':responsavel_financeiro', $data['responsavel_financeiro'] ?? null);

        $stmt->bindValue(':cep', $data['cep'] ?? null);
        $stmt->bindValue(':logradouro', $data['logradouro'] ?? null);
        $stmt->bindValue(':numero', $data['numero'] ?? null);
        $stmt->bindValue(':complemento', $data['complemento'] ?? null);
        $stmt->bindValue(':bairro', $data['bairro'] ?? null);
        $stmt->bindValue(':cidade', $data['cidade'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? null);
    }

    public function delete($id)
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("DELETE FROM pacientes WHERE id_paciente = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
