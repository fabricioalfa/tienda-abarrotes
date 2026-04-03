<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full bg-rose-600 px-5 py-3 text-xs font-semibold uppercase tracking-[0.24em] text-white shadow-[0_12px_30px_-16px_rgba(225,29,72,0.9)] transition hover:-translate-y-0.5 hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
