<?php

declare(strict_types=1);

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class Excel
{
    /**
     * Streams an array of associative rows as a real .xlsx download and ends the request.
     * $headers maps column label => array key, preserving column order — same shape as
     * Csv::export()/Pdf::export(), so every report reuses the exact column list it already
     * defines.
     */
    public static function export(string $filename, array $headers, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $labels = array_keys($headers);
        $keys = array_values($headers);

        $sheet->fromArray($labels, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $rowIndex = 2;
        foreach ($rows as $row) {
            $line = [];
            foreach ($keys as $key) {
                $line[] = $row[$key] ?? '';
            }
            $sheet->fromArray($line, null, 'A' . $rowIndex);
            $rowIndex++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
