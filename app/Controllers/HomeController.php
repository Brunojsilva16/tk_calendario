<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\HomeModel;
use App\Core\Paginator;

class HomeController extends BaseController
{
    public function index()
    {
        date_default_timezone_set('America/Sao_Paulo');
        Auth::init(); 

        if (!Auth::isLogged()) {
            $this->redirect('/login');
            return;
        }

        $HomeModel = new HomeModel();

        // --- LÓGICA DE PERMISSÃO ---
        $idProf = null;
        if (Auth::level() <= 2) {
            $userData = Auth::user();
            // Correção: Usa a chave correta definida no Auth::login
            $idProf = $userData['id_profissional'] ?? null;
        }

        // --- LÓGICA 1: ESTATÍSTICAS ---
        // Passa o $idProf para filtrar as estatísticas
        $dadosEstatisticas = $HomeModel->getAllWithDetails(2000, '', 0, $idProf); 

        $hoje = date('Y-m-d');
        $mesAtual = date('Y-m');

        $atendimentosHoje = 0;
        $faturamentoHoje = 0.00;
        $faturamentoMes = 0.00;
        
        // O Total já vem filtrado pelo count do array
        $totalTokensGeral = count($dadosEstatisticas);

        foreach ($dadosEstatisticas as $t) {
            // Verifica data_cadastro (ajuste conforme seu banco: data_registro ou data_cadastro)
            $dataDb = $t['data_cadastro'] ?? $t['data_registro'] ?? '';
            
            if (empty($dataDb)) continue;

            $dataReg = substr($dataDb, 0, 10);
            $mesReg = substr($dataDb, 0, 7);
            $valor = $t['valor'] ?? 0;

            if ($dataReg == $hoje) {
                $atendimentosHoje++;
                $faturamentoHoje += $valor;
            }

            if ($mesReg == $mesAtual) {
                $faturamentoMes += $valor;
            }
        }

        // --- LÓGICA 2: TABELA PAGINADA ---
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 10;
        $search = $_GET['search'] ?? '';

        // CORREÇÃO CRÍTICA: Passando $idProf para o countAll também!
        $totalItems = $HomeModel->countAll($search, $idProf);

        $paginator = new Paginator($totalItems, $limit, $page, URL_BASE . '/home', $search);

        // Busca os dados filtrados
        $tabelaAtendimentos = $HomeModel->getAllWithDetails($limit, $search, $paginator->getOffset(), $idProf);

        $data = [
            'view' => 'home',
            'title' => 'Painel de Controle',
            
            'atendimentos_hoje' => $atendimentosHoje,
            'faturamento_hoje' => $faturamentoHoje,
            'faturamento_mes' => $faturamentoMes,
            'total_tokens' => $totalItems,
            
            'atendimentos' => $tabelaAtendimentos,
            'paginator' => $paginator,
            'search' => $search
        ];

        $this->view('pages/home', $data);
    }
}