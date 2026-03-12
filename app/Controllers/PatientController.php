<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Core\Paginator; // Certifique-se que o Paginator está neste namespace

class PatientController extends BaseController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']['id'])) {
            header('Location: ' . URL_BASE . '/login');
            exit;
        }

        $model = new PatientModel();

        // Parâmetros de Paginação e Busca
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 10;
        $search = $_GET['search'] ?? '';

        // 1. Conta total
        $totalItems = $model->countAll($search);

        // 2. Inicializa Paginator
        $paginator = new Paginator($totalItems, $limit, $page, URL_BASE . '/pacientes', $search);

        // 3. Busca dados paginados
        $pacientes = $model->getAllPaginated($limit, $paginator->getOffset(), $search);

        $this->view('pages/pacientes_lista', [
            'pacientes' => $pacientes,
            'paginator' => $paginator,
            'search' => $search,
            'limit' => $limit
        ]);
    }

    public function create()
    {
        $this->view('pages/cadastro_paciente');
    }

    public function edit($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            header('Location: ' . URL_BASE . '/login');
            exit;
        }

        $pacienteId = $id ?? filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$pacienteId) {
            header('Location: ' . URL_BASE . '/pacientes');
            exit;
        }

        $model = new PatientModel();
        $paciente = $model->find($pacienteId);
        if (!$paciente) {
            header('Location: ' . URL_BASE . '/pacientes');
            exit;
        }

        $this->view('pages/cadastro_paciente', ['paciente' => $paciente]);
    }

    /**
     * Responsável por CRIAR um novo paciente
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Coleta de dados (Direta, sem função auxiliar)
        $dados = [
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'cpf' => filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'telefone' => filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_nascimento' => $_POST['data_nascimento'] ?? null,
            'genero' => $_POST['genero'] ?? null,
            'origem' => $_POST['origem'] ?? null,
            'data_cadastro' => $_POST['data_cadastro'] ?? null,
            'tags' => $_POST['tags'] ?? null,
            'nome_responsavel' => $_POST['nome_responsavel'] ?? null,
            'responsavel_financeiro' => $_POST['responsavel_financeiro'] ?? null,
            'cep' => $_POST['cep'] ?? null,
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        if (!$dados['nome']) {
            $_SESSION['error'] = "Nome é obrigatório!";
            header('Location: ' . URL_BASE . '/pacientes/cadastrar');
            exit;
        }

        $model = new PatientModel();

        try {
            // Se por acaso vier ID, redireciona para update
            $id = filter_input(INPUT_POST, 'id_paciente', FILTER_SANITIZE_NUMBER_INT);
            if ($id) {
                $this->update();
                return;
            }

            $model->create($dados);
            $_SESSION['success'] = "Paciente cadastrado com sucesso!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erro ao salvar: " . $e->getMessage();
        }

        header('Location: ' . URL_BASE . '/pacientes');
        exit;
    }

    /**
     * Responsável por ATUALIZAR um paciente existente
     */
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id = filter_input(INPUT_POST, 'id_paciente', FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            $_SESSION['error'] = "ID do paciente não informado.";
            header('Location: ' . URL_BASE . '/pacientes');
            exit;
        }

        // Coleta de dados (Direta, sem função auxiliar)
        $dados = [
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'cpf' => filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'telefone' => filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_nascimento' => $_POST['data_nascimento'] ?? null,
            'genero' => $_POST['genero'] ?? null,
            'origem' => $_POST['origem'] ?? null,
            'data_cadastro' => $_POST['data_cadastro'] ?? null,
            'tags' => $_POST['tags'] ?? null,
            'nome_responsavel' => $_POST['nome_responsavel'] ?? null,
            'responsavel_financeiro' => $_POST['responsavel_financeiro'] ?? null,
            'cep' => $_POST['cep'] ?? null,
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        if (!$dados['nome']) {
            $_SESSION['error'] = "Nome é obrigatório!";
            header('Location: ' . URL_BASE . '/pacientes/editar?id=' . $id);
            exit;
        }

        $model = new PatientModel();

        try {
            if ($model->update($id, $dados)) {
                $_SESSION['success'] = "Paciente atualizado com sucesso!";
            } else {
                $_SESSION['error'] = "Não foi possível atualizar o paciente.";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erro ao atualizar: " . $e->getMessage();
        }

        header('Location: ' . URL_BASE . '/pacientes');
        exit;
    }

    public function apiSearch()
    {
        $termo = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$termo || strlen($termo) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        try {
            $patientModel = new PatientModel();
            $resultados = $patientModel->search($termo);

            $json = [];
            foreach ($resultados as $p) {
                $cpf = $p['cpf'] ? $p['cpf'] : 'S/ CPF';

                $json[] = [
                    'id_paciente' => $p['id_paciente'],
                    'id' => $p['id_paciente'],
                    'nome' => $p['nome'],
                    'cpf' => $cpf,
                    'label' => $p['nome'] . ' - ' . $cpf,
                    'telefone' => $p['telefone'] ?? '',
                    'nome_responsavel' => $p['nome_responsavel'] ?? '',
                    'responsavel_financeiro' => $p['responsavel_financeiro'] ?? '',
                    'origem' => $p['origem'] ?? '',
                    'data_cadastro' => $p['data_cadastro'] ?? ''
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($json);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // --- MÉTODO DELETE ADICIONADO ---
    public function delete()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']['id'])) {
            header('Location: ' . URL_BASE . '/login');
            exit;
        }

        // Tenta pegar ID via POST (Modal) ou GET (Link)
        $id = filter_input(INPUT_POST, 'id_paciente', FILTER_SANITIZE_NUMBER_INT)
            ?? filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        if ($id) {
            $model = new PatientModel();
            try {
                if ($model->delete($id)) {
                    $_SESSION['success'] = "Paciente excluído com sucesso!";
                } else {
                    $_SESSION['error'] = "Erro ao excluir paciente.";
                }
            } catch (\Exception $e) {
                // Captura erro de chave estrangeira (se tiver tokens vinculados)
                if (strpos($e->getMessage(), 'Constraint violation') !== false || $e->getCode() == '23000') {
                    $_SESSION['error'] = "Não é possível excluir este paciente pois ele possui Token vinculados.";
                } else {
                    $_SESSION['error'] = "Erro de sistema: " . $e->getMessage();
                }
            }
        } else {
            $_SESSION['error'] = "ID inválido.";
        }

        header('Location: ' . URL_BASE . '/pacientes');
        exit;
    }
}
