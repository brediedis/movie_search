@if ($poster)
    <img src="{{ $poster }}" alt="{{ $title }} plakāts" class="poster" loading="lazy">
@else
    <div class="poster poster--empty" role="img" aria-label="Plakāts nav pieejams">
        <span>Nav plakāta</span>
    </div>
@endif
