<?php
// app/Controllers/ReportController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\ReportModel;
use App\Models\ProfessionalModel;

class ReportController extends BaseController
{
    // Tela de filtro (Mantém layout padrão pois é parte do sistema)
    public function index()
    {
        Auth::requireLevel(2); // Ajustado para usar requireLevel se disponível, ou protect
        $profModel = new ProfessionalModel();
        $profissionais = $profModel->getAll();

        $this->view('pages/reports/filter', [
            'profissionais' => $profissionais,
            'title' => 'Relatórios Financeiros'
        ]);
    }

    // Processa o relatório (existente)
    public function generate()
    {
        Auth::requireLevel(2);

        $tipo = $_GET['tipo'] ?? 'completo';
        $format = $_GET['format'] ?? 'html';

        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $profId = $_GET['profissional_id'] ?? null;

        if (Auth::level() == 1) {
            $profId = Auth::user()['professional_id'];
        }
        $filters = [
            'responsavel_f' => $_GET['responsavel_f'] ?? null,
            'formapag' => $_GET['formapag'] ?? null,
            'nome_banco' => $_GET['nome_banco'] ?? null,
            'origem' => $_GET['origem'] ?? null
        ];

        $ReportModel = new ReportModel();

        if (method_exists($ReportModel, 'getReportData')) {
            $dados = $ReportModel->getReportData($dataInicio, $dataFim, $profId, $filters);
        } else {
            $dados = [];
        }

        // Variáveis para a View
        $viewData = [
            'dados' => $dados,
            'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim],
            'filtro_prof' => $profId,
            'isExcel' => ($format === 'excel')
        ];

        // Define qual arquivo procurar
        $fileName = ($tipo === 'completo') ? 'complete.phtml' : 'summary.phtml';

        // --- CORREÇÃO DE CAMINHO ---
        // Lista de locais possíveis onde o arquivo pode estar (prioridade para pasta reports)
        // Usa 'views' minúsculo para compatibilidade com Linux
        $possiblePaths = [
            __DIR__ . '/../Views/pages/reports/' . $fileName,  // Caminho Ideal
            __DIR__ . '/../Views/pages/' . $fileName           // Caminho Alternativo (raiz de pages)
        ];

        $viewPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $viewPath = $path;
                break;
            }
        }

        // Se não achou em lugar nenhum, mostra erro detalhado
        if (!$viewPath) {
            echo "<h3>Erro: Arquivo de visualização não encontrado.</h3>";
            echo "<p>O sistema procurou nos seguintes locais e não encontrou:</p>";
            echo "<ul>";
            foreach ($possiblePaths as $path) {
                echo "<li>" . htmlspecialchars($path) . "</li>";
            }
            echo "</ul>";
            echo "<p>Verifique se o arquivo <strong>$fileName</strong> foi enviado para o servidor e se o nome da pasta <strong>Views</strong> está em minúsculo.</p>";
            exit;
        }

        // --- MODO EXCEL ---
        if ($format === 'excel') {
            $filename = 'relatorio_' . $tipo . '_' . date('Y-m-d_Hi') . '.xls';

            if (ob_get_level()) ob_end_clean(); // Limpa buffers anteriores

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            extract($viewData);
            require $viewPath;
            exit;
        }

        // --- MODO TELA (HTML) ---
        // Require direto para evitar Sidebar/Header do BaseController
        extract($viewData);
        require $viewPath;
        exit;
    }

    // --- CORREÇÃO DO ERRO AQUI ---

    // O nome do método deve bater com o que está na sua rota. 
    // O erro indicou que a rota chama: getRelatorioQuantitativo
    // Removemos os argumentos da função e usamos $_GET dentro dela.
    public function getRelatorioQuantitativo()
    {
        Auth::requireLevel(2); // Garante admin

        $ReportModel = new ReportModel();
        $ProfessionalModel = new ProfessionalModel();

        // 1. Carrega lista de profissionais para o filtro
        $profissionais = $ProfessionalModel->getAll();

        // 2. Captura os filtros do GET (Aqui corrigimos o problema dos argumentos)
        $dtInicio   = $_GET['dt_inicio']   ?? date('Y-m-01');
        $dtFim      = $_GET['dt_fim']      ?? date('Y-m-t');
        $origem     = $_GET['origem']      ?? '';
        $primeiroAt = $_GET['primeiro_at'] ?? '';
        $motivo     = $_GET['motivo']      ?? '';

        // Lógica de restrição de profissional
        $idProf = $_GET['id_prof'] ?? '';
        if (Auth::level() == 1) {
            $idProf = Auth::user()['professional_id'];
        }

        // 3. Monta array de filtros para a view
        $filtros = [
            'dt_inicio'   => $dtInicio,
            'dt_fim'      => $dtFim,
            'origem'      => $origem,
            'id_prof'     => $idProf,
            'primeiro_at' => $primeiroAt,
            'motivo'      => $motivo
        ];

        $dadosRelatorio = [];

        try {
            // 4. CHAMA O MODEL (Este sim precisa dos argumentos na ordem correta)
            $dadosRelatorio = $ReportModel->getRelatorioQuantitativo(
                $dtInicio,
                $dtFim,
                $idProf,
                $origem,
                $primeiroAt,
                $motivo
            );
        } catch (\Exception $e) {
            $dadosRelatorio = [];
        }

        // 5. Carrega a View
        // Verifique se o caminho da view 'relatorio_quantitativo.phtml' está correto na sua estrutura
        // Se estiver na raiz de views/pages, use apenas 'pages/relatorio_quantitativo' ou 'relatorio_quantitativo'
        $this->view('pages/relatorio_quantitativo', [
            'profissionais'  => $profissionais,
            'dadosRelatorio' => $dadosRelatorio,
            'filtros'        => $filtros
        ]);
    }
}
