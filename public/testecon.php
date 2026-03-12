
<?php
// --- CONFIGURAÇÕES DE BANCO DE DADOS ---
// Edite as variáveis abaixo conforme seu ambiente
$db_host = 'localhost'; // ou o IP do servidor / nome do container (ex: 'db')
$db_user = 'controleassistac_assist';      // Usuário do banco
$db_pass = 'Ass05649@';     // Senha do banco
$db_name = 'controleassistac_26_tokens';  // Nome do banco de dados (opcional para teste apenas de conexão)

// Variáveis de controle de status
$db_status = "";
$db_message = "";
$conn = null;

// Tenta realizar a conexão
try {
    // Supressão de erros para capturá-los manualmente e não expor dados sensíveis
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    $conn->set_charset("utf8");
    $db_status = "success";
    $db_message = "Conexão realizada com sucesso!";
    
} catch (mysqli_sql_exception $e) {
    $db_status = "error";
    $db_message = "Falha na conexão: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Ambiente PHP + BD</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f9; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px; width: 100%; }
        h1 { text-align: center; color: #444; margin-bottom: 1.5rem; }
        .info-block { margin-bottom: 1rem; padding: 10px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; }
        
        /* Status Box Styles */
        .status-box { padding: 15px; border-radius: 5px; text-align: center; margin-top: 20px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        footer { margin-top: 20px; text-align: center; font-size: 0.8rem; color: #888; }
    </style>
</head>
<body>

<div class="container">
    <h1>Ambiente PHP</h1>

    <div class="info-block">
        <span class="label">Versão do PHP:</span>
        <span><?php echo phpversion(); ?></span>
    </div>

    <div class="info-block">
        <span class="label">Servidor Web:</span>
        <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'; ?></span>
    </div>

    <div class="info-block">
        <span class="label">Sistema Operacional:</span>
        <span><?php echo PHP_OS; ?></span>
    </div>

    <?php if ($db_status == 'success'): ?>
        <div class="status-box success">
            ✅ <?php echo $db_message; ?><br>
            <small>Host: <?php echo $db_host; ?> | Versão do BD: <?php echo $conn->server_info; ?></small>
        </div>
    <?php else: ?>
        <div class="status-box error">
            ❌ <?php echo $db_message; ?>
        </div>
    <?php endif; ?>
    
    <?php 
    // Fecha a conexão se estiver aberta
    if ($conn instanceof mysqli) {
        $conn->close();
    }
    ?>

    <footer>
        Arquivo de teste gerado para verificação rápida.
    </footer>
</div>

</body>
</html>