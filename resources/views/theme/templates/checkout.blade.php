@extends('theme.layouts.app')

@section('title', 'Checkout — ' . config('app.name', 'Store'))

@section('content')
    @include('theme.sections.main-checkout')
@endsection
