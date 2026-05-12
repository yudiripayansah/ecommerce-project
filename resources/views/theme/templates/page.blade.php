@extends('theme.layouts.app')

@section('title', ($page->meta_title ?? $page->title) . ' — ' . config('app.name', 'Store'))
@section('meta_description', $page->meta_description ?? '')

@section('content')
    @include('theme.sections.main-page')
@endsection
