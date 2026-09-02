<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $post->title }}</title>
    <style>
        body { background: #0a0e17; color: rgba(255,255,255,.8); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.75; margin: 0; padding: 0; }
        .banner { background: #fcbd17; color: #0a0e17; text-align: center; padding: 10px; font-weight: 700; font-size: 13px; letter-spacing: .05em; text-transform: uppercase; }
        .wrap { max-width: 720px; margin: 0 auto; padding: 40px 24px 100px; }
        .meta { font-family: monospace; font-size: 12px; color: rgba(255,255,255,.4); }
        h1 { font-size: 2.5rem; font-weight: 600; color: #fff; margin: 12px 0 24px; }
        .body h2 { font-size: 1.5rem; font-weight: 600; color: #fff; margin-top: 1.5rem; }
        .body h3 { font-size: 1.2rem; font-weight: 600; color: #fff; margin-top: 1.25rem; }
        .body h4, .body h5, .body h6 { font-weight: 600; color: #fff; margin-top: 1rem; }
        .body p { margin: 1rem 0; }
        .body a { color: #7dd3c0; }
        .body img { max-width: 100%; border-radius: 8px; }
        .body table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: .9rem; }
        .body th, .body td { border: 1px solid rgba(255,255,255,.15); padding: 8px 12px; text-align: left; }
        .body th { background: rgba(255,255,255,.06); color: #fff; }
        .cover { width: 100%; border-radius: 12px; margin: 24px 0; }
        .faq { margin-top: 40px; }
        .faq-item { border: 1px solid rgba(255,255,255,.15); border-radius: 10px; padding: 14px 18px; margin-bottom: 12px; }
        .faq-item summary { cursor: pointer; font-weight: 600; color: #fff; }
        .faq-item p { margin: 10px 0 0; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status.draft { background: rgba(255,255,255,.1); color: rgba(255,255,255,.6); }
        .status.published { background: rgba(34,197,94,.15); color: #4ade80; }
    </style>
</head>
<body>
    <div class="banner">Preview only — this is not the live page. Layout is an approximation of the real Astro site.</div>
    <div class="wrap">
        <p class="meta">
            {{ $post->author }} &middot; {{ $post->pub_date->format('D, d M Y g:i A') }}
            &middot; <span class="status {{ $post->status }}">{{ ucfirst($post->status) }}</span>
        </p>
        <h1>{{ $post->title }}</h1>
        @if ($post->image)
            <img class="cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->image) }}" alt="{{ $post->image_alt }}">
        @endif
        <div class="body">{!! $post->body !!}</div>

        @if ($post->video_url)
            <p><em>[Video embed: {{ $post->video_url }}]</em></p>
        @endif

        @if (!empty($post->faqs))
            <div class="faq">
                <h2>Frequently Asked Questions</h2>
                @foreach ($post->faqs as $faq)
                    <details class="faq-item">
                        <summary>{{ $faq['question'] ?? '' }}</summary>
                        <p>{{ $faq['answer'] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
