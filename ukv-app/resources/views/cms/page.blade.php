{{-- CMS page shell. Renders the block stack INSIDE the site layout (one Blade pass) so @once/@push
     directives in shared partials behave exactly as on the coded page. --}}
@extends($page->layoutView())

@section('title', $page->seo_title ?: $page->title)
@section('description', $page->seo_description ?? '')

@if ($page->noindex)
  @push('head')<meta name="robots" content="noindex,nofollow">@endpush
@endif
@if (! empty($page->og_image))
  @push('head')<meta property="og:image" content="{{ \Illuminate\Support\Str::startsWith($page->og_image, ['http://', 'https://']) ? $page->og_image : url($page->og_image) }}">@endpush
@endif

@section('content')
@php($registry = app(\App\Cms\BlockRegistry::class))
@foreach ($page->blocks ?? [] as $block)
@php($view = $registry->view($block['type'] ?? ''))
@if ($view)@include($view, ['data' => $block['data'] ?? []])@endif
@endforeach
@endsection
