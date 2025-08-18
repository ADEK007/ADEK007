document.addEventListener('DOMContentLoaded', () => {
	const yearSpan = document.getElementById('year');
	if (yearSpan) yearSpan.textContent = new Date().getFullYear();

	const refreshButton = document.getElementById('refresh-gallery');
	if (refreshButton) {
		refreshButton.addEventListener('click', () => loadGallery(true));
	}

	loadGallery(false);
});

async function loadGallery(forceReload) {
	const grid = document.getElementById('gallery-grid');
	const status = document.getElementById('gallery-status');
	if (!grid) return;

	status.textContent = 'Loading images…';

	try {
		const cacheBuster = forceReload ? `?t=${Date.now()}` : '';
		const res = await fetch(`/api/images.php${cacheBuster}`, { headers: { 'Accept': 'application/json' } });
		if (!res.ok) throw new Error(`HTTP ${res.status}`);
		const data = await res.json();

		grid.innerHTML = '';
		if (!Array.isArray(data) || data.length === 0) {
			status.textContent = 'No images yet. The gallery will populate once the backend seeds the database.';
			return;
		}

		for (const item of data) {
			const card = document.createElement('article');
			card.className = 'gallery-item';
			card.innerHTML = `
				<img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.title || 'Astronomy image')}" loading="lazy" />
				<div class="meta">${escapeHtml(item.title || '')}</div>
			`;
			grid.appendChild(card);
		}

		status.textContent = `${data.length} images loaded.`;
	} catch (err) {
		status.textContent = `Failed to load images: ${err.message}`;
	}
}

function escapeHtml(str) {
	if (typeof str !== 'string') return '';
	return str
		.replaceAll('&', '&amp;')
		.replaceAll('<', '&lt;')
		.replaceAll('>', '&gt;')
		.replaceAll('"', '&quot;')
		.replaceAll("'", '&#039;');
}

