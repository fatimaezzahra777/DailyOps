<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#e8007d] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-[0_2px_14px_rgba(232,0,125,0.3)] hover:bg-[#ff1a8c] focus:bg-[#ff1a8c] active:bg-[#a0005a] focus:outline-none focus:ring-2 focus:ring-[#e8007d] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
