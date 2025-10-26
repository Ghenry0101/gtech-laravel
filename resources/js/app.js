import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('searchToggle');
  const bar = document.getElementById('searchBar');
  const input = document.getElementById('searchInput');

  if (!toggle || !bar) return;

  const hide = () => {
    if (!bar.classList.contains('hidden')) {
      bar.classList.add('hidden');
      toggle.setAttribute('aria-expanded', 'false');
    }
  };

  const show = () => {
    bar.classList.remove('hidden');
    toggle.setAttribute('aria-expanded', 'true');
    setTimeout(() => input?.focus(), 10);
  };

  toggle.addEventListener('click', () => {
    if (bar.classList.contains('hidden')) show();
    else hide();
  });

  document.addEventListener('click', (e) => {
    if (!bar.classList.contains('hidden')) {
      if (!bar.contains(e.target) && !toggle.contains(e.target)) hide();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hide();
  });
});

import.meta.glob([
  '../fonts/**',
]);
