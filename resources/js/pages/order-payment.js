const paymentSection = document.querySelector('[data-order-payment]');

if (paymentSection) {
    const snapToken = paymentSection.dataset.snapToken;
    const redirectUrl = paymentSection.dataset.redirect || window.location.href;
    const triggerButton = paymentSection.querySelector('[data-order-pay-trigger]');
    const errorBox = paymentSection.querySelector('[data-order-payment-error]');

    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        if (!message) {
            errorBox.classList.add('hidden');
            errorBox.textContent = '';
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const redirect = () => {
        window.location.href = redirectUrl;
    };

    const triggerSnapPayment = () => {
        showError('');

        if (!snapToken) {
            showError('Token pembayaran tidak tersedia. Hubungi tim kami untuk bantuan.');
            return;
        }

        if (!window.snap) {
            showError('Script Midtrans belum termuat. Tunggu sebentar lalu coba lagi.');
            return;
        }

        window.snap.pay(snapToken, {
            onSuccess: redirect,
            onPending: redirect,
            onError: (error) => {
                showError(error?.message || 'Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: () => {
                showError('Jendela pembayaran ditutup sebelum selesai.');
            },
        });
    };

    triggerButton?.addEventListener('click', triggerSnapPayment);
}
