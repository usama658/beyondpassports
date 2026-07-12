{{-- Rich-text CMS block. Renders trusted editor HTML inside the site's content width. --}}
<section class="cms-block cms-rich-text"><div class="wrap">
    {!! $data['body'] ?? '' !!}
</div></section>
