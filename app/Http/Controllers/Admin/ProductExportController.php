<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ProductExportController extends Controller
{
    public function excel()
    {
        $filename = 'products-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new ProductsExport(), $filename);
    }

    public function pdf()
    {
        $products = Product::with('categories', 'tags')
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.products-pdf', compact('products'))
            ->setPaper('a4', 'landscape');

        $filename = 'products-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}
