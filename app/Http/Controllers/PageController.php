<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $handle)
    {
        $page = Page::where('handle', $handle)
            ->where('visibility', 'visible')
            ->firstOrFail();

        return view('theme.templates.page', compact('page'));
    }
}
