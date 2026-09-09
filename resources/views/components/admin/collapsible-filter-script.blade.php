@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-admin-filter-toggle]').forEach((toggle) => {
                    const panelId = toggle.getAttribute('aria-controls');
                    const panel = panelId ? document.getElementById(panelId) : null;
                    const chevron = toggle.querySelector('[data-filter-chevron]');

                    if (!panel) {
                        return;
                    }

                    toggle.addEventListener('click', () => {
                        const isOpen = !panel.classList.contains('hidden');
                        panel.classList.toggle('hidden', isOpen);
                        toggle.setAttribute('aria-expanded', (!isOpen).toString());
                        chevron?.classList.toggle('rotate-180', !isOpen);
                    });
                });
            });
        </script>
    @endpush
@endonce
