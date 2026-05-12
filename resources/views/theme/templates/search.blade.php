@extends('theme.layouts.app')

@section('title', $q ? 'Search: ' . $q . ' — ' . store_name() : 'Search — ' . store_name())
@section('meta_description', $q ? 'Search results for "' . $q . '"' : 'Search our store')

@section('content')
    @include('theme.sections.main-search')
@endsection
