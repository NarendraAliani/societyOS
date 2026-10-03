<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Society;
use Dompdf\Dompdf;
use Dompdf\Options;

final class Pdf
{
    /**
     * Renders an array of associative rows as a landscape PDF table and ends the request.
     * $headers maps column label => array key, preserving column order — same shape as
     * Csv::export(), so every report reuses the exact column list it already defines.
     */
    public static function export(string $filename, string $title, array $headers, array $rows): void
    {
        $society = Society::current();

        ob_start();
        require __DIR__ . '/../Views/reports/pdf_table.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
