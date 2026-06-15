{{-- Email-safe personalised document checklist.
     Expects $items in the RequirementService shape:
       list<array{document_key, label, note, category, mandatory}>
     Inline-styled only (no site CSS classes) so it renders in email clients.
     This is the EMAIL version — do NOT reuse the web partial (resources/views/partials),
     which relies on site stylesheets. Renders nothing when $items is empty. --}}
@php
    $items = $items ?? [];
    $required = array_values(array_filter($items, static fn ($i) => ! empty($i['mandatory'])));
    $recommended = array_values(array_filter($items, static fn ($i) => empty($i['mandatory'])));
@endphp

@if (! empty($items))
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:24px 0;border-collapse:collapse;">
    <tr>
        <td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;color:#1f2933;">
            Your document checklist
        </td>
    </tr>

    @foreach ([['title' => 'Required', 'rows' => $required], ['title' => 'Recommended', 'rows' => $recommended]] as $group)
        @if (! empty($group['rows']))
        <tr>
            <td style="padding:12px 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.04em;color:#52606d;">
                {{ $group['title'] }}
            </td>
        </tr>
        <tr>
            <td style="padding:0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
                    @foreach ($group['rows'] as $item)
                    <tr>
                        <td valign="top" style="padding:6px 8px 6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.2;color:#1f2933;width:18px;">
                            &bull;
                        </td>
                        <td valign="top" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#1f2933;">
                            <span style="font-weight:bold;">{{ $item['label'] }}</span>
                            @if (! empty($item['note']))
                                <br><span style="font-size:13px;color:#52606d;">{{ $item['note'] }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif
    @endforeach
</table>
@endif
