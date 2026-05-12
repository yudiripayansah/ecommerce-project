@extends('theme.layouts.app')

@section('title', 'Checkout — ' . store_name())

@section('content')
    @include('theme.sections.main-checkout')
@endsection
