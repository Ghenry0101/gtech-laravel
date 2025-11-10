import axios from 'axios';

const areaPickers = document.querySelectorAll('[data-biteship-area-picker]');

const formatAreaLabel = (area) => {
    if (!area) {
        return '';
    }

    const parts = [area.district, area.city, area.province]
        .filter(Boolean)
        .join(', ');

    return `${parts}${area.postal_code ? ` ${area.postal_code}` : ''}`;
};

areaPickers.forEach((root) => {
    const endpoint = root.dataset.endpoint;
    if (!endpoint) {
        return;
    }

    const form = root.closest('form') ?? document;
    const input = root.querySelector('[data-area-input]');
    const resultsBox = root.querySelector('[data-area-results]');
    const hiddenId = root.querySelector('[data-area-id]');
    const districtInput = form.querySelector('[data-area-district]');
    const cityInput = form.querySelector('[data-area-city]');
    const provinceInput = form.querySelector('[data-area-province]');
    const postalInput = form.querySelector('[data-area-postal]');
    if (!input || !resultsBox || !hiddenId) {
        return;
    }

    let typingTimeout = null;
    let lastQuery = '';

    const hideResults = () => {
        resultsBox.classList.add('hidden');
    };

    const showResults = () => {
        resultsBox.classList.remove('hidden');
    };

    const clearDependentFields = () => {
        hiddenId.value = '';
        if (districtInput) {
            districtInput.value = '';
        }
        if (cityInput) {
            cityInput.value = '';
        }
        if (provinceInput) {
            provinceInput.value = '';
        }
        if (postalInput) {
            postalInput.value = '';
        }
    };

    const applyArea = (area) => {
        if (!area) {
            return;
        }

        hiddenId.value = area.id ?? '';

        if (districtInput) {
            districtInput.value = area.district ?? '';
        }
        if (cityInput) {
            cityInput.value = area.city ?? '';
        }
        if (provinceInput) {
            provinceInput.value = area.province ?? '';
        }
        if (postalInput) {
            postalInput.value = area.postal_code ?? '';
        }

        input.value = formatAreaLabel(area);
        hideResults();
    };

    const renderResults = (areas = []) => {
        resultsBox.innerHTML = '';

        if (!areas.length) {
            resultsBox.innerHTML = '<p class="px-3 py-2 text-sm text-gray-500">Wilayah tidak ditemukan.</p>';
            showResults();
            return;
        }

        areas.forEach((area) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex w-full flex-col gap-0.5 px-3 py-2 text-left hover:bg-gray-50';

            const title = document.createElement('span');
            title.className = 'text-sm font-semibold text-gray-800';
            title.textContent = `${area.district}, ${area.city}`;

            const subtitle = document.createElement('span');
            subtitle.className = 'text-xs text-gray-500';
            subtitle.textContent = `${area.province} • ${area.postal_code ?? 'Tanpa kode pos'}`;

            button.appendChild(title);
            button.appendChild(subtitle);

            button.addEventListener('click', () => {
                applyArea(area);
            });

            resultsBox.appendChild(button);
        });

        showResults();
    };

    const searchAreas = async (query) => {
        if (query.length < 3) {
            hideResults();
            return;
        }

        if (query === lastQuery) {
            return;
        }

        lastQuery = query;
        resultsBox.innerHTML = '<p class="px-3 py-2 text-sm text-gray-500">Mencari wilayah...</p>';
        showResults();

        try {
            const response = await axios.get(endpoint, {
                params: {
                    q: query,
                },
            });

            renderResults(response.data?.data ?? []);
        } catch (error) {
            resultsBox.innerHTML = '<p class="px-3 py-2 text-sm text-rose-600">Gagal mencari wilayah. Coba lagi.</p>';
            showResults();
        }
    };

    input.addEventListener('input', () => {
        clearTimeout(typingTimeout);
        clearDependentFields();

        const value = input.value.trim();
        if (value.length < 3) {
            hideResults();
            return;
        }

        typingTimeout = setTimeout(() => {
            searchAreas(value);
        }, 350);
    });

    input.addEventListener('focus', () => {
        if (resultsBox.innerHTML.trim() !== '') {
            showResults();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            hideResults();
        }
    });

    if (hiddenId.value) {
        // Ensure readonly fields keep synced label on load.
        const currentLabel = input.value.trim();
        if (!currentLabel && districtInput && cityInput && provinceInput) {
            input.value = formatAreaLabel({
                district: districtInput.value,
                city: cityInput.value,
                province: provinceInput.value,
                postal_code: postalInput?.value,
            });
        }
    }
});
