document.addEventListener('DOMContentLoaded', function () {
    const section = document.getElementById('inventory');
    if (!section) return;

    const apiBase = (window.baseAppUrl || '/');
    const brandSections = document.getElementById('brand-sections');
    const searchResults = document.getElementById('search-results');
    const searchGrid = document.getElementById('search-grid');
    const searchEmpty = document.getElementById('search-empty');
    const searchLoadMore = document.getElementById('search-load-more');
    const searchInput = document.getElementById('car-search');

    const whatsappNum = (section.dataset.whatsapp || '').replace(/[^0-9]/g, '');
    const waTemplateEl = document.getElementById('wa-message-template');
    let waTemplate = 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?';
    if (waTemplateEl) {
        try { waTemplate = JSON.parse(waTemplateEl.textContent); } catch (e) {}
    }
    if (!waTemplate || !String(waTemplate).trim()) {
        waTemplate = 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?';
    }

    let searchNextCursor = null;
    let searchQuery = '';
    let searchLoading = false;
    let debounceTimer = null;

    var brandPage = {};

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
            '{nombre}': car.title || '', '{titulo}': car.title || '', '{title}': car.title || '',
            '{precio}': priceFormatted, '{price}': priceFormatted,
            '{descripcion}': description, '{description}': description,
            '{id}': car.id != null ? String(car.id) : '',
        };
        let message = waTemplate;
        Object.keys(replacements).forEach(function (key) { message = message.split(key).join(replacements[key]); });
        return message;
    }

    function buildWhatsAppLink(car) {
        if (!whatsappNum) return '#';
        const msg = encodeURIComponent(applyMessageTemplate(car));
        return 'https://wa.me/' + whatsappNum + '?text=' + msg;
    }

    function createCarCard(car) {
        const card = document.createElement('div');
        card.className = 'car-card';
        const title = escapeHtml(car.title);
        const img = escapeHtml(car.image_path);
        const price = formatPrice(car.price);
        const carLink = (window.baseAppUrl || '/') + 'auto/' + (car.slug || car.id);

        card.innerHTML =
            '<a href="' + carLink + '" class="car-img-wrapper">' +
                '<img src="' + img + '" alt="' + title + '">' +
            '</a>' +
            '<div class="car-price">' + price + '</div>' +
            '<div class="car-info">' +
                '<h3><a href="' + carLink + '">' + title + '</a></h3>' +
                '<a href="' + carLink + '" class="btn btn-details">VER DETALLES</a>' +
            '</div>';
        return card;
    }

    function getAssetUrl(path) {
        if (!path) return '';
        const base = window.baseAppUrl || '/';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return base + path;
    }

    function loadBrandSections() {
        brandPage = {};
        fetchBrandPage(null);
    }

    function fetchBrandPage(brandId) {
        var limit = 2;
        var offset = 0;
        if (brandId !== null) {
            offset = (brandPage[brandId] || 0) * limit;
        }

        fetch(apiBase + 'api/home_brands.php?limit=' + limit + '&offset=' + offset)
            .then(res => res.json())
            .then(data => {
                var brands = data.brands || [];
                if (brands.length === 0) {
                    if (!brandId) brandSections.innerHTML = '<p style="text-align:center; color:#64748b; padding:40px 0;">No hay marcas disponibles.</p>';
                    return;
                }

                if (brandId === null) {
                    brandSections.innerHTML = '';
                }

                brands.forEach(function(brand) {
                    var existing = brandId === null ? null : document.getElementById('brand-section-' + brand.id);
                    var sectionEl, gridEl, loadMoreBtn;

                    if (existing) {
                        sectionEl = existing;
                        gridEl = document.getElementById('brand-grid-' + brand.id);
                        loadMoreBtn = document.getElementById('brand-more-' + brand.id);
                    } else {
                        sectionEl = document.createElement('div');
                        sectionEl.className = 'brand-section';
                        sectionEl.id = 'brand-section-' + brand.id;

                        var header = document.createElement('div');
                        header.className = 'brand-section-header';
                        header.innerHTML = '<h2>' + escapeHtml(brand.nombre) + '</h2>';
                        if (brand.logo) {
                            header.innerHTML += '<img src="' + escapeHtml(getAssetUrl(brand.logo)) + '" alt="' + escapeHtml(brand.nombre) + '" class="brand-logo">';
                        }
                        sectionEl.appendChild(header);

                        gridEl = document.createElement('div');
                        gridEl.className = 'cars-grid grid-2x2';
                        gridEl.id = 'brand-grid-' + brand.id;
                        sectionEl.appendChild(gridEl);

                        loadMoreBtn = document.createElement('button');
                        loadMoreBtn.type = 'button';
                        loadMoreBtn.className = 'btn btn-primary cars-load-more';
                        loadMoreBtn.id = 'brand-more-' + brand.id;
                        loadMoreBtn.textContent = 'Cargar más';
                        loadMoreBtn.style.marginTop = '20px';
                        loadMoreBtn.addEventListener('click', function() { loadMoreForBrand(brand.id); });

                        var wrap = document.createElement('div');
                        wrap.className = 'cars-load-more-wrap';
                        wrap.appendChild(loadMoreBtn);
                        sectionEl.appendChild(wrap);

                        brandSections.appendChild(sectionEl);
                    }

                    brandPage[brand.id] = (brandPage[brand.id] || 0) + 1;

                    brand.cars.forEach(function(car) {
                        gridEl.appendChild(createCarCard(car));
                    });

                    loadMoreBtn.hidden = !brand.has_more;
                    if (brand.total !== undefined) {
                        var countEl = document.getElementById('brand-count-' + brand.id);
                        if (!countEl) {
                            var header = sectionEl.querySelector('.brand-section-header');
                            var countSpan = document.createElement('span');
                            countSpan.id = 'brand-count-' + brand.id;
                            countSpan.style.cssText = 'margin-left:auto;font-size:0.85rem;color:#64748b;';
                            countSpan.textContent = brand.total + ' auto' + (brand.total !== 1 ? 's' : '');
                            header.appendChild(countSpan);
                        }
                    }
                });
            })
            .catch(function(err) {
                if (!brandId) brandSections.innerHTML = '<p style="text-align:center; color:#dc2626; padding:40px 0;">Error al cargar las marcas.</p>';
                console.error(err);
            });
    }

    function loadMoreForBrand(brandId) {
        fetchBrandPage(brandId);
    }

    // Search functionality
    async function loadSearchCars({ cursor = null, q = '', append = false } = {}) {
        if (searchLoading) return;
        searchLoading = true;

        if (searchLoadMore) {
            searchLoadMore.disabled = searchLoading;
            searchLoadMore.textContent = searchLoading ? 'Cargando...' : 'Cargar más';
        }

        const params = new URLSearchParams({ action: 'list', limit: '12' });
        if (cursor) params.set('cursor', String(cursor));
        if (q) params.set('q', q);

        try {
            const res = await fetch(apiBase + 'api/cars.php?' + params.toString());
            const data = await res.json();

            if (!res.ok || data.error) throw new Error(data.error || 'Error al cargar vehículos');

            if (!append && searchGrid) searchGrid.innerHTML = '';

            const items = data.items || [];
            items.forEach(function (car) {
                if (searchGrid) searchGrid.appendChild(createCarCard(car));
            });

            searchNextCursor = data.next_cursor;
            searchQuery = q;
            if (searchEmpty) searchEmpty.hidden = items.length > 0 || append;
            if (searchLoadMore) searchLoadMore.hidden = !data.has_more;
        } catch (err) {
            if (!append && searchGrid) searchGrid.innerHTML = '';
            if (searchEmpty) {
                searchEmpty.hidden = false;
                searchEmpty.textContent = err.message || 'No se pudieron cargar los vehículos.';
            }
            if (searchLoadMore) searchLoadMore.hidden = true;
        } finally {
            searchLoading = false;
            if (searchLoadMore) searchLoadMore.disabled = false;
        }
    }

    if (searchLoadMore) {
        searchLoadMore.addEventListener('click', function () {
            if (!searchNextCursor) return;
            loadSearchCars({ cursor: searchNextCursor, q: searchQuery, append: true });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const q = searchInput.value.trim();

            if (q === '') {
                brandSections.hidden = false;
                searchResults.hidden = true;
                return;
            }

            brandSections.hidden = true;
            searchResults.hidden = false;
            searchNextCursor = null;

            debounceTimer = setTimeout(function () {
                loadSearchCars({ q: searchInput.value.trim(), append: false });
            }, 300);
        });
    }

    if (searchLoadMore) searchLoadMore.hidden = true;
    if (searchResults) searchResults.hidden = true;

    loadBrandSections();
});
