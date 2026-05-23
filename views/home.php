<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Auto Con Jesus Guerrero</title>
    <!-- Favicon -->
    <script>
        window.baseAppUrl = '<?= $base_url ?>';
    </script>
    <link rel="icon" href="<?= htmlspecialchars(get_asset_url($settings['favicon'] ?? '')) ?>" type="image/x-icon">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css?v=<?= time() ?>">
    <style>
        /* Responsive styles for Flatpickr appointment picker */
        @media (max-width: 768px) {
            .appointment-picker-container {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
            }
        }
        /* Custom styles for flatpickr calendar to match modern design */
        .flatpickr-calendar {
            background: #fff !important;
            box-shadow: none !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            font-family: inherit !important;
            width: 100% !important;
            max-width: 320px;
            margin: 0 auto;
        }
        .flatpickr-months {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 0;
            border-radius: 12px 12px 0 0;
        }
        .flatpickr-months .flatpickr-month {
            color: #1e293b !important;
            fill: #1e293b !important;
            font-weight: 700;
        }
        .flatpickr-months .flatpickr-prev-month, 
        .flatpickr-months .flatpickr-next-month {
            color: #475569 !important;
            fill: #475569 !important;
            padding: 10px;
        }
        .flatpickr-months .flatpickr-prev-month:hover, 
        .flatpickr-months .flatpickr-next-month:hover {
            color: var(--primary-color) !important;
            fill: var(--primary-color) !important;
        }
        .flatpickr-weekdays {
            background: #f8fafc;
            padding: 8px 0;
        }
        .flatpickr-weekday {
            color: #64748b !important;
            font-weight: 700 !important;
            font-size: 0.8rem;
        }
        .flatpickr-days {
            border: none !important;
            padding: 5px;
        }
        .flatpickr-day {
            color: #334155 !important;
            border-radius: 8px !important;
            font-weight: 600;
            font-size: 0.85rem;
            margin: 2px 0;
        }
        .flatpickr-day.flatpickr-disabled, 
        .flatpickr-day.flatpickr-disabled:hover {
            color: #cbd5e1 !important;
            background: transparent !important;
            cursor: not-allowed;
            text-decoration: none;
        }
        .flatpickr-day.selected {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(230, 0, 0, 0.25) !important;
        }
        .flatpickr-day.selected:hover {
            background: #b30000 !important;
            border-color: #b30000 !important;
        }
        .flatpickr-day:hover {
            background: #f1f5f9 !important;
        }
        .flatpickr-day.today {
            border-color: #cbd5e1 !important;
        }
        .flatpickr-day.today:hover {
            border-color: var(--primary-color) !important;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <header class="main-header">
        <div class="container header-container">
            <div class="logo">
                <img src="<?= htmlspecialchars(get_asset_url($settings['logo'] ?? '')) ?>" alt="Tu Auto Con Jesus Guerrero">
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
                <?php if(!empty($settings['social_twitter'])): ?>
                    <a href="<?= htmlspecialchars($settings['social_twitter']) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <?php
            // Obtener imágenes del carrusel si existen
            $stmt = $pdo->query("SELECT * FROM carousel_images WHERE is_active = 1");
            $hero_images = $stmt->fetchAll();
            
            // Si no hay imágenes en BD, usar una por defecto (o color de fallback)
            if (empty($hero_images)) {
                echo '<div class="hero-bg slide active" style="background-color: #1a1a1a;"></div>';
            } else {
                foreach($hero_images as $index => $img) {
                    $active = $index === 0 ? 'active' : '';
                    echo '<div class="hero-bg slide '.$active.'" style="background-image: url(\''.htmlspecialchars(get_asset_url($img['image_path'])).'\');"></div>';
                }
            }
        ?>
        <div class="hero-overlay"></div>

        <!-- Carousel Controls -->
        <?php if (count($hero_images) > 1): ?>
        <button class="carousel-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next-btn"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-dots">
            <?php foreach($hero_images as $index => $img): ?>
                <span class="dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="container hero-content">
            <h1 class="hero-title">ASESORÍA QUE MARCA<br><span> LA DIFERENCIA</span></h1>
            <a href="#inventory" class="btn btn-primary hero-btn">VER INVENTARIO DE ÉLITE</a>
        </div>
    </section>

    <!-- Carbon Fiber Buttons Container -->
    <section class="action-buttons-section">
        <div class="container">
            <div class="carbon-fiber-container">
                <button class="carbon-btn" onclick="openAppointmentModal()">
                    <i class="fas fa-calendar-check"></i> RESERVA TU CITA
                </button>
                <?php
                    $wa_num_float = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
                ?>
                <a href="https://wa.me/<?= $wa_num_float ?>" target="_blank" class="carbon-btn">
                    <i class="fab fa-whatsapp"></i> CONTÁCTANOS
                </a>
                <button class="carbon-btn" onclick="openCalculatorModal()">
                    <i class="fas fa-calculator"></i> CALCULADORA DE PAGO
                </button>
            </div>
        </div>
    </section>

    <!-- Servicios Section -->
    <?php
        $stmt_services = $pdo->query("SELECT * FROM services ORDER BY id ASC");
        $services = $stmt_services->fetchAll();
    ?>
    <section id="services-section" class="main-content-section" style="padding-bottom: 0; background-color: var(--secondary-color);">
        <div class="container">
            <div class="layout-column nuestros-servicios-bottom" style="margin-top: 0;">
                <div class="section-header center">
                    <h2>NUESTROS SERVICIOS</h2>
                </div>
                <div class="services-grid-4">
                    <?php if (empty($services)): ?>
                        <p class="cars-empty" style="grid-column: 1/-1; text-align:center;">No hay servicios configurados.</p>
                    <?php else: foreach($services as $svc): ?>
                    <div class="service-item">
                        <i class="<?= htmlspecialchars($svc['icon']) ?>"></i>
                        <h4><?= htmlspecialchars($svc['title']) ?></h4>
                        <p><?= nl2br(htmlspecialchars($svc['description'])) ?></p>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <?php
        $wa_num_inventory = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
        $wa_template_default = 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?';
        $wa_message_template = $settings['whatsapp_message_template'] ?? $wa_template_default;
        if (trim($wa_message_template) === '') {
            $wa_message_template = $wa_template_default;
        }
    ?>
    <section id="inventory" class="main-content-section" data-whatsapp="<?= htmlspecialchars($wa_num_inventory) ?>">
        <script type="application/json" id="wa-message-template"><?= json_encode($wa_message_template, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
        <div class="container">

            <?php
                $stmt_recent = $pdo->prepare("SELECT id, title, slug, price, image_path FROM cars WHERE status = 'active' ORDER BY id DESC LIMIT 3");
                $stmt_recent->execute();
                $recent_cars = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if (!empty($recent_cars)): ?>
            <div class="anadido-recientemente">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Últimos Añadidos</h2>
                </div>
                <div class="recent-carousel">
                    <?php foreach ($recent_cars as $rc): 
                        $img = get_asset_url($rc['image_path']);
                        $price_fmt = '$' . number_format((float)$rc['price'], 0, '', ',');
                        $link = $base_url . 'auto/' . htmlspecialchars($rc['slug']);
                    ?>
                    <a href="<?= $link ?>" class="small-car-card">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($rc['title']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="section-header inventory-header" style="margin-bottom: 30px;">
                <h2>EXPLORA POR MARCA</h2>
                <a href="<?= $base_url ?>catalogo" class="btn-catalog-link"><i class="fas fa-th-large"></i> Ver Catálogo Completo</a>
                <div class="car-search-wrap">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" id="car-search" class="car-search-input" placeholder="Buscar por marca, modelo..." autocomplete="off">
                </div>
            </div>

            <!-- Brand Sections Container -->
            <div id="brand-sections">
                <p style="text-align:center; color:#64748b; padding:40px 0;">Cargando marcas...</p>
            </div>

            <!-- Search Results (hidden by default) -->
            <div id="search-results" hidden>
                <p id="search-empty" class="cars-empty" hidden>No hay vehículos que coincidan con tu búsqueda.</p>
                <div id="search-grid" class="cars-grid grid-3x3"></div>
                <div class="cars-load-more-wrap">
                    <button type="button" id="search-load-more" class="btn btn-primary cars-load-more" hidden>Cargar más</button>
                </div>
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
                    <!-- Repeated for footer if needed -->
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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="<?= $base_url ?>assets/js/main.js?v=<?= time() ?>"></script>
    <script src="<?= $base_url ?>assets/js/home-brands.js?v=<?= time() ?>"></script>

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
                        <input type="text" id="appt_first_name" required style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(230,0,0,0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                    </div>
                    <div class="form-group" style="position: relative;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Apellido</label>
                        <input type="text" id="appt_last_name" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(230,0,0,0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div class="form-group" style="position: relative; margin-top: 15px;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Teléfono</label>
                    <i class="fas fa-phone" style="position: absolute; left: 15px; top: 38px; color: #999;"></i>
                    <input type="text" id="appt_phone" required style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; transition: all 0.3s;" placeholder="+1 234 567 890" onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(230,0,0,0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label style="font-size: 0.9rem; font-weight: 700; color: #333; margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">Selecciona tu Fecha y Hora</label>
                    
                    <div class="appointment-picker-container">
                        <!-- Inline Calendar Container -->
                        <div>
                            <div id="calendar-inline" style="width: 100%;"></div>
                            <input type="hidden" id="appt_date" required>
                        </div>
                        
                        <!-- Slots Container -->
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

    <!-- Calculator Modal -->
    <div id="calculatorModal" class="modal-overlay">
        <div class="modal-content calculator-content">
            <span class="close-modal" onclick="closeCalculatorModal()">&times;</span>
            <h2>Calculadora de Pago Mensual</h2>
             <?php
             $min_price = (int)($settings['calc_min_price'] ?? 5000);
             $max_price = (int)($settings['calc_max_price'] ?? 100000);
             $default_price = max($min_price, min(25000, $max_price));

             $min_downpayment = (int)($settings['calc_min_downpayment'] ?? 0);
             $max_downpayment = (int)($settings['calc_max_downpayment'] ?? 50000);
             $default_downpayment = max($min_downpayment, min(5000, $max_downpayment));

             $default_apr = (float)($settings['calc_default_apr'] ?? 5);
             ?>
             <div class="calc-layout">
                 <div class="calc-inputs">
                     <div class="form-group">
                         <label>Precio del Vehículo ($)</label>
                         <input type="text" id="calc_price" inputmode="numeric" min="<?= $min_price ?>" max="<?= $max_price ?>" value="<?= number_format($default_price) ?>" oninput="updateCalculator()" onfocus="this.value=this.value.replace(/,/g,'')" onblur="formatCalcInput(this)">
                     </div>
                     <div class="form-group">
                         <label>Enganche ($)</label>
                         <input type="text" id="calc_downpayment" inputmode="numeric" min="<?= $min_downpayment ?>" max="<?= $max_downpayment ?>" value="<?= number_format($default_downpayment) ?>" oninput="updateCalculator()" onfocus="this.value=this.value.replace(/,/g,'')" onblur="formatCalcInput(this)">
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
                        <div class="monthly-payment" id="res_monthly">$377.42</div>
                    </div>
                    <div class="result-details">
                        <div class="detail-row">
                            <span>Monto Financiado:</span>
                            <strong id="res_principal">$20,000.00</strong>
                        </div>
                        <div class="detail-row">
                            <span>Total de Intereses:</span>
                            <strong id="res_interest">$2,645.48</strong>
                        </div>
                        <div class="detail-row">
                            <span>Costo Total del Préstamo:</span>
                            <strong id="res_total">$22,645.48</strong>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-bar principal-bar" id="bar_principal" style="width: 88%;"></div>
                        <div class="chart-bar interest-bar" id="bar_interest" style="width: 12%;"></div>
                    </div>
                    <div class="chart-legend">
                        <span><span class="legend-color" style="background:#0B192C;"></span> Principal</span>
                        <span><span class="legend-color" style="background:#dc3545;"></span> Interés</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inline Script for Modals to bypass any caching issues -->
    <script>
    /* Modals Logic */
    let fpInstance = null;

    function openAppointmentModal() {
        document.getElementById('appointmentModal').style.display = 'flex';
        initFlatpickr();
    }

    function closeAppointmentModal() {
        document.getElementById('appointmentModal').style.display = 'none';
        document.getElementById('appointmentForm').reset();
        document.getElementById('appt_date').value = '';
        document.getElementById('appt_time').value = '';
        
        // Reset slots UI
        document.getElementById('slots-container').innerHTML = `
            <p id="slots-placeholder" style="grid-column: span 2; color: #94a3b8; font-size: 0.85rem; text-align: center; margin: 40px auto 0 auto; line-height: 1.4;">
                Selecciona un día disponible en el calendario para ver las horas.
            </p>
        `;

        if(fpInstance) {
            fpInstance.destroy();
            fpInstance = null;
        }
        document.getElementById('appointmentMessage').style.display = 'none';
    }

    function openCalculatorModal() {
        document.getElementById('calculatorModal').style.display = 'flex';
        updateCalculator();
    }

    function closeCalculatorModal() {
        document.getElementById('calculatorModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        let apptModal = document.getElementById('appointmentModal');
        let calcModal = document.getElementById('calculatorModal');
        if (event.target === apptModal) closeAppointmentModal();
        if (event.target === calcModal) closeCalculatorModal();
    });

    /* Appointment System Logic */
    function initFlatpickr() {
        fetch(window.baseAppUrl + 'api/appointments.php?action=available_dates')
            .then(res => res.json())
            .then(data => {
                const availableDates = data.dates || [];
                
                fpInstance = flatpickr("#calendar-inline", {
                    inline: true,
                    locale: "es",
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    enable: [
                        function(date) {
                            // Enable only dates fetched from API
                            const dStr = flatpickr.formatDate(date, "Y-m-d");
                            return availableDates.includes(dStr);
                        }
                    ],
                    onChange: function(selectedDates, dateStr) {
                        document.getElementById('appt_date').value = dateStr;
                        fetchAvailableSlots(dateStr);
                    }
                });
            })
            .catch(err => console.error("Error initializing calendar:", err));
    }

    function fetchAvailableSlots(date) {
        const slotsContainer = document.getElementById('slots-container');
        document.getElementById('appt_time').value = ''; // Reset selected time
        
        slotsContainer.innerHTML = '<p style="grid-column: span 2; color: #666; font-size: 0.85rem; text-align: center; margin-top: 30px;"><i class="fas fa-spinner fa-spin"></i> Cargando horarios...</p>';

        fetch(window.baseAppUrl + `api/appointments.php?action=available_slots&date=${date}`)
            .then(res => res.json())
            .then(data => {
                slotsContainer.innerHTML = '';
                if(data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        let [h, m] = slot.split(':');
                        let ampm = h >= 12 ? 'PM' : 'AM';
                        let h12 = h % 12 || 12;
                        let displayTime = `${h12}:${m} ${ampm}`;
                        
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'time-slot-btn';
                        btn.innerText = displayTime;
                        btn.style.cssText = `
                            padding: 12px 10px;
                            border: 1px solid #e2e8f0;
                            border-radius: 10px;
                            background: #fff;
                            color: #334155;
                            font-size: 0.85rem;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s ease;
                            text-align: center;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
                        `;
                        
                        btn.addEventListener('click', function() {
                            // Deselect others
                            document.querySelectorAll('.time-slot-btn').forEach(b => {
                                b.style.background = '#fff';
                                b.style.color = '#334155';
                                b.style.borderColor = '#e2e8f0';
                                b.style.boxShadow = '0 2px 4px rgba(0,0,0,0.02)';
                            });
                            // Select this one
                            btn.style.background = 'var(--primary-color)';
                            btn.style.color = '#fff';
                            btn.style.borderColor = 'var(--primary-color)';
                            btn.style.boxShadow = '0 4px 12px rgba(220, 53, 69, 0.25)';
                            document.getElementById('appt_time').value = slot;
                        });

                        // Hover effect
                        btn.addEventListener('mouseover', function() {
                            if(document.getElementById('appt_time').value !== slot) {
                                btn.style.background = '#f8fafc';
                                btn.style.borderColor = '#cbd5e1';
                                btn.style.transform = 'translateY(-1px)';
                            }
                        });
                        btn.addEventListener('mouseout', function() {
                            if(document.getElementById('appt_time').value !== slot) {
                                btn.style.background = '#fff';
                                btn.style.borderColor = '#e2e8f0';
                                btn.style.transform = 'none';
                            }
                        });

                        slotsContainer.appendChild(btn);
                    });
                } else {
                    slotsContainer.innerHTML = '<p style="grid-column: span 2; color: #dc2626; font-size: 0.85rem; text-align: center; margin-top: 30px; font-weight:600;">Sin horarios disponibles para este día.</p>';
                }
            })
            .catch(err => {
                console.error("Error fetching slots:", err);
                slotsContainer.innerHTML = '<p style="grid-column: span 2; color: #dc2626; font-size: 0.85rem; text-align: center; margin-top: 30px;">Error al cargar horarios.</p>';
            });
    }

    function submitAppointment(e) {
        e.preventDefault();
        
        const dateVal = document.getElementById('appt_date').value;
        const timeVal = document.getElementById('appt_time').value;

        if(!dateVal || !timeVal) {
            const msgDiv = document.getElementById('appointmentMessage');
            msgDiv.style.display = 'block';
            msgDiv.style.backgroundColor = '#f8d7da';
            msgDiv.style.color = '#721c24';
            msgDiv.innerText = 'Por favor, selecciona un día y una hora de tu cita.';
            return;
        }

        const formData = new FormData();
        formData.append('action', 'book');
        formData.append('first_name', document.getElementById('appt_first_name').value);
        formData.append('last_name', document.getElementById('appt_last_name').value);
        formData.append('phone', document.getElementById('appt_phone').value);
        formData.append('appointment_date', dateVal);
        formData.append('appointment_time', timeVal);

        fetch(window.baseAppUrl + 'api/appointments.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const msgDiv = document.getElementById('appointmentMessage');
            msgDiv.style.display = 'block';
            if(data.success) {
                msgDiv.style.backgroundColor = '#d4edda';
                msgDiv.style.color = '#155724';
                msgDiv.innerText = 'Cita reservada exitosamente. Te contactaremos pronto.';
                document.getElementById('appointmentForm').reset();
                document.getElementById('appt_date').value = '';
                document.getElementById('appt_time').value = '';
                setTimeout(closeAppointmentModal, 3000);
            } else {
                msgDiv.style.backgroundColor = '#f8d7da';
                msgDiv.style.color = '#721c24';
                msgDiv.innerText = data.error || 'Ocurrió un error';
            }
        })
        .catch(err => console.error("Error submitting appointment:", err));
    }

    /* Calculator Logic */
    function formatCalcInput(el) {
        const raw = el.value.replace(/,/g, '');
        const num = parseFloat(raw);
        if (!isNaN(num)) {
            el.value = num.toLocaleString('en-US');
        }
    }

    function getCalcValue(id) {
        const el = document.getElementById(id);
        return parseFloat(el.value.replace(/,/g, '')) || 0;
    }

    function updateCalculator() {
        const price = getCalcValue('calc_price');
        const downpayment = getCalcValue('calc_downpayment');
        const apr = parseFloat(document.getElementById('calc_apr').value) || 0;
        const term = parseInt(document.getElementById('calc_term').value) || 12;

        const principal = price - downpayment;
        
        if (principal <= 0) {
            document.getElementById('res_monthly').innerText = "$0.00";
            document.getElementById('res_principal').innerText = "$0.00";
            document.getElementById('res_interest').innerText = "$0.00";
            document.getElementById('res_total').innerText = "$0.00";
            document.getElementById('bar_principal').style.width = "100%";
            document.getElementById('bar_interest').style.width = "0%";
            return;
        }

        const r = (apr / 100) / 12;
        const n = term;

        let monthly = 0;
        let totalInterest = 0;
        
        if (r === 0) {
            monthly = principal / n;
        } else {
            monthly = principal * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
            totalInterest = (monthly * n) - principal;
        }

        const totalCost = principal + totalInterest;

        const formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
        
        document.getElementById('res_monthly').innerText = formatter.format(monthly);
        document.getElementById('res_principal').innerText = formatter.format(principal);
        document.getElementById('res_interest').innerText = formatter.format(totalInterest);
        document.getElementById('res_total').innerText = formatter.format(totalCost);

        const principalPct = (principal / totalCost) * 100;
        const interestPct = (totalInterest / totalCost) * 100;
        
        document.getElementById('bar_principal').style.width = principalPct + '%';
        document.getElementById('bar_interest').style.width = interestPct + '%';
    }
    </script>
</body>
</html>
