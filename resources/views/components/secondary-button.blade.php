<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary text-xs uppercase tracking-[0.24em]']) }}>
    {{ $slot }}
</button>
