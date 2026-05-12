@extends('theme.layouts.app')

@section('title', config('app.name', 'Store') . ' — Home')
@section('meta_description', 'Discover our curated selection of quality products.')

@section('content')
    @include('theme.sections.hero')
    @include('theme.sections.featured-collections')
    @include('theme.sections.featured-products')
@endsection
