<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Tu Auto Con Jesus Guerrero</title>
    <script>
        window.baseAppUrl = '<?= $base_url ?>';
    </script>
    <link rel="icon" href="<?= htmlspecialchars(get_asset_url($settings['favicon'] ?? '')) ?>" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
</head>
<body>

    <!-- Header / Navbar -->
    <header class="main-header">
        <div class="container header-container">
            <div class="logo">
                <a href="<?= $base_url ?>"><img src="<?= htmlspecialchars(get_asset_url($settings['logo'] ?? '')) ?>" alt="Tu Auto Con Jesus Guerrero"></a>
            </div>
            <nav class="header-nav">
                <a href="<?= $base_url ?>">Inicio</a>
                <a href="<?= $base_url ?>catalogo" class="active">Catálogo</a>
            </nav>
            <button class="hamburger" id="hamburger" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
            <div class="social-icons">
                <?php if(!empty($settings['social_instagram'])): ?>
                    <a href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if(!empty($settings['social_facebook'])): ?>
                    <a href="<?= htmlspecialchars($settings['social_facebook']) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if(!empty($settings['social_youtube'])): ?>
                    <a href="<?= htmlspecialchars($settings['social_youtube']) ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    <nav class="mobile-nav" id="mobileNav">
        <button class="mobile-nav-close" id="mobileNavClose">&times;</button>
        <a href="<?= $base_url ?>">Inicio</a>
        <a href="<?= $base_url ?>catalogo">Catálogo</a>
    </nav>

    <!-- Catalog Hero -->
    <section class="catalog-hero">
        <div class="container">
            <h1>CATÁLOGO COMPLETO</h1>
            <p>Encuentra tu vehículo ideal entre nuestra selección de elite</p>
        </div>
    </section>

    <!-- Catalog Content -->
    <section class="catalog-section">
        <div class="container">
            <div class="catalog-layout">
                <!-- Filters Sidebar -->
                <aside class="catalog-filters" id="catalog-filters">
                    <div class="filters-header">
                        <h3><i class="fas fa-sliders-h"></i> FILTROS</h3>
                        <button class="btn-clear-filters" id="btn-clear-filters" type="button">Limpiar</button>
                    </div>

                    <!-- Search -->
                    <div class="filter-group">
                        <label>Buscar</label>
                        <div class="search-input-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="filter-search" placeholder="Marca, modelo...">
                        </div>
                    </div>

                    <!-- Marca -->
                    <div class="filter-group">
                        <label>Marca</label>
                        <select id="filter-marca">
                            <option value="">Todas</option>
                        </select>
                    </div>

                    <!-- Modelo -->
                    <div class="filter-group">
                        <label>Modelo</label>
                        <select id="filter-modelo" disabled>
                            <option value="">Selecciona una marca primero</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <label>Rango de Precio</label>
                        <div class="price-inputs">
                            <div class="price-input">
                                <span>$</span>
                                <input type="number" id="filter-price-min" placeholder="Mín" min="0">
                            </div>
                            <span class="price-separator">—</span>
                            <div class="price-input">
                                <span>$</span>
                                <input type="number" id="filter-price-max" placeholder="Máx" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Spec Filters -->
                    <div id="spec-filters-container"></div>

                    <!-- Order By -->
                    <div class="filter-group">
                        <label>Ordenar por</label>
                        <select id="filter-order">
                            <option value="date_desc">Más recientes</option>
                            <option value="date_asc">Más antiguos</option>
                            <option value="price_asc">Precio: Menor a Mayor</option>
                            <option value="price_desc">Precio: Mayor a Menor</option>
                            <option value="name_asc">Nombre: A-Z</option>
                            <option value="name_desc">Nombre: Z-A</option>
                        </select>
                    </div>

                    <button type="button" id="btn-apply-filters" class="btn btn-primary btn-apply-filters">
                        <i class="fas fa-search"></i> APLICAR FILTROS
                    </button>
                </aside>

                <!-- Results -->
                <main class="catalog-results">
                    <div class="results-header">
                        <span id="results-count">Cargando...</span>
                        <button class="mobile-filter-toggle" id="mobile-filter-toggle" type="button">
                            <i class="fas fa-sliders-h"></i> Filtros
                        </button>
                    </div>

                    <div id="catalog-grid" class="catalog-grid"></div>

                    <div id="catalog-empty" class="catalog-empty" hidden>
                        <i class="fas fa-car-side"></i>
                        <p>No se encontraron vehículos con los filtros seleccionados.</p>
                    </div>

                    <!-- Pagination -->
                    <div id="catalog-pagination" class="catalog-pagination" hidden>
                        <button type="button" id="btn-prev-page" class="btn-pagination" disabled>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <div id="pagination-pages" class="pagination-pages"></div>
                        <button type="button" id="btn-next-page" class="btn-pagination">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </main>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container footer-container">
            <div class="footer-logo">
                <img src="<?= htmlspecialchars(get_asset_url($settings['logo'] ?? '')) ?>" alt="Tu Auto Con Jesus Guerrero">
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Tu Auto Con. Todos los derechos reservados.</p>
                <div class="social-icons">
                    <?php if(!empty($settings['social_instagram'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if(!empty($settings['social_facebook'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_facebook']) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <?php if(!empty($settings['whatsapp_number'])): 
        $wa_num_float = preg_replace('/[^0-9]/', '', $settings['whatsapp_number']);
    ?>
    <a href="https://wa.me/<?= $wa_num_float ?>" target="_blank" class="floating-wa">
        <i class="fab fa-whatsapp"></i>
    </a>
    <?php endif; ?>

    <script src="<?= $base_url ?>assets/js/catalog.js?v=<?= time() ?>"></script>
</body>
</html>
