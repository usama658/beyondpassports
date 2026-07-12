{{-- Email-safe personalised document checklist.
     Expects $items in the RequirementService shape:
       list<array{document_key, label, note, category, mandatory}>
     Uses the mail::table component (the reliable primitive for these markdown mailables) — a bare
     markdown list does NOT render inside the message component's HTML, and a raw indented HTML
     table gets escaped as a code block. The group name is the table's header row (a standalone
     bold heading after a table renders as literal ** because it lacks a blank line before it).
     Renders nothing when $items is empty. --}}
@php
    $items = $items ?? [];
    $required = array_values(array_filter($items, static fn ($i) => ! empty($i['mandatory'])));
    $recommended = array_values(array_filter($items, static fn ($i) => empty($i['mandatory'])));
@endphp
@if (! empty($items))

**Your document checklist**

@if (! empty($required))
@component('mail::table')
| Required documents |
| :----------------- |
@foreach ($required as $item)
| {{ $item['label'] }}{{ ! empty($item['note']) ? ': '.$item['note'] : '' }} |
@endforeach
@endcomponent
@endif
@if (! empty($recommended))
@component('mail::table')
| Recommended documents |
| :-------------------- |
@foreach ($recommended as $item)
| {{ $item['label'] }}{{ ! empty($item['note']) ? ': '.$item['note'] : '' }} |
@endforeach
@endcomponent
@endif
@endif
