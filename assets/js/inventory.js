document.addEventListener('DOMContentLoaded', function () {
    const section = document.getElementById('inventory');
    if (!section) return;

    const grid = document.getElementById('cars-grid');
    const searchInput = document.getElementById('car-search');
    const loadMoreBtn = document.getElementById('cars-load-more');
    const emptyEl = document.getElementById('cars-empty');

    const apiBase = (window.baseAppUrl || '/') + 'api/cars.php';
    const whatsappNum = (section.dataset.whatsapp || '').replace(/[^0-9]/g, '');
    const waTemplateEl = document.getElementById('wa-message-template');
    let waTemplate = 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?';
    if (waTemplateEl) {
        try {
            waTemplate = JSON.parse(waTemplateEl.textContent);
        } catch (e) { /* usa plantilla por defecto */ }
    }
    if (!waTemplate || !String(waTemplate).trim()) {
        waTemplate = 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?';
    }

    let nextCursor = null;
    let currentQuery = '';
    let loading = false;
    let debounceTimer = null;
    const emptyDefault = emptyEl ? emptyEl.textContent : '';

    function setLoading(isLoading) {
        loading = isLoading;
        if (loadMoreBtn) {
            loadMoreBtn.disabled = isLoading;
            loadMoreBtn.textContent = isLoading ? 'Cargando...' : 'Cargar más';
        }
    }

    function formatPrice(price) {
        const n = Number(price);
        if (Number.isNaN(n)) return '$0';
        return '$' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function escapeHtml(str) {
        const el = document.createElement('div');
        el.textContent = String(str ?? '');
        return el.innerHTML;
    }

    function applyMessageTemplate(car) {
        const priceFormatted = formatPrice(car.price);
        const description = car.description != null ? String(car.description) : '';
        const replacements = {
            '{nombre}': car.title || '',
            '{titulo}': car.title || '',
            '{title}': car.title || '',
            '{precio}': priceFormatted,
            '{price}': priceFormatted,
            '{descripcion}': description,
            '{description}': description,
            '{id}': car.id != null ? String(car.id) : '',
        };
        let message = waTemplate;
        Object.keys(replacements).forEach(function (key) {
            message = message.split(key).join(replacements[key]);
        });
        return message;
    }

    function buildWhatsAppLink(car) {
        if (!whatsappNum) return '#';
        const msg = encodeURIComponent(applyMessageTemplate(car));
        return 'https://wa.me/' + whatsappNum + '?text=' + msg;
    }

    function createCard(car) {
        const card = document.createElement('div');
        card.className = 'car-card';
        const title = escapeHtml(car.title);
        const img = escapeHtml(car.image_path);
        const price = formatPrice(car.price);
        const waLink = buildWhatsAppLink(car);
        const carLink = (window.baseAppUrl || '/') + 'auto/' + (car.slug || car.id);

        card.innerHTML =
            '<a href="' + carLink + '" class="car-img-wrapper">' +
                '<img src="' + img + '" alt="' + title + '">' +
            '</a>' +
            '<div class="car-price" style="position:absolute;top:15px;right:15px;background:linear-gradient(135deg,#f5f5f5,#c0c0c0);color:#111;padding:5px 15px;border-radius:4px;font-weight:800;font-size:1.1rem;box-shadow:0 4px 10px rgba(0,0,0,0.2);border:1px solid #fff;z-index:10;">' + price + '</div>' +
            '<div class="car-info">' +
                '<h3><a href="' + carLink + '">' + title + '</a></h3>' +
                '<a href="' + carLink + '" class="btn btn-primary" style="display:block;text-align:center;width:100%;background-color:#0B192C;color:white;padding:12px;border:none;border-radius:4px;font-weight:bold;text-transform:uppercase;cursor:pointer;text-decoration:none;">VER DETALLES</a>' +
            '</div>';

        return card;
    }

    function updateEmptyState(show) {
        if (emptyEl) emptyEl.hidden = !show;
    }

    function updateLoadMore(show) {
        if (loadMoreBtn) loadMoreBtn.hidden = !show;
    }

    async function loadCars({ cursor = null, q = '', append = false } = {}) {
        if (loading) return;
        setLoading(true);

        const params = new URLSearchParams({
            action: 'list',
            limit: '12',
        });
        if (cursor) params.set('cursor', String(cursor));
        if (q) params.set('q', q);

        try {
            const res = await fetch(apiBase + '?' + params.toString());
            const data = await res.json();

            if (!res.ok || data.error) {
                throw new Error(data.error || 'Error al cargar vehículos');
            }

            if (!append && grid) grid.innerHTML = '';

            const items = data.items || [];
            items.forEach(function (car) {
                if (grid) grid.appendChild(createCard(car));
            });

            nextCursor = data.next_cursor;
            currentQuery = q;
            if (emptyEl && emptyDefault) emptyEl.textContent = emptyDefault;
            updateEmptyState(items.length === 0 && !append);
            updateLoadMore(Boolean(data.has_more));
        } catch (err) {
            if (!append && grid) grid.innerHTML = '';
            updateEmptyState(true);
            if (emptyEl) {
                emptyEl.textContent = err.message || 'No se pudieron cargar los vehículos.';
            }
            updateLoadMore(false);
        } finally {
            setLoading(false);
        }
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            if (!nextCursor) return;
            loadCars({ cursor: nextCursor, q: currentQuery, append: true });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                const q = searchInput.value.trim();
                nextCursor = null;
                loadCars({ q: q, append: false });
            }, 300);
        });
    }

    loadCars({ append: false });
});
