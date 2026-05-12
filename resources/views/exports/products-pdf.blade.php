<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Products Export</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111827; background: #fff; }

    .header { padding: 18px 24px 14px; border-bottom: 2px solid #1F2937; margin-bottom: 16px; }
    .header h1 { font-size: 18px; font-weight: bold; color: #1F2937; }
    .header .meta { font-size: 8px; color: #6B7280; margin-top: 4px; }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #1F2937; color: #fff; }
    thead th { padding: 7px 8px; text-align: left; font-size: 8px; font-weight: bold; letter-spacing: 0.05em; text-transform: uppercase; }
    tbody tr { border-bottom: 1px solid #E5E7EB; }
    tbody tr:nth-child(even) { background: #F9FAFB; }
    tbody td { padding: 6px 8px; vertical-align: top; }

    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7.5px; font-weight: bold; }
    .badge-active   { background: #D1FAE5; color: #065F46; }
    .badge-draft    { background: #F3F4F6; color: #374151; }
    .badge-archived { background: #FEE2E2; color: #991B1B; }

    .price { font-weight: bold; }
    .compare { color: #9CA3AF; text-decoration: line-through; font-size: 8px; }

    .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #E5E7EB; font-size: 7.5px; color: #9CA3AF; text-align: right; }
</style>
</head>
<body>

<div class="header">
    <h1>Products List</h1>
    <div class="meta">Generated on {{ now()->format('d M Y, H:i') }} &nbsp;·&nbsp; Total: {{ $products->count() }} products</div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:20px">#</th>
            <th style="width:130px">Title</th>
            <th style="width:45px">Status</th>
            <th style="width:65px">Price</th>
            <th style="width:65px">Vendor</th>
            <th style="width:65px">Type</th>
            <th style="width:80px">Categories</th>
            <th style="width:60px">Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $i => $product)
        <tr>
            <td style="color:#9CA3AF">{{ $i + 1 }}</td>
            <td>
                <strong>{{ $product->title }}</strong>
                <div style="color:#9CA3AF; font-size:7.5px;">{{ $product->handle }}</div>
            </td>
            <td>
                <span class="badge badge-{{ $product->status }}">
                    {{ ucfirst($product->status) }}
                </span>
            </td>
            <td>
                <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                @if($product->compare_at_price)
                <div class="compare">Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}</div>
                @endif
            </td>
            <td>{{ $product->vendor ?: '—' }}</td>
            <td>{{ $product->product_type ?: '—' }}</td>
            <td>{{ $product->categories->pluck('name')->join(', ') ?: '—' }}</td>
            <td>{{ $product->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    {{ store_name() }} &nbsp;·&nbsp; Products Export
</div>

</body>
</html>
