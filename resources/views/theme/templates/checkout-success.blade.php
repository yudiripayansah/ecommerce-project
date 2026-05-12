@extends('theme.layouts.app')

@section('title', 'Order Confirmed — ' . store_name())

@section('content')
    @include('theme.sections.main-checkout-success')
@endsection
