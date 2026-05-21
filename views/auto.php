<?php
$preview_car_id = (int)($_GET['car_id'] ?? 0);
$has_preview_param = isset($_GET['preview']);

if ($has_preview_param && $preview_car_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? LIMIT 1");
    $stmt->execute([$preview_car_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE slug = ? LIMIT 1");
    $stmt->execute([$car_slug]);
}
$car = $stmt->fetch();

if (!$car) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$car['image_path'] = get_asset_url($car['image_path']);

$stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY sort_order ASC");
$stmt->execute([$car['id']]);
$raw_images = $stmt->fetchAll();
$seen_paths = [];
$car['images'] = [];
foreach ($raw_images as $img) {
    $p = $img['image_path'];
    if (isset($seen_paths[$p])) continue;
    $seen_paths[$p] = true;
    $img['image_path'] = get_asset_url($p);
    $car['images'][] = $img;
}

$stmt = $pdo->prepare("SELECT * FROM car_videos WHERE car_id = ? ORDER BY sort_order ASC");
$stmt->execute([$car['id']]);
$car['videos'] = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT cs.id, cs.valor, cs.etiqueta, cs.sort_order, sf.nombre, sf.slug, sf.tipo
    FROM car_specs cs
    LEFT JOIN spec_fields sf ON cs.spec_field_id = sf.id
    WHERE cs.car_id = ?
    ORDER BY cs.sort_order ASC
");
$stmt->execute([$car['id']]);
$car['specs'] = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM car_components WHERE car_id = ? AND is_active = 1 ORDER BY sort_order ASC");
$stmt->execute([$car['id']]);
$car['components'] = $stmt->fetchAll();
$is_preview = isset($_GET['preview']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$wa_num = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
$wa_template = $settings['whatsapp_message_template'] ?? 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?';
$precio_fmt = '$' . number_format((float)$car['price'], 2);
$wa_msg = str_replace(['{nombre}', '{precio}'], [$car['title'], $precio_fmt], $wa_template);
$wa_link = 'https://wa.me/' . $wa_num . '?text=' . rawurlencode($wa_msg);

$primary_image = $car['image_path'];
foreach ($car['images'] ?? [] as $img) {
    if ($img['is_primary']) {
        $primary_image = $img['image_path'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['title']) ?> - Tu Auto Con Jesus Guerrero</title>
    <meta name="description" content="<?= htmlspecialchars(substr($car['description'] ?? '', 0, 160)) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($car['title']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($primary_image) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(substr($car['description'] ?? '', 0, 160)) ?>">
    <script>
        window.baseAppUrl = '<?= $base_url ?>';
        window.carData = <?= json_encode($car, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;
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
                <a href="<?= $base_url ?>catalogo">Catálogo</a>
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

    <?php if ($is_preview): ?>
    <div class="preview-banner">
        <i class="fas fa-eye"></i> Vista previa — <a href="<?= $base_url ?>auto/<?= htmlspecialchars($car['slug']) ?>">Ver página publicada</a>
    </div>
    <?php endif; ?>

    <!-- Car Page Content -->
    <div id="car-page-content">
        <?php foreach ($car['components'] ?? [] as $comp): 
            $comp_type = $comp['component_type'];
            $raw_config = $comp['config'] ?? '{}';
            $config = is_array($raw_config) ? $raw_config : (json_decode($raw_config, true) ?: []);
        ?>
            <?php if ($comp_type === 'hero_slider'): ?>
                <!-- Hero Slider Component -->
                <section class="car-hero-slider" <?php if (!empty($config['background'])): ?>style="position: relative;"<?php endif; ?>>
                    <?php if (!empty($config['background'])): ?>
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('<?= htmlspecialchars(get_asset_url($config['background'])) ?>'); background-size: cover; background-position: center; opacity: 0.15; z-index: 0;"></div>
                    <?php endif; ?>
                    <?php $hero_bg = $car['image_path'] ?: ''; ?>
                    <div class="hero-slider-container" style="position: relative; z-index: 1;">
                        <?php if (!empty($hero_bg)): ?>
                        <div class="hero-slide active" style="background-image: url('<?= htmlspecialchars($hero_bg) ?>');"></div>
                        <?php endif; ?>
                        <div class="hero-slider-overlay"></div>
                        <div class="hero-slider-darken"></div>
                    </div>
                    <div class="hero-slider-content">
                        <?php if ($config['show_title'] ?? true): ?>
                        <h1><?= htmlspecialchars($car['title']) ?></h1>
                        <?php endif; ?>
                        <?php if ($config['show_price'] ?? true): ?>
                        <div class="hero-price"><?= htmlspecialchars($precio_fmt) ?></div>
                        <?php endif; ?>
                        <div class="hero-slider-actions">
                            <a href="<?= htmlspecialchars($wa_link) ?>" target="_blank" class="btn btn-primary"><i class="fab fa-whatsapp"></i> CONTACTAR</a>
                            <a href="#specs-tabla" class="btn btn-outline"><i class="fas fa-list"></i> VER ESPECIFICACIONES</a>
                        </div>
                    </div>
                </section>

            <?php elseif ($comp_type === 'specs_destacadas'): ?>
                <!-- Specs Destacadas Component -->
                <section class="car-specs-destacadas">
                    <div class="container">
                        <div class="specs-destacadas-grid">
                            <?php
                            $max_items = $config['max_items'] ?? 6;
                            $count = 0;
                            foreach ($car['specs'] as $spec):
                                if ($count >= $max_items) break;
                                $label = $spec['nombre'] ?? $spec['etiqueta'] ?? '';
                                if (empty($label)) continue;
                                $count++;
                            ?>
                            <div class="spec-destacada-card">
                                <i class="fas fa-tag"></i>
                                <span class="spec-label"><?= htmlspecialchars($label) ?></span>
                                <span class="spec-value"><?= htmlspecialchars($spec['valor']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

            <?php elseif ($comp_type === 'descripcion'): ?>
                <!-- Descripcion Component -->
                <?php if (!empty($car['description'])): 
                    $desc_img = $config['image'] ?? '';
                    $desc_img_pos = $config['image_position'] ?? 'left';
                    $has_image = !empty($desc_img);
                ?>
                <section class="car-descripcion">
                    <div class="container">
                        <h2><i class="fas fa-info-circle"></i> DESCRIPCIÓN</h2>
                        <div class="descripcion-layout <?= $has_image ? 'has-image' . ($desc_img_pos === 'right' ? ' image-right' : '') : '' ?>">
                            <?php if ($has_image): ?>
                            <div class="descripcion-image">
                                <img src="<?= htmlspecialchars(get_asset_url($desc_img)) ?>" alt="<?= htmlspecialchars($car['title']) ?>">
                            </div>
                            <?php endif; ?>
                            <div class="descripcion-text">
                                <div class="descripcion-content"><?= nl2br(htmlspecialchars($car['description'])) ?></div>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

            <?php elseif ($comp_type === 'exterior_interior'): ?>
                <!-- Exterior/Interior Component -->
                <?php
                $ext_title = $config['exterior_title'] ?? 'Exterior';
                $ext_desc = $config['exterior_description'] ?? '';
                $ext_img = $config['exterior_image'] ?? '';
                $int_title = $config['interior_title'] ?? 'Interior';
                $int_desc = $config['interior_description'] ?? '';
                $int_img = $config['interior_image'] ?? '';
                $has_ext = !empty($ext_desc) || !empty($ext_img);
                $has_int = !empty($int_desc) || !empty($int_img);
                if ($has_ext || $has_int):
                ?>
                <section class="car-exterior-interior">
                    <div class="container">
                        <div class="ei-tabs">
                            <button type="button" class="ei-tab active" data-target="exterior"><?= htmlspecialchars($ext_title) ?></button>
                            <button type="button" class="ei-tab" data-target="interior"><?= htmlspecialchars($int_title) ?></button>
                        </div>
                        <div class="ei-content active" data-content="exterior">
                            <?php if (!empty($ext_img) || !empty($ext_desc)): ?>
                            <div class="ei-layout">
                                <?php if (!empty($ext_img)): ?>
                                <div class="ei-image">
                                    <img src="<?= htmlspecialchars(get_asset_url($ext_img)) ?>" alt="<?= htmlspecialchars($ext_title) ?>">
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ext_desc)): ?>
                                <div class="ei-text">
                                    <h3><?= htmlspecialchars($ext_title) ?></h3>
                                    <p><?= nl2br(htmlspecialchars($ext_desc)) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="ei-empty"><i class="fas fa-car-side" style="font-size:2rem;margin-bottom:10px;display:block;"></i>Sin información de exterior.</div>
                            <?php endif; ?>
                        </div>
                        <div class="ei-content" data-content="interior">
                            <?php if (!empty($int_img) || !empty($int_desc)): ?>
                            <div class="ei-layout">
                                <?php if (!empty($int_img)): ?>
                                <div class="ei-image">
                                    <img src="<?= htmlspecialchars(get_asset_url($int_img)) ?>" alt="<?= htmlspecialchars($int_title) ?>">
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($int_desc)): ?>
                                <div class="ei-text">
                                    <h3><?= htmlspecialchars($int_title) ?></h3>
                                    <p><?= nl2br(htmlspecialchars($int_desc)) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="ei-empty"><i class="fas fa-couch" style="font-size:2rem;margin-bottom:10px;display:block;"></i>Sin información de interior.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

            <?php elseif ($comp_type === 'image_gallery'): ?>
                <!-- Image Gallery Component -->
                <?php $gallery_images = $car['images'] ?? []; ?>
                <?php if (!empty($gallery_images)): ?>
                <section class="car-image-gallery" id="gallery-section" <?php if (!empty($config['header_image'])): ?>style="position: relative;"<?php endif; ?>>
                    <?php if (!empty($config['header_image'])): ?>
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 200px; background-image: url('<?= htmlspecialchars(get_asset_url($config['header_image'])) ?>'); background-size: cover; background-position: center; opacity: 0.1; z-index: 0;"></div>
                    <?php endif; ?>
                    <div class="container" style="position: relative; z-index: 1;">
                        <h2><i class="fas fa-images"></i> GALERÍA</h2>
                        <div class="gallery-grid <?= $config['layout'] === 'masonry' ? 'gallery-masonry' : 'gallery-grid' ?>">
                            <?php foreach ($gallery_images as $img): ?>
                            <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($img['image_path']) ?>')">
                                <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($car['title']) ?>">
                                <?php if ($img['is_primary']): ?>
                                <span class="gallery-badge"><i class="fas fa-star"></i> Principal</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

            <?php elseif ($comp_type === 'specs_tabla'): ?>
                <!-- Specs Tabla Component -->
                <?php if (!empty($car['specs'])): ?>
                <section class="car-specs-tabla" id="specs-tabla">
                    <div class="container">
                        <h2><i class="fas fa-cogs"></i> ESPECIFICACIONES TÉCNICAS</h2>
                        <div class="specs-table-container">
                            <table class="specs-table">
                                <tbody>
                                    <?php foreach ($car['specs'] as $idx => $spec): ?>
                                    <tr class="<?= $idx % 2 === 0 ? 'even' : 'odd' ?>">
                                        <td class="spec-name"><?= htmlspecialchars($spec['nombre'] ?? $spec['etiqueta'] ?? '') ?></td>
                                        <td class="spec-val"><?= htmlspecialchars($spec['valor']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

            <?php elseif ($comp_type === 'video'): ?>
                <!-- Video Component -->
                <?php if (!empty($car['videos'])): ?>
                <section class="car-video">
                    <div class="container">
                        <h2><i class="fas fa-play-circle"></i> VIDEO</h2>
                        <div class="video-grid">
                            <?php foreach ($car['videos'] as $video): 
                                $video_id = '';
                                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video['url'], $m)) {
                                    $video_id = $m[1];
                                }
                                if ($video_id):
                            ?>
                            <div class="video-item">
                                <?php if (!empty($video['titulo'])): ?>
                                <h4><?= htmlspecialchars($video['titulo']) ?></h4>
                                <?php endif; ?>
                                <div class="video-embed">
                                    <iframe src="https://www.youtube.com/embed/<?= $video_id ?>" frameborder="0" allowfullscreen loading="lazy"></iframe>
                                </div>
                            </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

            <?php elseif ($comp_type === 'cta_whatsapp'): ?>
                <!-- CTA WhatsApp Component -->
                <section class="car-cta">
                    <div class="container">
                        <div class="cta-content">
                            <h2>¿TE INTERESA ESTE VEHÍCULO?</h2>
                            <p>Contáctanos ahora y te brindaremos toda la información que necesitas.</p>
                            <div class="cta-actions">
                                <a href="<?= htmlspecialchars($wa_link) ?>" target="_blank" class="btn btn-primary btn-cta-wa">
                                    <i class="fab fa-whatsapp"></i> ESCRIBIR POR WHATSAPP
                                </a>
                                <button type="button" class="btn btn-outline" onclick="openAppointmentModal()">
                                    <i class="fas fa-calendar-check"></i> RESERVAR CITA
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

            <?php elseif ($comp_type === 'calculadora'): ?>
                <!-- Calculadora Component -->
                <section class="car-calculadora">
                    <div class="container">
                        <h2><i class="fas fa-calculator"></i> CALCULADORA DE PAGO</h2>
                        <div class="calc-layout">
                            <div class="calc-inputs">
                                <?php
                                $min_price = (int)($settings['calc_min_price'] ?? 5000);
                                $max_price = (int)($settings['calc_max_price'] ?? 100000);
                                $default_apr = (float)($settings['calc_default_apr'] ?? 5);
                                ?>
                                <div class="form-group">
                                    <label>Precio del Vehículo ($)</label>
                                    <input type="text" id="calc_price" inputmode="numeric" value="<?= number_format((float)$car['price']) ?>" oninput="updateCalculator()" onfocus="this.value=this.value.replace(/,/g,'')" onblur="formatCalcInput(this)">
                                </div>
                                <div class="form-group">
                                    <label>Enganche ($)</label>
                                    <input type="text" id="calc_downpayment" inputmode="numeric" value="0" oninput="updateCalculator()" onfocus="this.value=this.value.replace(/,/g,'')" onblur="formatCalcInput(this)">
                                </div>
                                <div class="form-group">
                                    <label>Tasa de Interés (APR %)</label>
                                    <input type="number" id="calc_apr" min="0" max="100" step="0.5" value="<?= $default_apr ?>" oninput="updateCalculator()">
                                </div>
                                <div class="form-group">
                                    <label>Plazo del Préstamo (Meses)</label>
                                    <select id="calc_term" onchange="updateCalculator()">
                                        <?php
                                        $terms_str = $settings['calc_terms'] ?? '12,24,36,48,60,72,84';
                                        $terms = explode(',', $terms_str);
                                        $first = true;
                                        foreach ($terms as $term) {
                                            $term = trim($term);
                                            if (!empty($term)) {
                                                $selected = ($term == 60 || ($first && !in_array('60', $terms))) ? 'selected' : '';
                                                echo "<option value=\"$term\" $selected>$term Meses</option>";
                                                $first = false;
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="calc-results">
                                <div class="result-box">
                                    <h3>Pago Mensual Estimado</h3>
                                    <div class="monthly-payment" id="res_monthly">$0.00</div>
                                </div>
                                <div class="result-details">
                                    <div class="detail-row">
                                        <span>Monto Financiado:</span>
                                        <strong id="res_principal">$0.00</strong>
                                    </div>
                                    <div class="detail-row">
                                        <span>Total de Intereses:</span>
                                        <strong id="res_interest">$0.00</strong>
                                    </div>
                                    <div class="detail-row">
                                        <span>Costo Total del Préstamo:</span>
                                        <strong id="res_total">$0.00</strong>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <div class="chart-bar principal-bar" id="bar_principal" style="width: 100%;"></div>
                                    <div class="chart-bar interest-bar" id="bar_interest" style="width: 0%;"></div>
                                </div>
                                <div class="chart-legend">
                                    <span><span class="legend-color" style="background:#0B192C;"></span> Principal</span>
                                    <span><span class="legend-color" style="background:#dc3545;"></span> Interés</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            <?php elseif ($comp_type === 'autos_relacionados'): ?>
                <!-- Autos Relacionados Component -->
                <section class="car-related" <?php if (!empty($config['background'])): ?>style="position: relative;"<?php endif; ?>>
                    <?php if (!empty($config['background'])): ?>
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('<?= htmlspecialchars(get_asset_url($config['background'])) ?>'); background-size: cover; background-position: center; opacity: 0.08; z-index: 0;"></div>
                    <?php endif; ?>
                    <div class="container" style="position: relative; z-index: 1;">
                        <h2><i class="fas fa-car"></i> AUTOS RELACIONADOS</h2>
                        <div class="related-grid" id="related-cars-grid">
                            <!-- Loaded via JS -->
                        </div>
                    </div>
                </section>

            <?php elseif ($comp_type === 'custom_html'): ?>
                <!-- Custom HTML Component -->
                <?php if (!empty($config['html'])): ?>
                <section class="car-custom-html" data-component-id="<?= $comp['id'] ?>">
                    <?php if (!empty($config['css'])): ?>
                    <style>
                    .car-custom-html[data-component-id="<?= $comp['id'] ?>"] { <?= $config['css'] ?> }
                    </style>
                    <?php endif; ?>
                    <div class="container">
                        <?= $config['html'] ?>
                    </div>
                    <?php if (!empty($config['js'])): ?>
                    <script>
                    (function() {
                        try { <?= $config['js'] ?> } catch(e) { console.error('Error en componente custom HTML:', e); }
                    })();
                    </script>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Lightbox -->
    <div id="image-lightbox" class="lightbox-overlay" onclick="closeLightbox()">
        <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
        <button class="lightbox-nav prev" onclick="event.stopPropagation(); navigateLightbox(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="lightbox-nav next" onclick="event.stopPropagation(); navigateLightbox(1)"><i class="fas fa-chevron-right"></i></button>
        <img id="lightbox-img" src="" alt="">
    </div>

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
    <?php if(!empty($settings['whatsapp_number'])): ?>
    <a href="https://wa.me/<?= $wa_num ?>" target="_blank" class="floating-wa">
        <i class="fab fa-whatsapp"></i>
    </a>
    <?php endif; ?>

    <!-- Appointment Modal -->
    <div id="appointmentModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 780px; width: 95%;">
            <span class="close-modal" onclick="closeAppointmentModal()">&times;</span>
            <div style="text-align:center; margin-bottom: 25px;">
                <i class="fas fa-calendar-alt" style="font-size: 40px; color: var(--primary-color); margin-bottom: 10px;"></i>
                <h2 style="margin: 0; font-family: var(--font-heading); font-weight: 900; text-transform: uppercase;">Reserva tu Cita</h2>
                <p style="color: #666; font-size: 0.9rem; margin-top: 5px;">Déjanos tus datos y nos pondremos en contacto contigo.</p>
            </div>
            
            <div id="appointmentMessage" style="display:none; padding:15px; margin-bottom:20px; border-radius:8px; font-weight: 600; text-align: center;"></div>
            
            <form id="appointmentForm" onsubmit="submitAppointment(event)">
                <div class="form-row-2col">
                    <div class="form-group" style="position: relative;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Nombre</label>
                        <i class="fas fa-user" style="position: absolute; left: 15px; top: 38px; color: #999;"></i>
                        <input type="text" id="appt_first_name" required style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; transition: all 0.3s; box-sizing: border-box;" onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(230,0,0,0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                    </div>
                    <div class="form-group" style="position: relative;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Apellido</label>
                        <input type="text" id="appt_last_name" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; transition: all 0.3s; box-sizing: border-box;" onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(230,0,0,0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div class="form-group" style="position: relative; margin-top: 15px;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Teléfono</label>
                    <i class="fas fa-phone" style="position: absolute; left: 15px; top: 38px; color: #999;"></i>
                    <input type="text" id="appt_phone" required style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; transition: all 0.3s; box-sizing: border-box;" placeholder="+1 234 567 890" onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(230,0,0,0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label style="font-size: 0.9rem; font-weight: 700; color: #333; margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">Selecciona tu Fecha y Hora</label>
                    
                    <div class="appointment-picker-container">
                        <div>
                            <div id="calendar-inline" style="width: 100%;"></div>
                            <input type="hidden" id="appt_date" required>
                        </div>
                        
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; min-height: 280px; box-sizing: border-box;">
                            <h4 style="margin: 0 0 12px 0; font-size: 0.85rem; color: #475569; display: flex; align-items: center; gap: 6px; text-transform: uppercase; font-family: var(--font-heading); font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                <i class="fas fa-clock" style="color: var(--primary-color);"></i> Horarios Disponibles
                            </h4>
                            <div id="slots-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; overflow-y: auto; max-height: 220px; flex-grow: 1;">
                                <p id="slots-placeholder" style="grid-column: span 2; color: #94a3b8; font-size: 0.85rem; text-align: center; margin: 40px auto 0 auto; line-height: 1.4;">
                                    Selecciona un día disponible en el calendario para ver las horas.
                                </p>
                            </div>
                            <input type="hidden" id="appt_time" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 25px; padding: 15px; font-size: 1.1rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px rgba(230,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    CONFIRMAR RESERVA <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <?php $preview_scroll = isset($_GET['s']) ? (int)$_GET['s'] : 0; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="<?= $base_url ?>assets/js/auto-page.js?v=<?= time() ?>"></script>
    <script src="<?= $base_url ?>assets/js/main.js?v=<?= time() ?>"></script>
    <?php if ($preview_scroll > 0): ?>
    <script>
    window.addEventListener('load', function() {
        window.scrollTo(0, <?= $preview_scroll ?>);
    });
    </script>
    <?php endif; ?>
</body>
</html>
