@extends('theme.layouts.app')

@section('title', ($page->meta_title ?? $page->title) . ' — ' . store_name())
@section('meta_description', $page->meta_description ?? '')

@section('content')
    @include('theme.sections.main-page')
@endsection
