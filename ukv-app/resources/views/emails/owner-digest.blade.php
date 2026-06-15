@component('mail::message')
# Daily owner digest

**{{ $counts['pending'] ?? 0 }}** order(s) need action.

- Manual review awaiting clearance: **{{ $counts['manual_review'] ?? 0 }}**
- SLA breached: **{{ $counts['sla_breached'] ?? 0 }}**
- Awaiting documents: **{{ $counts['awaiting_docs'] ?? 0 }}**

@php
    $titles = [
        'manual_review' => 'Manual review — awaiting eligibility clearance',
        'sla_breached'  => 'SLA breached',
        'awaiting_docs' => 'Awaiting documents',
    ];
@endphp

@foreach ($titles as $key => $title)
@php $rows = $sections[$key] ?? []; @endphp
@if (count($rows) > 0)
## {{ $title }} ({{ count($rows) }})

@component('mail::table')
| Order | Customer | Destination | Status | Detail |
| :---- | :------- | :---------- | :----- | :----- |
@foreach ($rows as $row)
| {{ $row['ref'] }} | {{ $row['name'] }} | {{ $row['destination'] }} | {{ $row['status'] }} | {{ $row['detail'] }} |
@endforeach
@endcomponent

@endif
@endforeach

@if (($counts['pending'] ?? 0) === 0)
_Nothing needs action right now — all open orders are clear._
@endif

Generated {{ $generatedAt }}.

Internal operations email — not for customers.
@endcomponent
