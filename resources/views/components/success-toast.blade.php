<div class="sr-only" aria-live="polite" role="status">{{ session('status') }}</div>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Toastify({
            text: @json(session('status')),
            duration: 4500,
            gravity: 'top',
            position: 'right',
            close: true,
            stopOnFocus: true,
            escapeMarkup: true,
            style: {
                background: '#047857',
                borderRadius: '0.75rem',
                boxShadow: '0 12px 28px rgba(6, 78, 59, 0.25)',
                direction: 'rtl',
                fontFamily: 'Tajawal, ui-sans-serif, system-ui, sans-serif',
                textAlign: 'right',
            },
        }).showToast();
    });
</script>
