<?php

use App\Core\Auth;

// Garante que a sessão está iniciada para verificar o login
Auth::init();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Clínica Assista' ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= defined('URL_BASE') ? URL_BASE : '' ?>/assets/img/favicon.png" sizes="32x32">

    <!-- Fontes e Ícones -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5 & CSS Personalizado -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= defined('URL_BASE') ? URL_BASE : '' ?>/css/admin.css" rel="stylesheet">

    <!-- CSS DO PAINEL (SB Admin 2 Customizado) -->
    <link href="<?= URL_BASE ?>/css/admin.css" rel="stylesheet">
    <!-- 5. Seu CSS Personalizado -->
    <link href="<?= URL_BASE ?>/css/sidebar.css" rel="stylesheet">
    <link href="<?= URL_BASE ?>/css/style.css" rel="stylesheet">

    <?php if (isset($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link href="<?= defined('URL_BASE') ? URL_BASE : '' ?>/<?= $style ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<!-- Verifica via Auth se está logado para definir a classe do body -->

<body class="<?= Auth::isLogged() ? 'body-admin' : 'body-login' ?>">

    <?php if (Auth::isLogged()): ?>

        <!-- Navbar Superior (Se existir o arquivo) -->
        <?php
        $profiss = $_SESSION['user']['id_profissional'] ?? Null;
        // echo $profiss . ' - ' . $_SESSION['user']['name'] . ' - ' . $_SESSION['user']['level'];

        $navbarPath = __DIR__ . '/partials/navbar.phtml';
        if (file_exists($navbarPath)) {
            require $navbarPath;
        }
        ?>

        <!-- Wrapper Flexbox para Sidebar e Conteúdo -->
        <div class="d-flex" id="wrapper">

            <!-- Sidebar -->
            <?php require __DIR__ . '/partials/sidebar.phtml'; ?>

            <!-- Área Principal de Conteúdo -->
            <main class="w-100 main-content bg-light">

                <!-- Container fluido para padding interno -->
                <div class="container-fluid p-3">

                    <!-- Botão Toggle Mobile (caso a sidebar suma em telas pequenas) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3 mb-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Conteúdo da View -->
                    <?= $content ?>
                </div>

            </main>
        </div>

    <?php else: ?>
        
        <!-- Wrapper de Login (Centralizado) -->
        <?= $content ?>

    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para Toggle do Sidebar (Importante para Mobile) -->
    <script>
        (function($) {
            "use strict";
            // Alternar sidebar
            $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
                $("body").toggleClass("sidebar-toggled");
                $(".sidebar").toggleClass("toggled");
                if ($(".sidebar").hasClass("toggled")) {
                    $('.sidebar .collapse').collapse('hide');
                };
            });
        })(jQuery);
    </script>

    <?php if (isset($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= defined('URL_BASE') ? URL_BASE : '' ?>/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>