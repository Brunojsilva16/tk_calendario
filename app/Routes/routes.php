<?php
// app/Routes/routes.php

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\TokenController;
use App\Controllers\MeusTokensController;
use App\Controllers\PatientController; 
use App\Controllers\CalendarController; 
use App\Controllers\ReportController; // Adicione o Use

// --- Rotas de Teste ---
// $router->get('/teste-conexao', TestController::class, 'testConnection');

// --- Auth ---
$router->get('/login', AuthController::class, 'loginForm');
$router->post('/login', AuthController::class, 'authenticate'); // Atualizado para authenticate
$router->get('/logout', AuthController::class, 'logout');

// --- Painel ---
$router->get('/', HomeController::class, 'index');
$router->get('/home', HomeController::class, 'index');

// --- Tokens ---
$router->get('/gerar-token', TokenController::class, 'create');
$router->post('/gerar-token/salvar', TokenController::class, 'store');
$router->get('/token/imprimir', TokenController::class, 'print');
$router->post('/token/baixar', TokenController::class, 'settle');
$router->get('/meus-tokens', MeusTokensController::class, 'index');
$router->post('/token/excluir', TokenController::class, 'delete');

// --- Edição de Token ---
$router->get('/token/editar', TokenController::class, 'edit');
$router->post('/token/atualizar', TokenController::class, 'update');

// --- Pacientes ---
$router->get('/pacientes', PatientController::class, 'index'); // Lista
$router->get('/pacientes/cadastrar', PatientController::class, 'create'); // Form Novo
$router->post('/pacientes/salvar', PatientController::class, 'store');  // Salvar (Insert/Update)
$router->get('/pacientes/editar', PatientController::class, 'edit');// Ex: /pacientes/editar?id=1
$router->post('/pacientes/update', PatientController::class, 'update');
$router->post('/pacientes/delete', PatientController::class, 'delete');// Ex: /pacientes/excluir?id=1
// API Busca
$router->get('/api/pacientes/busca', PatientController::class, 'apiSearch');

$router->get('/reembolso', TokenController::class, 'reembolso');
$router->post('/token/reembolso/save', TokenController::class, 'saveReembolso');

// --- RELATÓRIOS (NOVO) ---
$router->get('/relatorios', ReportController::class, 'index');         // Tela de Filtros
$router->get('/relatorios/gerar', ReportController::class, 'generate'); // Processamento
$router->get('/relatorios/quantitativo', ReportController::class, 'getRelatorioQuantitativo');

// Adicione estas linhas no seu arquivo routes.php

// Rota para renderizar a página do calendário
$router->get('/calendario', CalendarController::class, 'index');

// Rota para o FullCalendar buscar as sessões via AJAX
$router->get('/api/sessoes/eventos', CalendarController::class, 'getEventos');