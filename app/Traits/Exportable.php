<?php

namespace App\Traits;

use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Barryvdh\DomPDF\Facade\Pdf;

trait Exportable
{
    /**
     * Exporter en Excel (XLSX) avec PhpSpreadsheet
     */
    protected function exportToExcel(array $headers, array $data, string $filename): Response
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Définir les en-têtes
            $sheet->fromArray([$headers], null, 'A1');
            
            // Style pour les en-têtes
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FBBF24'], // Jaune
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            $lastCol = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);
            
            // Ajouter les données
            if (!empty($data)) {
                $sheet->fromArray($data, null, 'A2');
                
                // Style pour les données
                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];
                
                $lastRow = count($data) + 1;
                $sheet->getStyle('A2:' . $lastCol . $lastRow)->applyFromArray($dataStyle);
                
                // Ajuster la largeur des colonnes
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
            
            // Enregistrer dans un fichier temporaire
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Erreur export Excel: ' . $e->getMessage());
            return response('Erreur lors de la génération du fichier Excel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Exporter en CSV (compatibilité)
     */
    protected function exportToCsv(array $headers, array $data, string $filename): Response
    {
        $headers_response = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($headers, $data) {
            $file = fopen('php://output', 'w');
            
            // Ajouter BOM pour UTF-8 (pour Excel)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, $headers, ';');
            
            // Données
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers_response);
    }

    /**
     * Exporter en PDF avec dompdf
     */
    protected function exportToPdf(string $title, array $headers, array $data, string $filename): Response
    {
        try {
            $pdf = Pdf::loadView('exports.pdf-template', compact('title', 'headers', 'data'));
            $pdf->setPaper('a4', 'landscape'); // Format paysage pour les tableaux larges
            $pdf->setOption('enable-local-file-access', true);
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Erreur export PDF: ' . $e->getMessage());
            return response('Erreur lors de la génération du PDF: ' . $e->getMessage(), 500);
        }
    }
}
