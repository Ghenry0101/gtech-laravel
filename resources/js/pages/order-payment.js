const paymentSection = document.querySelector('[data-order-payment]');

if (paymentSection) {
    const feedbackBox = paymentSection.querySelector('[data-order-payment-feedback]');

    const showFeedback = (message, isError = false) => {
        if (!feedbackBox) {
            return;
        }

        if (!message) {
            feedbackBox.classList.add('hidden');
            feedbackBox.textContent = '';
            return;
        }

        feedbackBox.textContent = message;
        feedbackBox.classList.toggle('text-emerald-700', !isError);
        feedbackBox.classList.toggle('text-rose-700', isError);
        feedbackBox.classList.remove('hidden');
    };

    const handleCopy = async (value) => {
        if (!value) {
            showFeedback('Tidak ada data yang bisa disalin.', true);
            return;
        }

        if (!navigator.clipboard) {
            showFeedback('Browser tidak mendukung salin otomatis.', true);
            return;
        }

        try {
            await navigator.clipboard.writeText(value);
            showFeedback('Berhasil disalin ke clipboard.');
        } catch (error) {
            showFeedback('Gagal menyalin, coba manual.', true);
        }
    };

    paymentSection.querySelectorAll('[data-copy-value]').forEach((button) => {
        const value = button.dataset.copyValue;

        button.addEventListener('click', () => handleCopy(value));
    });

    paymentSection.querySelectorAll('[data-open-payment-link]').forEach((button) => {
        button.addEventListener('click', () => {
            const url = button.dataset.openPaymentLink;

            if (!url) {
                showFeedback('Link pembayaran belum tersedia.', true);
                return;
            }

            window.open(url, '_blank', 'noopener');
            showFeedback('Link pembayaran dibuka di tab baru.');
        });
    });
}
