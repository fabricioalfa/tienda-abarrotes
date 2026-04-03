@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'notice-success']) }}>
        {{ $status }}
    </div>
@endif
