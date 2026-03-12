<?php
echo "Versão PHP: " . phpversion() . "<br>";
echo "Extensões carregadas: <br>";
print_r(get_loaded_extensions());

if (class_exists('PDO')) {
    echo "<br><br>✅ A classe PDO existe neste ambiente.";
    if (in_array('pdo_mysql', get_loaded_extensions())) {
        echo "<br>✅ O driver pdo_mysql está ativo.";
    } else {
        echo "<br>❌ O driver pdo_mysql NÃO foi encontrado.";
    }
} else {
    echo "<br><br>❌ A classe PDO NÃO EXISTE neste ambiente.";
}