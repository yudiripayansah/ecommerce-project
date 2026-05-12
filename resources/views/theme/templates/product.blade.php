@extends('theme.layouts.app')

@section('title', $product->meta_title ?? ($product->title . ' — ' . store_name()))
@section('meta_description', $product->meta_description ?? '')

@section('content')
    @include('theme.sections.main-product')
@endsection
