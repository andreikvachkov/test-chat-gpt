@php($asset = $getRecord())
<div class="kamali-media-card" title="{{ $asset->name }}">
    <div class="kamali-media-card-preview">
        @if (str_starts_with($asset->mime_type ?? 'image/', 'image/'))
            <img src="{{ $asset->url }}" alt="{{ $asset->alt_text ?? '' }}" loading="lazy" />
        @elseif (str_starts_with($asset->mime_type ?? '', 'video/'))
            <span aria-label="Видео">▶</span>
        @else
            <span>{{ strtoupper(pathinfo($asset->path, PATHINFO_EXTENSION)) }}</span>
        @endif
    </div>
    <span class="kamali-media-card-name">{{ $asset->name }}</span>
</div>
