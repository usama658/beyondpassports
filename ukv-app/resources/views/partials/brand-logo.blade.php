{{-- Beyond Passports lockup — horizon-tile mark + wordmark.
     Pass ['dark' => true] for navy backgrounds (footer/hero). Mark colours:
     sun = terracotta #C75D38; horizon = sage #5C9A7B (light) / peach #F2C2AC (dark). --}}
@php $dark = $dark ?? false; @endphp
<svg class="bp-mark" width="32" height="32" viewBox="0 0 52 52" fill="none" aria-hidden="true" focusable="false">
  <rect x="1.5" y="1.5" width="49" height="49" rx="13" stroke="{{ $dark ? 'rgba(255,255,255,.30)' : '#e6e8ea' }}" stroke-width="2"/>
  <circle cx="33" cy="24" r="8" fill="#C75D38"/>
  <path d="M9 33h34" stroke="{{ $dark ? '#F2C2AC' : '#5C9A7B' }}" stroke-width="2.6" stroke-linecap="round"/>
  <path d="M14 39h24" stroke="{{ $dark ? '#F2C2AC' : '#5C9A7B' }}" stroke-width="2.6" stroke-linecap="round" opacity=".5"/>
</svg>Beyond <b>Passports</b>
