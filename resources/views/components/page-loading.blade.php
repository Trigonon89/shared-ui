<div
    x-data="{ open: false }"
    x-init="
        window.addEventListener('pageshow', e => { if (e.persisted) open = false });

        const navTarget = (target) => {
            const a = target.closest('a[href]');
            if (!a) return null;
            if (a.target === '_blank') return null;
            if (a.hasAttribute('download')) return null;
            const href = a.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return null;
            try {
                const url = new URL(href, window.location.href);
                if (url.origin !== window.location.origin) return null;
            } catch(e) { return null; }
            return a;
        };

        document.addEventListener('click', e => {
            if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            if (navTarget(e.target)) open = true;
        });
    "
    @submit.window="if (!$event.defaultPrevented) open = true"
    @page-loading.window="open = true"
>
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl flex flex-col items-center justify-center gap-4 p-8"
             style="width: 300px; height: 200px;">
            <svg class="animate-spin h-10 w-10 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Processing...</span>
        </div>
    </div>
</div>
