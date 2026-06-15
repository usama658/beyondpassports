<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- The Laravel starter page is retired: `/` is now the public.home view.
         This view is kept only so any stray reference still lands on the homepage. --}}
    <meta http-equiv="refresh" content="0;url={{ url('/') }}">
    <link rel="canonical" href="{{ url('/') }}">
    <title>Redirecting…</title>
</head>
<body>
    <p>Redirecting to <a href="{{ url('/') }}">the homepage</a>.</p>
</body>
</html>
