<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Product::query()->with('categories', 'tags')->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Handle',
            'Status',
            'Price (Rp)',
            'Compare At Price (Rp)',
            'Vendor',
            'Product Type',
            'Categories',
            'Tags',
            'Published At',
            'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->title,
            $product->handle,
            ucfirst($product->status),
            $product->price,
            $product->compare_at_price,
            $product->vendor,
            $product->product_type,
            $product->categories->pluck('name')->join(', '),
            $product->tags->pluck('name')->join(', '),
            $product->published_at?->format('Y-m-d H:i:s'),
            $product->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F2937']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
