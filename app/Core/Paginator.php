<?php

namespace App\Core;

class Paginator
{
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $url;
    private $searchQuery;

    public function __construct($totalItems, $itemsPerPage, $currentPage, $url, $searchQuery = '')
    {
        $this->totalItems = (int) $totalItems;
        $this->itemsPerPage = (int) $itemsPerPage;
        $this->currentPage = (int) $currentPage;
        $this->url = $url;
        $this->searchQuery = $searchQuery;
    }

    public function getTotalPages()
    {
        return ceil($this->totalItems / $this->itemsPerPage);
    }

    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function getLimit()
    {
        return $this->itemsPerPage;
    }

    // Gera o HTML da paginação compatível com Bootstrap
    public function render()
    {
        $totalPages = $this->getTotalPages();
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Navegação"><ul class="pagination justify-content-end mb-0">';
        
        // Monta os parâmetros da URL para manter a busca e o limite
        $queryParams = [];
        if (!empty($this->searchQuery)) {
            $queryParams['search'] = $this->searchQuery;
        }
        if ($this->itemsPerPage != 10) { // Só adiciona se for diferente do padrão
            $queryParams['limit'] = $this->itemsPerPage;
        }
        
        // Função auxiliar para montar query string
        $buildUrl = function($page) use ($queryParams) {
            $params = array_merge($queryParams, ['page' => $page]);
            // Se a URL já tiver query params (ex: ?id=1), usamos & se não ?
            $separator = (strpos($this->url, '?') !== false) ? '&' : '?';
            return $this->url . $separator . http_build_query($params);
        };

        // Botão Anterior
        $prevDisabled = ($this->currentPage <= 1) ? 'disabled' : '';
        $prevPage = max(1, $this->currentPage - 1);
        $html .= sprintf(
            '<li class="page-item %s"><a class="page-link" href="%s">Anterior</a></li>',
            $prevDisabled, $buildUrl($prevPage)
        );

        // Lógica de truncamento (1, 2, ..., 10, 11, 12, ..., 50)
        $start = max(1, $this->currentPage - 2);
        $end = min($totalPages, $this->currentPage + 2);

        if ($start > 1) {
            $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">1</a></li>', $buildUrl(1));
            if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = ($this->currentPage == $i) ? 'active' : '';
            $html .= sprintf(
                '<li class="page-item %s"><a class="page-link" href="%s">%d</a></li>',
                $active, $buildUrl($i), $i
            );
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">%d</a></li>', $buildUrl($totalPages), $totalPages);
        }

        // Botão Próximo
        $nextDisabled = ($this->currentPage >= $totalPages) ? 'disabled' : '';
        $nextPage = min($totalPages, $this->currentPage + 1);
        $html .= sprintf(
            '<li class="page-item %s"><a class="page-link" href="%s">Próximo</a></li>',
            $nextDisabled, $buildUrl($nextPage)
        );

        $html .= '</ul></nav>';
        
        $html .= sprintf(
            '<div class="mt-2 text-muted small text-end">Mostrando %d a %d de %d registros</div>',
            $this->getOffset() + 1,
            min($this->getOffset() + $this->itemsPerPage, $this->totalItems),
            $this->totalItems
        );

        return $html;
    }
}