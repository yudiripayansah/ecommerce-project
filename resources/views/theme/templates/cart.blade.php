@extends('theme.layouts.app')

@section('title', 'Cart — ' . store_name())

@section('content')
    @include('theme.sections.main-cart')
@endsection
