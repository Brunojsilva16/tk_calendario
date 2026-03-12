<?php
// app/Controllers/MeusTokensController.php
// Crie/Atualize este arquivo se ele não existir ou substitua a lógica no seu controller correspondente.

namespace App\Controllers;

use App\Core\Auth;
use App\Models\TokenModel;
use App\Core\Paginator;

class MeusTokensController extends BaseController
{
    public function index()
    {
        Auth::protect();
        $userId = Auth::id(); // Assumindo que Auth::id() retorna o ID do usuário logado

        $tokenModel = new TokenModel();

        // Parâmetros de Paginação e Busca
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 10;
        $search = $_GET['search'] ?? '';

        // 1. Conta o total de registros do usuário
        $totalItems = $tokenModel->countByUserId($userId, $search);

        // 2. Inicializa o Paginator
        $paginator = new Paginator($totalItems, $limit, $page, URL_BASE . '/meus-tokens', $search);

        // 3. Busca os dados paginados
        $tokens = $tokenModel->getByUserId($userId, $limit, $search, $paginator->getOffset());

        $data = [
            'view' => 'meus_tokens',
            'tokens' => $tokens,
            'paginator' => $paginator,
            'limit' => $limit,
            'search' => $search,
            'usuario_nome' => Auth::name(),
            'usuario_email' => Auth::email()
        ];

        $this->view('pages/meus_tokens', $data);
    }
}