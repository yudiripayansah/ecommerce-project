@extends('theme.layouts.app')

@section('title', 'Cart — ' . config('app.name', 'Store'))

@section('content')
    @include('theme.sections.main-cart')
@endsection
