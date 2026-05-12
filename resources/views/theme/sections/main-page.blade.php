<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
        <a href="{{ route('home') }}" class="hover:text-gray-600">Home</a>
        <span>/</span>
        <span class="text-gray-700">{{ $page->title }}</span>
    </nav>

    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">{{ $page->title }}</h1>

    @if ($page->content)
        <div class="prose prose-gray max-w-none leading-relaxed">
            {!! $page->content !!}
        </div>
    @endif
</section>
