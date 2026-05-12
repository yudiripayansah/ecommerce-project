@extends('theme.layouts.app')

@section('title', 'Order Confirmed — ' . config('app.name', 'Store'))

@section('content')
    @include('theme.sections.main-checkout-success')
@endsection
