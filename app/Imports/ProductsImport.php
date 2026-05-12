<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    public int $importedCount = 0;

    public function model(array $row): ?Product
    {
        $title = trim($row['title'] ?? '');

        if (blank($title)) {
            return null;
        }

        $handle = trim($row['handle'] ?? '');
        if (blank($handle)) {
            $handle = Str::slug($title);
        }

        // Ensure handle is unique by appending a suffix if needed
        $base    = $handle;
        $counter = 1;
        while (Product::where('handle', $handle)->exists()) {
            $handle = $base . '-' . $counter++;
        }

        $status = strtolower(trim($row['status'] ?? 'draft'));
        if (! in_array($status, ['draft', 'active', 'archived'])) {
            $status = 'draft';
        }

        $price = (float) str_replace(['.', ','], ['', '.'], $row['price'] ?? 0);
        $compareAtPrice = filled($row['compare_at_price'] ?? null)
            ? (float) str_replace(['.', ','], ['', '.'], $row['compare_at_price'])
            : null;

        $this->importedCount++;

        return new Product([
            'title'            => $title,
            'handle'           => $handle,
            'description'      => $row['description'] ?? null,
            'price'            => $price,
            'compare_at_price' => $compareAtPrice,
            'vendor'           => $row['vendor'] ?? null,
            'product_type'     => $row['product_type'] ?? null,
            'status'           => $status,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'title.required' => 'Kolom title wajib diisi.',
            'price.required' => 'Kolom price wajib diisi.',
            'price.numeric'  => 'Kolom price harus berupa angka.',
        ];
    }
}
