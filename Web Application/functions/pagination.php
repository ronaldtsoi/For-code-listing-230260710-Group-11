<?php
/**
 * Function to generate dynamic pagination links.
 *
 * @param int $currentPage The current page number.
 * @param int $totalPages The total number of pages.
 * @param string $url The URL to append the page parameter.
 * @return string The HTML for the pagination links.
 */
function generatePagination($currentPage, $totalPages, $url) {
    // Define the range of pages to display
    $range = 2; // Number of pages to show before and after the current page
    $start = max(1, $currentPage - $range);
    $end = min($totalPages, $currentPage + $range);

    // Start building the pagination HTML
    $paginationHtml = '<nav><ul class="pagination justify-content-center">';

    // Previous button
    if ($currentPage > 1) {
        $paginationHtml .= '<li class="page-item">
            <a class="page-link" href="' . $url . 'page=' . ($currentPage - 1) . '">Previous page</a>
        </li>';
    }

    // First page and ellipsis
    if ($start > 1) {
        $paginationHtml .= '<li class="page-item">
            <a class="page-link" href="' . $url . 'page=1">1</a>
        </li>';
        if ($start > 2) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Page links
    for ($i = $start; $i <= $end; $i++) {
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $paginationHtml .= '<li class="page-item ' . $activeClass . '">
            <a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a>
        </li>';
    }

    // Last page and ellipsis
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $paginationHtml .= '<li class="page-item">
            <a class="page-link" href="' . $url . 'page=' . $totalPages . '">' . $totalPages . '</a>
        </li>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        $paginationHtml .= '<li class="page-item">
            <a class="page-link" href="' . $url . 'page=' . ($currentPage + 1) . '">Next Page</a>
        </li>';
    }

    $paginationHtml .= '</ul></nav>';

    return $paginationHtml;
}
?>