{{-- Hero block. Renders the shared services-hero partial with the editable prose fields. --}}
@include('partials.services-hero', [
  'eyebrow' => $data['eyebrow'] ?? null,
  'title' => $data['title'] ?? null,
  'lede' => $data['lede'] ?? null,
])
