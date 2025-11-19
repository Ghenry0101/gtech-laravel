@once
    <script>
        (() => {
            const createFileKey = (file) => [file.name, file.lastModified, file.size].join(':');

            const renderPreviews = (input) => {
                const form = input.closest('[data-review-form]');
                if (!form) return;

                const previewContainer = form.querySelector('[data-photo-previews]');
                if (!previewContainer) return;

                previewContainer.querySelectorAll('[data-object-url]').forEach((node) => {
                    const url = node.getAttribute('data-object-url');
                    if (url) {
                        URL.revokeObjectURL(url);
                    }
                });

                previewContainer.innerHTML = '';

                Array.from(input.files || []).forEach((file) => {
                    const key = createFileKey(file);
                    const objectUrl = URL.createObjectURL(file);

                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative';
                    wrapper.setAttribute('data-photo-item', 'true');
                    wrapper.setAttribute('data-photo-key', key);
                    wrapper.setAttribute('data-object-url', objectUrl);

                    const previewButton = document.createElement('button');
                    previewButton.type = 'button';
                    previewButton.className = 'block h-20 w-20 overflow-hidden rounded-xl border border-slate-200 bg-white';
                    previewButton.setAttribute('data-image-preview', objectUrl);

                    const img = document.createElement('img');
                    img.src = objectUrl;
                    img.alt = 'Review preview';
                    img.className = 'h-full w-full object-cover';
                    previewButton.appendChild(img);

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-rose-500 text-xs font-semibold text-white shadow-sm';
                    removeButton.setAttribute('data-remove-selected-photo', '');
                    removeButton.setAttribute('aria-label', 'Hapus foto ini');
                    removeButton.textContent = '\u00D7';

                    wrapper.appendChild(previewButton);
                    wrapper.appendChild(removeButton);
                    previewContainer.appendChild(wrapper);
                });
            };

            const removeSelectedPhoto = (button) => {
                const photoItem = button.closest('[data-photo-item]');
                if (!photoItem) return;

                const form = button.closest('[data-review-form]');
                const input = form?.querySelector('[data-review-photo-input]');
                if (!input) return;

                const targetKey = photoItem.getAttribute('data-photo-key');
                if (!targetKey || typeof DataTransfer === 'undefined') {
                    input.value = '';
                    photoItem.remove();
                    return;
                }

                const transfer = new DataTransfer();
                Array.from(input.files || []).forEach((file) => {
                    if (createFileKey(file) !== targetKey) {
                        transfer.items.add(file);
                    }
                });

                input.files = transfer.files;

                const url = photoItem.getAttribute('data-object-url');
                if (url) {
                    URL.revokeObjectURL(url);
                }

                photoItem.remove();
            };

            const removeExistingPhoto = (button) => {
                const photoItem = button.closest('[data-existing-photo]');
                if (!photoItem) return;

                const id = photoItem.getAttribute('data-photo-id');
                if (!id) return;

                const form = button.closest('[data-review-form]');
                const container = form?.querySelector('[data-removed-inputs]');
                if (!container) return;

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_images[]';
                input.value = id;
                container.appendChild(input);

                photoItem.remove();
            };

            const setupLightbox = () => {
                if (window.__reviewLightboxInitialized) {
                    return;
                }
                window.__reviewLightboxInitialized = true;

                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 z-[999] hidden items-center justify-center bg-slate-900/80 p-6';
                overlay.innerHTML = `
                    <div class="relative w-full max-w-3xl">
                        <button type="button" class="absolute -right-3 -top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-700 shadow-lg" data-image-modal-close aria-label="Tutup">
                            &times;
                        </button>
                        <div class="overflow-hidden rounded-2xl bg-white p-4">
                            <img src="" alt="Preview" class="max-h-[80vh] w-full object-contain" data-image-modal-target>
                        </div>
                    </div>
                `;

                document.body.appendChild(overlay);
                const imageEl = overlay.querySelector('[data-image-modal-target]');

                const hideOverlay = () => overlay.classList.add('hidden');

                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        hideOverlay();
                    }
                });

                overlay.querySelectorAll('[data-image-modal-close]').forEach((btn) => {
                    btn.addEventListener('click', hideOverlay);
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
                        hideOverlay();
                    }
                });

                document.addEventListener('click', (event) => {
                    const trigger = event.target.closest('[data-image-preview]');
                    if (!trigger) return;

                    const src = trigger.getAttribute('data-image-preview');
                    if (!src) return;
                    event.preventDefault();

                    imageEl.src = src;
                    overlay.classList.remove('hidden');
                });
            };

            document.addEventListener('change', (event) => {
                if (event.target.matches('[data-review-photo-input]')) {
                    renderPreviews(event.target);
                }
            });

            document.addEventListener('click', (event) => {
                const removeSelected = event.target.closest('[data-remove-selected-photo]');
                if (removeSelected) {
                    removeSelectedPhoto(removeSelected);
                    return;
                }

                const removeExisting = event.target.closest('[data-remove-existing-photo]');
                if (removeExisting) {
                    removeExistingPhoto(removeExisting);
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupLightbox);
            } else {
                setupLightbox();
            }
        })();
    </script>
@endonce
