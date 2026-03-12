<?php

namespace App\Helpers;

use DateTime;
use Exception;

class DataHelper
{
    /**
     * Remove a hora de uma string de data/hora para evitar erros no cálculo.
     *
     * @param string $dataComHora
     * @return string
     */
    public static function removerHorario($dataComHora)
    {
        if (empty($dataComHora)) {
            return null;
        }

        try {
            $data = new DateTime($dataComHora);
            return $data->format('Y-m-d');
        } catch (Exception $e) {
            return $dataComHora; // Retorna original em caso de erro
        }
    }

    /**
     * Calcula o tempo desde o cadastro e retorna texto formatado.
     * Ex: "Cliente há 1 ano e 2 meses"
     *
     * @param string $data
     * @return string
     */
    public static function calcularTempoCadastro($data)
    {
        // Se a data estiver vazia
        if (empty($data)) {
            return "Novo Cliente";
        }

        // Chama a função vizinha usando self::
        $dataLimpa = self::removerHorario($data);
        
        try {
            $data_cadastro = new DateTime($dataLimpa);
            $data_atual    = new DateTime();
            
            // Zera as horas para comparar apenas datas
            $data_cadastro->setTime(0, 0, 0);
            $data_atual->setTime(0, 0, 0);

            // Se a data de cadastro for futura (erro de cadastro), trata como novo
            if ($data_cadastro > $data_atual) {
                return "Novo Cliente";
            }

            $intervalo = $data_cadastro->diff($data_atual);
        } catch (Exception $e) {
            return "Data inválida";
        }

        $partes = [];
        
        // Lógica de Anos
        if ($intervalo->y > 0) {
            $partes[] = $intervalo->y . ' ' . ($intervalo->y == 1 ? 'ano' : 'anos');
        }

        // Lógica de Meses
        if ($intervalo->m > 0) {
            $partes[] = $intervalo->m . ' ' . ($intervalo->m == 1 ? 'mês' : 'meses');
        }

        // Lógica de Dias (opcional: se quiser mostrar dias apenas se não tiver anos)
        // Se tiver menos de 1 mês, mostra os dias
        if ($intervalo->y == 0 && $intervalo->m == 0 && $intervalo->d > 0) {
            $partes[] = $intervalo->d . ' ' . ($intervalo->d == 1 ? 'dia' : 'dias');
        }

        // --- FORMATAÇÃO FINAL ---

        // 1. Se vazio, é novo cliente (hoje)
        if (empty($partes)) {
            return "Cliente desde hoje";
        }

        // 2. Monta a string com "e" ou vírgulas
        $texto = "";
        $qtd = count($partes);

        if ($qtd === 1) {
            $texto = $partes[0];
        } elseif ($qtd === 2) {
            $texto = $partes[0] . " e " . $partes[1];
        } else {
            // Caso raro (ano, mês e dia), pega os dois primeiros ou formata tudo
            $ultimo = array_pop($partes);
            $texto = implode(", ", $partes) . " e " . $ultimo;
        }

        return "Cliente há " . $texto;
    }
}