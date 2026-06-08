<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelBranding
{
    public const PRIMARY = '1A3A6E';

    public const PRIMARY_DARK = '122A52';

    public const ACCENT = 'F5C400';

    public const ACCENT_SOFT = 'FEF9E7';

    public const TEXT = '1F2937';

    public const MUTED = '6B7280';

    public const BORDER = 'E2E8F0';

    public const ROW_ALT = 'F8FAFC';

    public const WHITE = 'FFFFFF';

    public static function col(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }

    public static function range(int $colCount, int $startRow, int $endRow, int $startCol = 1): string
    {
        return self::col($startCol) . $startRow . ':' . self::col($colCount) . $endRow;
    }

    public static function applyDocumentHeader(
        Worksheet $sheet,
        int $columnCount,
        string $title,
        ?string $subtitle = null,
        ?string $meta = null
    ): int {
        $lastCol = self::col($columnCount);

        self::addLogo($sheet);

        $sheet->mergeCells('B1:' . $lastCol . '1');
        $sheet->setCellValue('B1', $title);
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::PRIMARY]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension(1)->setRowHeight(48);

        $currentRow = 2;

        if ($subtitle) {
            $sheet->mergeCells('A' . $currentRow . ':' . $lastCol . $currentRow);
            $sheet->setCellValue('A' . $currentRow, $subtitle);
            $sheet->getStyle('A' . $currentRow)->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => self::TEXT]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ACCENT_SOFT]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;
        }

        $infoParts = array_filter([
            $meta,
            'Généré le ' . now()->format('d/m/Y à H:i'),
        ]);
        $sheet->mergeCells('A' . $currentRow . ':' . $lastCol . $currentRow);
        $sheet->setCellValue('A' . $currentRow, implode('  ·  ', $infoParts));
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => self::MUTED]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::WHITE]],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::ACCENT]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($currentRow)->setRowHeight(20);
        $currentRow++;

        $sheet->getRowDimension($currentRow)->setRowHeight(8);

        return $currentRow + 1;
    }

    public static function applyTableHeader(Worksheet $sheet, int $row, array $headers): void
    {
        $lastCol = self::col(count($headers));
        $sheet->fromArray([$headers], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => self::WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::PRIMARY_DARK]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::PRIMARY]],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::ACCENT]],
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(26);
    }

    public static function applyDataRows(Worksheet $sheet, int $startRow, array $headers, array $rows): int
    {
        if (empty($rows)) {
            return $startRow;
        }

        $lastCol = self::col(count($headers));
        $sheet->fromArray($rows, null, 'A' . $startRow);
        $lastRow = $startRow + count($rows) - 1;

        $sheet->getStyle('A' . $startRow . ':' . $lastCol . $lastRow)->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => self::TEXT]],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER]],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => false],
        ]);

        for ($i = $startRow; $i <= $lastRow; $i++) {
            if (($i - $startRow) % 2 === 1) {
                $sheet->getStyle('A' . $i . ':' . $lastCol . $i)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ROW_ALT]],
                ]);
            }
        }

        self::applyNumericFormats($sheet, $headers, $startRow, $lastRow);

        return $lastRow;
    }

    public static function applySectionTitle(Worksheet $sheet, int $row, string $title, int $columnCount = 5): void
    {
        $lastCol = self::col($columnCount);
        $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
        $sheet->setCellValue('A' . $row, $title);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => self::PRIMARY]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ACCENT_SOFT]],
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::ACCENT]],
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER]],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(24);
    }

    public static function applyKeyValueBlock(Worksheet $sheet, int $startRow, array $pairs): int
    {
        $row = $startRow;
        foreach ($pairs as [$label, $value]) {
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => self::MUTED]],
            ]);
            $sheet->getStyle('B' . $row)->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => self::TEXT]],
            ]);
            $row++;
        }

        return $row;
    }

    public static function applyFooter(Worksheet $sheet, int $row, int $columnCount, int $recordCount): void
    {
        $lastCol = self::col($columnCount);
        $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
        $sheet->setCellValue('A' . $row, $recordCount . ' enregistrement' . ($recordCount > 1 ? 's' : '') . ' · PDV Connect');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => self::MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
    }

    public static function finalizeSheet(Worksheet $sheet, int $headerRow, int $columnCount, ?int $lastDataRow = null): void
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $sheet->getColumnDimension(self::col($i))->setAutoSize(true);
        }

        $endRow = $lastDataRow ?? $headerRow;
        if ($endRow > $headerRow) {
            $sheet->setAutoFilter('A' . $headerRow . ':' . self::col($columnCount) . $endRow);
            $sheet->freezePane('A' . ($headerRow + 1));
        }

        $sheet->getTabColor()->setRGB(self::PRIMARY);
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5)->setRight(0.3)->setLeft(0.3)->setBottom(0.5);
    }

    public static function finalizeSummarySheet(Worksheet $sheet, int $columnCount): void
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $sheet->getColumnDimension(self::col($i))->setAutoSize(true);
        }

        $sheet->getTabColor()->setRGB(self::ACCENT);
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
    }

    protected static function addLogo(Worksheet $sheet): void
    {
        $logoPath = public_path(PdfBranding::LOGO_PNG_PATH);
        if (! is_readable($logoPath)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setPath($logoPath);
        $drawing->setHeight(36);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(6);
        $drawing->setWorksheet($sheet);

        $sheet->getColumnDimension('A')->setWidth(14);
    }

    protected static function applyNumericFormats(Worksheet $sheet, array $headers, int $startRow, int $lastRow): void
    {
        foreach ($headers as $index => $header) {
            $headerLower = mb_strtolower((string) $header);
            if (! preg_match('/(montant|commission|solde|xof|total)/i', $headerLower)) {
                continue;
            }

            $col = self::col($index + 1);
            $sheet->getStyle($col . $startRow . ':' . $col . $lastRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $sheet->getStyle($col . $startRow . ':' . $col . $lastRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }
}
