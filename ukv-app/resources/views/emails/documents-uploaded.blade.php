@component('mail::message')
# Documents uploaded

A customer uploaded {{ $count }} {{ $count === 1 ? 'document' : 'documents' }} to their application.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
| Reference | {{ $orderRef }} |
| Destination | {{ $destination }} |
| Files | {{ $count }} |
@endcomponent

@if (! empty($fileNames))
**Uploaded files**
@component('mail::table')
| File |
| :--- |
@foreach ($fileNames as $name)
| {{ $name }} |
@endforeach
@endcomponent
@endif

Review them in the admin panel and move the order forward when ready.

Internal operations email — not for customers.
@endcomponent
