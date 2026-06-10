<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#c50064] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-[0_2px_14px_rgba(197,0,100,0.3)] hover:bg-[#a90056] focus:bg-[#a90056] active:bg-[#850044] focus:outline-none focus:ring-2 focus:ring-[#c50064] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
