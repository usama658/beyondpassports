{{-- CMS page shell. Renders the block stack INSIDE the site layout (one Blade pass) so @once/@push
     directives in shared partials behave exactly as on the coded page. --}}
@extends('layouts.public')

@section('title', $page->seo_title ?: $page->title)
@section('description', $page->seo_description ?? '')

@section('content')
@php($registry = app(\App\Cms\BlockRegistry::class))
@foreach ($page->blocks ?? [] as $block)
@php($view = $registry->view($block['type'] ?? ''))
@if ($view)@include($view, ['data' => $block['data'] ?? []])@endif
@endforeach
@endsection
