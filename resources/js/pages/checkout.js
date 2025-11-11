import axios from 'axios';

const root = document.querySelector('[data-checkout-root]');

if (root) {
    const addressInputs = root.querySelectorAll('[data-address-option]');
    const shippingContainer = root.querySelector('[data-shipping-options]');
    const shippingEmpty = root.querySelector('[data-empty-shipping]');
    const shippingError = root.querySelector('[data-shipping-error]');
    const refreshShippingButton = root.querySelector('[data-refresh-shipping]');
    const summarySubtotal = root.querySelector('[data-summary-subtotal]');
    const summaryShipping = root.querySelector('[data-summary-shipping]');
    const summaryTotal = root.querySelector('[data-summary-total]');
    const checkoutButton = root.querySelector('[data-checkout-submit]');
    const generalErrorBox = root.querySelector('[data-checkout-error]');
    const notesField = root.querySelector('[data-field-notes]');
    const hiddenShippingCode = root.querySelector('[data-field-shipping-code]');
    const hiddenShippingService = root.querySelector('[data-field-shipping-service]');

    const endpoints = {
        shipping: root.dataset.shippingEndpoint,
        submit: root.dataset.submitEndpoint,
    };

    const successRedirectTemplate = root.dataset.successRedirect;

    const parseJsonAttr = (attribute) => {
        try {
            return JSON.parse(attribute || 'null') || null;
        } catch (error) {
            return null;
        }
    };

    const state = {
        summary: parseJsonAttr(root.dataset.summary) || { subtotal: 0 },
        shippingOptions: parseJsonAttr(root.dataset.initialShipping) || [],
        selectedShipping: null,
        loadingShipping: false,
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(value || 0);
    };

    const updateSummary = () => {
        const shippingCost = state.selectedShipping?.cost || 0;
        summarySubtotal.textContent = formatCurrency(state.summary.subtotal || 0);
        summaryShipping.textContent = formatCurrency(shippingCost);
        summaryTotal.textContent = formatCurrency((state.summary.subtotal || 0) + shippingCost);
    };

    const renderShippingOptions = () => {
        shippingContainer.innerHTML = '';
        shippingError.classList.add('hidden');

        if (!state.shippingOptions.length) {
            shippingEmpty.classList.remove('hidden');
            state.selectedShipping = null;
            updateSummary();
            return;
        }

        shippingEmpty.classList.add('hidden');

        state.shippingOptions.forEach((option, index) => {
            const label = document.createElement('label');
            label.className = 'flex cursor-pointer items-center gap-4 px-6 py-4 hover:bg-slate-50';

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'shipping_option';
            input.value = `${option.courier_code}:${option.courier_service_code}`;
            input.className = 'h-4 w-4 text-slate-900';
            input.checked = index === 0;
            input.dataset.courierCode = option.courier_code;
            input.dataset.serviceCode = option.courier_service_code;

            input.addEventListener('change', () => {
                state.selectedShipping = option;
                hiddenShippingCode.value = option.courier_code;
                hiddenShippingService.value = option.courier_service_code;
                updateSummary();
            });

            const info = document.createElement('div');
            info.className = 'flex-1';

            const title = document.createElement('p');
            title.className = 'text-sm font-semibold text-slate-900';
            title.textContent = `${option.courier_company} - ${option.courier_service_name}`;

            const desc = document.createElement('p');
            desc.className = 'text-xs text-slate-500';
            desc.textContent = option.estimation
                ? `${option.estimation} • ${option.courier_description || 'Service'}`
                : (option.courier_description || '');

            info.appendChild(title);
            info.appendChild(desc);

            const price = document.createElement('span');
            price.className = 'text-sm font-semibold text-slate-900';
            price.textContent = formatCurrency(option.cost);

            label.appendChild(input);
            label.appendChild(info);
            label.appendChild(price);

            shippingContainer.appendChild(label);

            if (index === 0) {
                state.selectedShipping = option;
                hiddenShippingCode.value = option.courier_code;
                hiddenShippingService.value = option.courier_service_code;
            }
        });

        updateSummary();
    };

    const getSelectedAddressId = () => {
        const checked = Array.from(addressInputs).find((input) => input.checked);
        return checked ? checked.value : null;
    };

    const getSelectedPaymentMethod = () => {
        const checked = root.querySelector('input[name="payment_method"]:checked');
        return checked ? checked.value : null;
    };

    const handleAxiosError = (error) => {
        if (error.response?.data?.message) {
            return error.response.data.message;
        }

        return 'Terjadi kesalahan pada server. Coba ulangi.';
    };

    const fetchShippingRates = async () => {
        const addressId = getSelectedAddressId();

        if (!addressId || !endpoints.shipping) {
            return;
        }

        state.loadingShipping = true;
        shippingEmpty.classList.remove('hidden');
        shippingEmpty.textContent = 'Menghitung ongkir...';
        shippingContainer.innerHTML = '';
        shippingError.classList.add('hidden');
        checkoutButton.disabled = true;

        try {
            const response = await axios.post(endpoints.shipping, {
                address_id: addressId,
            });

            state.shippingOptions = response.data.data || [];
            renderShippingOptions();
        } catch (error) {
            shippingError.textContent = handleAxiosError(error);
            shippingError.classList.remove('hidden');
            state.shippingOptions = [];
            renderShippingOptions();
        } finally {
            checkoutButton.disabled = false;
            state.loadingShipping = false;
        }
    };

    const showGeneralError = (message) => {
        if (!generalErrorBox) {
            return;
        }

        if (!message) {
            generalErrorBox.classList.add('hidden');
            generalErrorBox.textContent = '';
            return;
        }

        generalErrorBox.textContent = message;
        generalErrorBox.classList.remove('hidden');
    };

    const buildSuccessRedirectUrl = (result) => {
        if (!successRedirectTemplate) {
            return null;
        }

        const orderNumber = result?.order_id || result?.orderId || null;

        if (successRedirectTemplate.includes('__ORDER_NUMBER__')) {
            if (!orderNumber) {
                return successRedirectTemplate.replace('__ORDER_NUMBER__', '');
            }

            return successRedirectTemplate.replace('__ORDER_NUMBER__', orderNumber);
        }

        if (!orderNumber) {
            return successRedirectTemplate;
        }

        try {
            const url = new URL(successRedirectTemplate, window.location.origin);
            url.searchParams.set('order', orderNumber);

            return url.toString();
        } catch (error) {
            return successRedirectTemplate;
        }
    };

    const redirectAfterPayment = (result) => {
        const successUrl = buildSuccessRedirectUrl(result);

        if (successUrl) {
            window.location.href = successUrl;
            return;
        }

        if (result?.finish_redirect_url) {
            window.location.href = result.finish_redirect_url;
            return;
        }

        window.location.reload();
    };

    const triggerSnapPayment = (snapToken) => {
        if (!window.snap || !snapToken) {
            showGeneralError('Script Midtrans belum termuat. Reload halaman dan coba lagi.');
            return;
        }

        window.snap.pay(snapToken, {
            onSuccess: redirectAfterPayment,
            onPending: redirectAfterPayment,
            onError: (error) => {
                showGeneralError(error.message || 'Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: () => {
                showGeneralError('Anda menutup jendela pembayaran sebelum selesai.');
            },
        });
    };

    const submitCheckout = async () => {
        const payload = {
            address_id: getSelectedAddressId(),
            payment_method: getSelectedPaymentMethod(),
            shipping_courier_code: hiddenShippingCode.value,
            shipping_service_code: hiddenShippingService.value,
            notes: notesField?.value,
        };

        if (!payload.address_id) {
            showGeneralError('Pilih alamat pengiriman terlebih dahulu.');
            return;
        }

        if (!payload.shipping_courier_code || !payload.shipping_service_code) {
            showGeneralError('Pilih ekspedisi pengiriman terlebih dahulu.');
            return;
        }

        if (!payload.payment_method) {
            showGeneralError('Pilih metode pembayaran.');
            return;
        }

        showGeneralError('');
        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Memproses...';

        try {
            const response = await axios.post(endpoints.submit, payload);

            triggerSnapPayment(response.data.snap_token);
        } catch (error) {
            showGeneralError(handleAxiosError(error));
        } finally {
            checkoutButton.disabled = false;
            checkoutButton.textContent = 'Bayar Sekarang';
        }
    };

    addressInputs.forEach((input) => {
        input.addEventListener('change', () => fetchShippingRates());
    });

    refreshShippingButton?.addEventListener('click', () => fetchShippingRates());
    checkoutButton?.addEventListener('click', () => submitCheckout());

    renderShippingOptions();
}
