<?php

declare(strict_types=1);

namespace App\Helpers;

final class Csv
{
    /**
     * Streams an array of associative rows as a CSV download and ends the request.
     * $headers maps column label => array key, preserving column order.
     */
    public static function export(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($headers));

        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $key) {
                $line[] = $row[$key] ?? '';
            }
            fputcsv($out, $line);
        }

        fclose($out);
        exit;
    }
}
