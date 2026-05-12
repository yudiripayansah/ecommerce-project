@extends('theme.layouts.app')

@section('title', ($collection->meta_title ?? $collection->title) . ' — ' . config('app.name', 'Store'))
@section('meta_description', $collection->meta_description ?? '')

@section('content')
    @include('theme.sections.main-collection')
@endsection
