@extends('theme.layouts.app')

@section('title', $product->meta_title ?? ($product->title . ' — ' . config('app.name', 'Store')))
@section('meta_description', $product->meta_description ?? '')

@section('content')
    @include('theme.sections.main-product')
@endsection
