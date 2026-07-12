{{-- CMS page shell. Wraps rendered blocks in the SAME site chrome coded pages use (layouts.public). --}}
@extends('layouts.public')

@section('title', $page->seo_title ?: $page->title)
@section('description', $page->seo_description ?? '')

@section('content')
    {!! $rendered !!}
@endsection
