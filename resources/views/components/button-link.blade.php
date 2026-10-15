<a {{ $attributes->merge(['href' => '/', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</a>
