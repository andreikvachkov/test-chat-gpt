@php($asset = $getRecord())
<div class="kamali-media-preview">
    @if ($asset && str_starts_with($asset->mime_type ?? 'image/', 'image/'))
        <img src="{{ $asset->url }}" alt="{{ $asset->alt_text ?? '' }}" />
    @elseif ($asset && str_starts_with($asset->mime_type ?? '', 'video/'))
        <video src="{{ $asset->url }}" controls preload="metadata"></video>
    @elseif ($asset)
        <a href="{{ $asset->url }}" target="_blank" rel="noopener">Открыть {{ $asset->name }}</a>
    @endif
</div>
