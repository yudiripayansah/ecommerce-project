<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Validation\DataValidation;

class ProductImportTemplateController extends Controller
{
    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        // ── Header row ───────────────────────────────────────────────────────
        $headers = [
            'A1' => 'title',
            'B1' => 'handle',
            'C1' => 'description',
            'D1' => 'price',
            'E1' => 'compare_at_price',
            'F1' => 'vendor',
            'G1' => 'product_type',
            'H1' => 'status',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        // Header styling
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF374151'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Column widths ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(30); // title
        $sheet->getColumnDimension('B')->setWidth(28); // handle
        $sheet->getColumnDimension('C')->setWidth(40); // description
        $sheet->getColumnDimension('D')->setWidth(15); // price
        $sheet->getColumnDimension('E')->setWidth(20); // compare_at_price
        $sheet->getColumnDimension('F')->setWidth(20); // vendor
        $sheet->getColumnDimension('G')->setWidth(20); // product_type
        $sheet->getColumnDimension('H')->setWidth(15); // status

        // ── Sample rows ──────────────────────────────────────────────────────
        $samples = [
            ['Kaos Polos Putih', 'kaos-polos-putih', 'Kaos polos bahan cotton combed 30s', 75000, 100000, 'BrandX', 'Kaos', 'active'],
            ['Celana Jeans Slim', 'celana-jeans-slim', '', 150000, '', 'BrandY', 'Celana', 'active'],
            ['Tas Selempang', '', '', 120000, 180000, '', 'Tas', 'draft'],
        ];

        foreach ($samples as $rowIndex => $row) {
            $excelRow = $rowIndex + 2;
            $sheet->setCellValue("A{$excelRow}", $row[0]);
            $sheet->setCellValue("B{$excelRow}", $row[1]);
            $sheet->setCellValue("C{$excelRow}", $row[2]);
            $sheet->setCellValue("D{$excelRow}", $row[3]);
            $sheet->setCellValue("E{$excelRow}", $row[4]);
            $sheet->setCellValue("F{$excelRow}", $row[5]);
            $sheet->setCellValue("G{$excelRow}", $row[6]);
            $sheet->setCellValue("H{$excelRow}", $row[7]);
        }

        // Style sample rows
        $sheet->getStyle('A2:H4')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF9FAFB'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFE5E7EB'],
                ],
            ],
        ]);

        // Mark required columns (title, price)
        foreach (['A', 'D'] as $col) {
            $sheet->getStyle("{$col}1")->getFont()->getColor()->setARGB('FFFBBF24');
        }

        // ── Data validation for status column ─────────────────────────────
        $validation = $sheet->getDataValidation('H2:H1000');
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(false);
        $validation->setFormula1('"draft,active,archived"');

        // ── Instructions sheet ───────────────────────────────────────────────
        $infoSheet = $spreadsheet->createSheet();
        $infoSheet->setTitle('Petunjuk');

        $instructions = [
            ['Kolom', 'Keterangan', 'Wajib', 'Contoh'],
            ['title', 'Nama produk', 'Ya', 'Kaos Polos Putih'],
            ['handle', 'Slug URL unik (huruf kecil, tanda hubung). Jika kosong, dibuat otomatis dari title.', 'Tidak', 'kaos-polos-putih'],
            ['description', 'Deskripsi produk (teks biasa)', 'Tidak', 'Deskripsi produk...'],
            ['price', 'Harga jual (angka, tanpa titik ribuan)', 'Ya', '75000'],
            ['compare_at_price', 'Harga coret / harga asli (angka)', 'Tidak', '100000'],
            ['vendor', 'Nama merek / supplier', 'Tidak', 'BrandX'],
            ['product_type', 'Jenis produk', 'Tidak', 'Kaos'],
            ['status', 'Status produk: draft, active, atau archived', 'Tidak', 'active'],
        ];

        foreach ($instructions as $rowIdx => $cols) {
            foreach ($cols as $colIdx => $val) {
                $infoSheet->setCellValueByColumnAndRow($colIdx + 1, $rowIdx + 1, $val);
            }
        }

        $infoSheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
        ]);

        $infoSheet->getColumnDimension('A')->setWidth(22);
        $infoSheet->getColumnDimension('B')->setWidth(55);
        $infoSheet->getColumnDimension('C')->setWidth(12);
        $infoSheet->getColumnDimension('D')->setWidth(25);

        $infoSheet->getStyle('A2:D9')->getAlignment()->setWrapText(true);
        $infoSheet->getStyle('A2:D9')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        foreach (range(2, 9) as $row) {
            $infoSheet->getRowDimension($row)->setRowHeight(28);
        }

        // ── Output ───────────────────────────────────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);

        $writer   = new Xlsx($spreadsheet);
        $filename = 'template-import-products.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
