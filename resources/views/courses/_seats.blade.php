@php
    $occupied = $course->occupiedCount();
    $capacity = $course->capacity;
    $pct = $capacity > 0 ? min(100, round($occupied / $capacity * 100)) : 0;
@endphp
<div class="seats" title="Obsazeno {{ $occupied }} z {{ $capacity }}">
    @if ($capacity <= 30)
        <div class="seats__grid" aria-hidden="true">
            @for ($i = 1; $i <= $capacity; $i++)
                <span @class(['seat', 'seat--taken' => $i <= $occupied])></span>
            @endfor
        </div>
    @else
        <div class="seats__bar" aria-hidden="true"><span style="width: {{ $pct }}%"></span></div>
    @endif
    <span class="seats__text">
        {{ $occupied }} / {{ $capacity }}
        @if ($course->isFull())
            <strong class="tag tag--full">plno</strong>
        @else
            <span class="muted">volných {{ $course->freeSeats() }}</span>
        @endif
    </span>
</div>
