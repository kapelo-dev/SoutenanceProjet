<?php

namespace App\Traits;

use App\Support\ExcelBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait Exportable
{
    protected function wantsExcelExport(?Request $request): bool
    {
        return $request && in_array(strtolower((string) $request->input('format')), ['excel', 'xlsx'], true);
    }

    protected function excelFilename(string $basename): string
    {
        return preg_replace('/\.(pdf|xlsx|csv)$/i', '', $basename) . '.xlsx';
    }

    protected function flattenExportCell(mixed $cell): mixed
    {
        if (is_array($cell)) {
            return $cell['libelle'] ?? $cell['badge'] ?? '-';
        }

        return $cell;
    }

    protected function flattenExportRows(array $rows): array
    {
        return array_map(
            fn (array $row) => array_map(fn ($cell) => $this->flattenExportCell($cell), $row),
            $rows
        );
    }

    protected function normalizeExportRows(array|Collection $rows): array
    {
        return collect($rows)
            ->map(fn ($row) => is_array($row) ? $row : (array) $row)
            ->all();
    }

    protected function autoSizeSheetColumns($sheet, int $columnCount): void
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    protected function exportToExcel(
        array $headers,
        array|Collection $data,
        string $filename,
        ?string $title = null,
        ?string $subtitle = null,
        ?string $filtersText = null
    ): HttpFoundationResponse {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(mb_substr(preg_replace('/[^\pL\pN ]/u', '', $title ?? 'Export'), 0, 31));

            $flatData = $this->flattenExportRows($this->normalizeExportRows($data));
            $columnCount = max(count($headers), 1);
            $displayTitle = $title ?? ucfirst(str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

            $headerRow = ExcelBranding::applyDocumentHeader(
                $sheet,
                $columnCount,
                $displayTitle,
                $subtitle,
                $filtersText
            );

            ExcelBranding::applyTableHeader($sheet, $headerRow, $headers);
            $lastDataRow = ExcelBranding::applyDataRows($sheet, $headerRow + 1, $headers, $flatData);
            ExcelBranding::applyFooter($sheet, max($lastDataRow, $headerRow) + 2, $columnCount, count($flatData));
            ExcelBranding::finalizeSheet($sheet, $headerRow, $columnCount, max($lastDataRow, $headerRow));

            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            (new Xlsx($spreadsheet))->save($tempFile);

            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Erreur export Excel: ' . $e->getMessage());

            return response('Erreur lors de la génération du fichier Excel: ' . $e->getMessage(), 500);
        }
    }

    protected function exportToCsv(array $headers, array $data, string $filename): Response
    {
        $headers_response = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers_response);
    }

    protected function exportToPdf(
        string $title,
        array $headers,
        array $data,
        string $filename,
        string $orientation = 'portrait',
        ?Request $request = null,
        array $extraViewData = []
    ): Response {
        try {
            $viewData = array_merge(compact('title', 'headers', 'data'), $extraViewData);
            $pdf = Pdf::loadView('exports.pdf-template', $viewData);
            $pdf->setPaper('a4', $orientation);
            $pdf->setOption('enable-local-file-access', true);

            return $this->pdfResponse($pdf, $filename, $request);
        } catch (\Exception $e) {
            \Log::error('Erreur export PDF: ' . $e->getMessage());

            return response('Erreur lors de la génération du PDF: ' . $e->getMessage(), 500);
        }
    }

    protected function exportRapportToPdf(
        string $title,
        array $headers,
        array $data,
        string $filename,
        array $viewData,
        ?Request $request = null,
        string $orientation = 'portrait'
    ): Response {
        try {
            $pdf = Pdf::loadView('exports.rapport-pdf-template', array_merge(
                compact('title', 'headers', 'data'),
                $viewData
            ));
            $pdf->setPaper('a4', $orientation);
            $pdf->setOption('enable-local-file-access', true);

            return $this->pdfResponse($pdf, $filename, $request);
        } catch (\Exception $e) {
            \Log::error('Erreur export PDF rapport: ' . $e->getMessage());

            return response('Erreur lors de la génération du PDF: ' . $e->getMessage(), 500);
        }
    }

    protected function pdfResponse($pdf, string $filename, ?Request $request = null): Response
    {
        $preview = $request?->boolean('preview') ?? false;

        if ($preview) {
            return $pdf->stream($filename, ['Attachment' => false]);
        }

        return $pdf->download($filename);
    }

    protected function exportRapportToExcel(
        string $filename,
        array $headers,
        array|Collection $data,
        array $statsGlobales,
        array|Collection $statsOperateurs,
        array|Collection $topAgents,
        $dateDebut,
        $dateFin,
        ?string $filtersText = null
    ): HttpFoundationResponse {
        try {
            $statsOperateurs = collect($statsOperateurs);
            $topAgents = collect($topAgents);
            $spreadsheet = new Spreadsheet();

            $summary = $spreadsheet->getActiveSheet();
            $summary->setTitle('Résumé');

            $summaryHeaderRow = ExcelBranding::applyDocumentHeader(
                $summary,
                5,
                'Rapport des Transactions',
                'PDV Connect — Synthèse de la période',
                'Période : ' . $dateDebut->format('d/m/Y') . ' — ' . $dateFin->format('d/m/Y') . ($filtersText ? '  ·  ' . $filtersText : '')
            );

            $row = $summaryHeaderRow + 1;
            ExcelBranding::applySectionTitle($summary, $row, 'Vue d\'ensemble');
            $row = ExcelBranding::applyKeyValueBlock($summary, $row + 1, [
                ['Transactions validées', $statsGlobales['total_transactions']],
                ['Montant total (XOF)', $statsGlobales['montant_total']],
                ['Commissions (XOF)', $statsGlobales['commission_total']],
                ['Agents actifs', $statsGlobales['nombre_agents']],
            ]);

            if ($statsOperateurs->isNotEmpty()) {
                $row += 2;
                ExcelBranding::applySectionTitle($summary, $row, 'Performance par opérateur');
                $row++;
                ExcelBranding::applyTableHeader($summary, $row, ['Opérateur', 'Transactions', 'Montant total (XOF)', 'Commission (XOF)']);
                $opRows = $statsOperateurs->map(fn ($stat) => [
                    $stat['operateur']->libelle,
                    $stat['nombre_transactions'],
                    $stat['montant_total'],
                    $stat['commission_total'],
                ])->all();
                ExcelBranding::applyDataRows($summary, $row + 1, ['Opérateur', 'Transactions', 'Montant total (XOF)', 'Commission (XOF)'], $opRows);
                $row += count($opRows) + 1;
            }

            if ($topAgents->isNotEmpty()) {
                $row += 2;
                ExcelBranding::applySectionTitle($summary, $row, 'Top 10 agents');
                $row++;
                ExcelBranding::applyTableHeader($summary, $row, ['Rang', 'Agent', 'Transactions', 'Montant (XOF)', 'Commission (XOF)']);
                $agentRows = $topAgents->values()->map(fn ($topAgent, $index) => [
                    $index + 1,
                    $topAgent['agent']->nomComplet,
                    $topAgent['nombre_transactions'],
                    $topAgent['montant_total'],
                    $topAgent['commission_total'],
                ])->all();
                ExcelBranding::applyDataRows($summary, $row + 1, ['Rang', 'Agent', 'Transactions', 'Montant (XOF)', 'Commission (XOF)'], $agentRows);
            }

            ExcelBranding::finalizeSummarySheet($summary, 5);

            $transactions = $spreadsheet->createSheet();
            $transactions->setTitle('Transactions');
            $flatData = $this->flattenExportRows($this->normalizeExportRows($data));
            $columnCount = count($headers);

            $txHeaderRow = ExcelBranding::applyDocumentHeader(
                $transactions,
                $columnCount,
                'Détail des transactions',
                'Liste complète des opérations filtrées'
            );
            ExcelBranding::applyTableHeader($transactions, $txHeaderRow, $headers);
            $lastDataRow = ExcelBranding::applyDataRows($transactions, $txHeaderRow + 1, $headers, $flatData);
            ExcelBranding::applyFooter($transactions, max($lastDataRow, $txHeaderRow) + 2, $columnCount, count($flatData));
            ExcelBranding::finalizeSheet($transactions, $txHeaderRow, $columnCount, max($lastDataRow, $txHeaderRow));

            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            (new Xlsx($spreadsheet))->save($tempFile);

            return response()->download($tempFile, $this->excelFilename($filename), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Erreur export Excel rapport: ' . $e->getMessage());

            return response('Erreur lors de la génération du fichier Excel: ' . $e->getMessage(), 500);
        }
    }
}
