@props(['title'])

@if (is_array($title))
    @foreach ($title as $line)
        {{ $line }}<br>
    @endforeach
@else
    {{ $title }}
@endif
