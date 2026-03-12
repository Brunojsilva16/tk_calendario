<?php

namespace App\Core;

class Auth
{

    /**
     * Inicia a sessão com configurações de tempo estendidas.
     */
    public static function init()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            
            // 1. Configura o Garbage Collection do PHP para 10 horas (36000 segundos)
            // ini_set('session.gc_maxlifetime', 36000);

            // // 2. Configura o Cookie da Sessão
            // session_set_cookie_params([
            //     'lifetime' => 0,      // 0 = Expira apenas quando fecha o navegador
            //     'path'     => '/',
            //     'domain'   => '',     // Domínio atual
            //     'secure'   => false,  // Mude para true se estiver usando HTTPS
            //     'httponly' => true    // Previne acesso via JavaScript
            // ]);

            session_start();
        }
    }

    /**
     * Realiza o login do usuário na sessão
     */
    public static function login($user)
    {
        // Converte objeto para array se necessário
        if (is_object($user)) {
            $user = (array) $user;
        }

        // Segurança: Regenera o ID da sessão
        session_regenerate_id(true);

        $nivel = isset($user['nivel']) ? (int)$user['nivel'] : 1;

        // Tenta capturar o ID do profissional de várias formas possíveis para evitar erros
        // $profId = $user['id_profissional_real'] ?? $user['id_prof'] ?? $user['id_profissional'] ?? null;

        $sessionData = [
            'id' => $user['id_user'],
            'name' => $user['nome'],
            'email' => $user['email'],
            'level' => $nivel,
            'id_profissional' => $user['id_profissional'] // Salva com a chave padronizada
        ];

        $_SESSION['user'] = $sessionData;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
    }

    /**
     * Verifica se o usuário está logado e controla o tempo de inatividade (10h)
     */
    public static function isLogged()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }

        $timeout_duration = 36000; // 10 Horas

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            self::logout(); 
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id()
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function name()
    {
        return $_SESSION['user']['name'] ?? 'Visitante';
    }

    public static function email()
    {
        return $_SESSION['user']['email'] ?? '';
    }

    public static function level()
    {
        return $_SESSION['user']['level'] ?? null;
    }

    public static function isProfessional()
    {
        return self::level() === 1;
    }

    public static function logout()
    {
        unset($_SESSION['user']);
        unset($_SESSION['logged_in']);
        unset($_SESSION['last_activity']);
        session_destroy();
    }

    public static function requireLevel($minLevel)
    {
        if (!self::isLogged()) {
            $_SESSION['flash_error'] = 'Sua sessão expirou. Faça login novamente.';
            $url = defined('URL_BASE') ? URL_BASE : '';
            header("Location: {$url}/login");
            exit;
        }

        if (self::level() < $minLevel) {
            $_SESSION['error'] = 'Acesso não permitido.';
            $url = defined('URL_BASE') ? URL_BASE : '';
            header("Location: {$url}/home");
            exit;
        }
    }

        /**
     * Método auxiliar para checar e redirecionar se não estiver logado.
     * Uso: Auth::protect(); dentro de um Controller.
     */
    public static function protect()
    {
        if (!self::isLogged()) {
            // Redireciona para login
            header('Location: ' . URL_BASE . '/login');
            exit;
        }
    }
}