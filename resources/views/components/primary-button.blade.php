<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary text-xs uppercase tracking-[0.24em]']) }}>
    {{ $slot }}
</button>
