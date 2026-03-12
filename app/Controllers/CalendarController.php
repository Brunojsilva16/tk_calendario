<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\SessaoModel;

class CalendarController extends BaseController
{
    public function __construct()
    {
        // Garante que apenas usuários logados acessem
        date_default_timezone_set('America/Sao_Paulo');
        Auth::init();

        if (!Auth::isLogged()) {
            $this->redirect('/login');
            return;
        }
    }

    /**
     * Renderiza a página principal do calendário
     */
    public function index()
    {
        $data = [
            'title' => 'Calendário de Sessões',
            'pageScripts' =>['js/calendario.js']
        ];
        // Renderiza usando o layout padrão do seu sistema
        $this->view('pages/calendario', $data);
    }

    /**
     * Retorna os dados em formato JSON para o FullCalendar
     */
    public function getEventos()
    {
        // O FullCalendar envia parâmetros 'start' e 'end' automaticamente via GET
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        $sessaoModel = new SessaoModel();
        $sessoes = $sessaoModel->getSessoesNoPeriodo($start, $end);

        echo "<h3>Debug do POST (Dados recebidos do formulário):</h3>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc;'>";
        print_r($sessoes);
        echo "</pre>";
        echo "<hr>";
        echo "<p>Se você vê o array 'sessoes' acima com os dados corretos, o formulário está funcionando.</p>";
        echo "<p>Remova ou comente este bloco no arquivo TokenController.php para prosseguir com o salvamento.</p>";
        exit;

        $eventos = [];

        foreach ($sessoes as $sessao) {
            // Adaptando os campos do seu banco para o formato do FullCalendar
            // Altere 'data_agendamento', 'nome_paciente' para os nomes reais das colunas da sua tabela "sessoes"

            // Exemplo de junção de Data e Hora: "2023-10-25T14:30:00"
            $dataHoraInicio = $sessao->data_sessao . 'T' . $sessao->hora_inicio;

            // Se houver hora de término, adicione. Senão, pode omitir.
            $dataHoraFim = $sessao->data_sessao . 'T' . ($sessao->hora_fim ?? clone $sessao->hora_inicio);

            $eventos[] = [
                'id'    => $sessao->id,
                'title' => $sessao->nome_paciente . ' - ' . $sessao->tipo_sessao, // O que vai aparecer escrito no bloco
                'start' => $dataHoraInicio,
                'end'   => $dataHoraFim,
                'color' => '#007bff', // Cor do evento no calendário (você pode criar lógicas para cores diferentes)
                'url'   => URL_BASE . '/sessoes/editar/' . $sessao->id // Link opcional ao clicar no evento
            ];
        }

        // Define o header como JSON e imprime o resultado
        header('Content-Type: application/json');
        echo json_encode($eventos);
        exit;
    }
}
