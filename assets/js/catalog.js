(function() {
    'use strict';

    const baseUrl = window.baseAppUrl || '/';
    let currentPage = 1;
    const limit = 12;
    let specFields = [];
    let currentFilters = {};
    let allMarcas = [];

    function init() {
        loadMarcas();
        loadSpecFields();
        bindEvents();
        loadCatalog();
    }

    function loadMarcas() {
        fetch(baseUrl + 'api/marcas.php?action=list')
            .then(res => res.json())
            .then(data => {
                allMarcas = data.marcas || [];
                renderMarcaFilter();
                applyUrlParams();
            })
            .catch(() => {});
    }

    function renderMarcaFilter() {
        const select = document.getElementById('filter-marca');
        if (!select) return;

        select.innerHTML = '<option value="">Todas</option>';
        allMarcas.forEach(marca => {
            const opt = document.createElement('option');
            opt.value = marca.id;
            opt.textContent = marca.nombre;
            select.appendChild(opt);
        });
    }

    function loadModelosForMarca(marcaId) {
        const select = document.getElementById('filter-modelo');
        if (!select) return;

        if (!marcaId) {
            select.innerHTML = '<option value="">Selecciona una marca primero</option>';
            select.disabled = true;
            return;
        }

        select.disabled = true;
        select.innerHTML = '<option value="">Cargando...</option>';

        fetch(baseUrl + `api/marcas.php?action=get_models&marca_id=${marcaId}`)
            .then(res => res.json())
            .then(data => {
                const modelos = data.modelos || [];
                select.innerHTML = '<option value="">Todos</option>';
                modelos.forEach(modelo => {
                    const opt = document.createElement('option');
                    opt.value = modelo;
                    opt.textContent = modelo;
                    select.appendChild(opt);
                });
                select.disabled = false;
            })
            .catch(() => {
                select.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    function applyUrlParams() {
        const params = new URLSearchParams(window.location.search);
        const marcaSlug = params.get('marca');
        const modeloParam = params.get('modelo');

        if (marcaSlug && allMarcas.length > 0) {
            const marca = allMarcas.find(m => m.slug === marcaSlug);
            if (marca) {
                document.getElementById('filter-marca').value = marca.id;
                loadModelosForMarca(marca.id);
                if (modeloParam) {
                    setTimeout(() => {
                        document.getElementById('filter-modelo').value = modeloParam;
                        currentPage = 1;
                        loadCatalog();
                    }, 300);
                } else {
                    currentPage = 1;
                    loadCatalog();
                }
            }
        }
    }

    function loadSpecFields() {
        fetch(baseUrl + 'api/spec_fields.php?action=list')
            .then(res => res.json())
            .then(data => {
                specFields = data.fields || [];
                renderSpecFilters();
            })
            .catch(() => {});
    }

    function renderSpecFilters() {
        const container = document.getElementById('spec-filters-container');
        if (!container || specFields.length === 0) return;

        container.innerHTML = specFields.map(f => `
            <div class="filter-group">
                <label>${f.nombre}</label>
                ${f.tipo === 'select' && f.opciones ? `
                    <select data-spec="${f.slug}">
                        <option value="">Todos</option>
                        ${JSON.parse(f.opciones).map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                    </select>
                ` : `
                    <input type="text" data-spec="${f.slug}" placeholder="Cualquier ${f.nombre.toLowerCase()}">
                `}
            </div>
        `).join('');
    }

    function bindEvents() {
        document.getElementById('btn-apply-filters').addEventListener('click', () => {
            currentPage = 1;
            loadCatalog();
        });

        document.getElementById('btn-clear-filters').addEventListener('click', clearFilters);

        document.getElementById('btn-prev-page').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadCatalog();
            }
        });

        document.getElementById('btn-next-page').addEventListener('click', () => {
            currentPage++;
            loadCatalog();
        });

        document.getElementById('mobile-filter-toggle').addEventListener('click', () => {
            document.getElementById('catalog-filters').classList.toggle('show');
        });

        document.getElementById('filter-marca').addEventListener('change', function() {
            const marcaId = this.value;
            document.getElementById('filter-modelo').value = '';
            loadModelosForMarca(marcaId);
        });
    }

    function clearFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-marca').value = '';
        document.getElementById('filter-modelo').value = '';
        document.getElementById('filter-modelo').innerHTML = '<option value="">Selecciona una marca primero</option>';
        document.getElementById('filter-modelo').disabled = true;
        document.getElementById('filter-price-min').value = '';
        document.getElementById('filter-price-max').value = '';
        document.getElementById('filter-order').value = 'date_desc';
        document.querySelectorAll('[data-spec]').forEach(el => el.value = '');
        currentPage = 1;
        loadCatalog();
    }

    function getFilters() {
        const filters = {
            q: document.getElementById('filter-search').value.trim(),
            price_min: document.getElementById('filter-price-min').value,
            price_max: document.getElementById('filter-price-max').value,
            marca_id: document.getElementById('filter-marca').value,
            modelo: document.getElementById('filter-modelo').value,
            order_by: document.getElementById('filter-order').value,
            specs: {}
        };

        document.querySelectorAll('[data-spec]').forEach(el => {
            const val = el.value.trim();
            if (val) {
                filters.specs[el.dataset.spec] = val;
            }
        });

        return filters;
    }

    function loadCatalog() {
        const filters = getFilters();
        const offset = (currentPage - 1) * limit;
        const grid = document.getElementById('catalog-grid');
        const empty = document.getElementById('catalog-empty');
        const pagination = document.getElementById('catalog-pagination');
        const countEl = document.getElementById('results-count');

        grid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#64748b;">Cargando vehículos...</p>';
        empty.hidden = true;
        pagination.hidden = true;

        let url = `${baseUrl}api/catalog.php?action=list&limit=${limit}&offset=${offset}`;
        url += `&q=${encodeURIComponent(filters.q)}`;
        url += `&price_min=${filters.price_min}`;
        url += `&price_max=${filters.price_max}`;
        url += `&order_by=${filters.order_by}`;
        if (filters.marca_id) url += `&marca_id=${filters.marca_id}`;
        if (filters.modelo) url += `&modelo=${encodeURIComponent(filters.modelo)}`;

        Object.entries(filters.specs).forEach(([key, val]) => {
            url += `&specs[${key}]=${encodeURIComponent(val)}`;
        });

        countEl.textContent = 'Buscando...';

        fetch(url)
            .then(res => res.json())
            .then(data => {
                grid.innerHTML = '';

                if (!data.items || data.items.length === 0) {
                    empty.hidden = false;
                    countEl.textContent = '0 vehículos encontrados';
                    return;
                }

                countEl.textContent = `${data.total} vehículo${data.total !== 1 ? 's' : ''} encontrado${data.total !== 1 ? 's' : ''}`;

                data.items.forEach(car => {
                    const card = createCarCard(car);
                    grid.appendChild(card);
                });

                if (data.total_pages > 1) {
                    pagination.hidden = false;
                    renderPagination(data.page, data.total_pages);
                }
            })
            .catch(err => {
                grid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#dc2626;">Error al cargar el catálogo.</p>';
                console.error(err);
            });
    }

    function createCarCard(car) {
        const card = document.createElement('div');
        card.className = 'catalog-card';

        const priceFormatted = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(car.price);

        card.innerHTML = `
            <a href="${baseUrl}auto/${car.slug}" class="catalog-card-img">
                <img src="${car.image_path || ''}" alt="${car.title}" loading="lazy">
                ${car.featured ? '<span class="catalog-card-badge">Destacado</span>' : ''}
            </a>
            <div class="catalog-card-body">
                <a href="${baseUrl}auto/${car.slug}" class="catalog-card-title">${escapeHtml(car.title)}</a>
                <div class="catalog-card-price" style="font-size:1.3rem;font-weight:800;color:#dc2626;margin-bottom:15px;">${priceFormatted}</div>
                <div class="catalog-card-actions">
                    <a href="${baseUrl}auto/${car.slug}" class="btn" style="flex:1;display:inline-block;text-align:center;background-color:#0B192C;color:white;padding:10px;border:none;border-radius:8px;font-size:0.8rem;font-weight:700;text-transform:uppercase;text-decoration:none;">VER DETALLES</a>
                    <a href="https://wa.me/${document.querySelector('.catalog-section')?.dataset?.whatsapp || ''}?text=${encodeURIComponent('Hola, me interesa el ' + car.title + ' a ' + priceFormatted)}" target="_blank" class="btn btn-outline" style="flex:1;display:inline-block;text-align:center;background:transparent;border:2px solid #0B192C;color:#0B192C;padding:10px;border-radius:8px;font-size:0.8rem;font-weight:700;text-transform:uppercase;text-decoration:none;"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        `;

        return card;
    }

    function renderPagination(current, total) {
        const pagesContainer = document.getElementById('pagination-pages');
        const prevBtn = document.getElementById('btn-prev-page');
        const nextBtn = document.getElementById('btn-next-page');

        prevBtn.disabled = current === 1;
        nextBtn.disabled = current === total;

        let html = '';
        const maxVisible = 5;
        let start = Math.max(1, current - Math.floor(maxVisible / 2));
        let end = Math.min(total, start + maxVisible - 1);
        if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);

        if (start > 1) {
            html += `<button class="pagination-page" data-page="1">1</button>`;
            if (start > 2) html += `<span style="padding: 0 5px; color: #94a3b8;">...</span>`;
        }

        for (let i = start; i <= end; i++) {
            html += `<button class="pagination-page ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        if (end < total) {
            if (end < total - 1) html += `<span style="padding: 0 5px; color: #94a3b8;">...</span>`;
            html += `<button class="pagination-page" data-page="${total}">${total}</button>`;
        }

        pagesContainer.innerHTML = html;

        pagesContainer.querySelectorAll('.pagination-page').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = parseInt(btn.dataset.page);
                loadCatalog();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', init);
})();
