@extends('theme.layouts.app')

@section('title', $q ? 'Search: ' . $q . ' — ' . config('app.name', 'Store') : 'Search — ' . config('app.name', 'Store'))
@section('meta_description', $q ? 'Search results for "' . $q . '"' : 'Search our store')

@section('content')
    @include('theme.sections.main-search')
@endsection
