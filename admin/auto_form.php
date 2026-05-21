<?php
require_once __DIR__ . '/../api/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . $base_url . "admin");
    exit;
}

$mode = $_GET['mode'] ?? 'create';
$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($car_id === 0 && $mode === 'edit') {
    $mode = 'create';
}

if ($mode === 'edit' && $car_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $existing_car = $stmt->fetch();
    if (!$existing_car) {
        $_SESSION['toast_msg'] = 'Auto no encontrado. Redirigiendo al dashboard...';
        $_SESSION['toast_type'] = 'error';
        header("Location: " . $base_url . "admin/dashboard");
        exit;
    }
    $page_title = 'Editar: ' . $existing_car['title'];
} else {
    $mode = 'create';
    $existing_car = null;
    $page_title = 'Crear Nuevo Auto';
}

$stmt = $pdo->query("SELECT * FROM marcas ORDER BY nombre ASC");
$marcas = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM spec_fields ORDER BY sort_order ASC");
$spec_fields = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM car_components WHERE car_id IS NULL ORDER BY sort_order ASC");
$default_components = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>admin/assets/css/auto-form.css?v=<?= time() ?>">
</head>
<body>

<div class="auto-form-layout">
    <div class="auto-form-left">
        <div class="top-bar">
            <a href="<?= $base_url ?>admin/dashboard<?= isset($_GET['page']) ? '?page=' . (int)$_GET['page'] : '' ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Volver</a>
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <div class="top-bar-actions">
                <button class="btn btn-secondary btn-sm" onclick="togglePreview()"><i class="fas fa-eye"></i> Preview</button>
                <button class="btn btn-primary" id="btnSave" onclick="saveAll()"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
        <div id="upload-overlay" class="upload-overlay" style="display:none;">
            <div class="upload-modal">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p id="upload-status">Subiendo imagen...</p>
                <div class="upload-bar-track">
                    <div id="upload-progress" class="upload-bar-fill" style="width: 0%;"></div>
                </div>
                <span id="upload-percent" class="upload-percent">0%</span>
            </div>
        </div>
        <div id="unsaved-badge" class="unsaved-badge" style="display:none;">
            <i class="fas fa-exclamation-circle"></i> <span id="unsaved-text">Cambios sin guardar</span>
        </div>

        <div class="form-content">
            <input type="hidden" id="formMode" value="<?= $mode ?>">
            <input type="hidden" id="formCarId" value="<?= $existing_car ? $existing_car['id'] : '' ?>">

            <div class="form-section" id="section-basic">
                <div class="section-header" onclick="toggleSection('section-basic')">
                    <i class="fas fa-info-circle"></i>
                    <h2>Datos Básicos</h2>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="section-body">
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label>Marca</label>
                            <select id="car_marca_id" onchange="schedulePreview(); scheduleAutoSave()">
                                <option value="">Seleccionar marca</option>
                                <?php foreach($marcas as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $existing_car && $existing_car['marca_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Modelo</label>
                            <input type="text" id="car_modelo" placeholder="Ej: RAV4, Silverado" value="<?= $existing_car ? htmlspecialchars($existing_car['modelo']) : '' ?>" oninput="schedulePreview(); scheduleAutoSave()">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" id="car_title" placeholder="Ej: Toyota RAV4 LE Hybrid 2025" value="<?= $existing_car ? htmlspecialchars($existing_car['title']) : '' ?>" oninput="autoGenerateSlug(); scheduleAutoSave()">
                    </div>
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label>Slug (URL)</label>
                            <input type="text" id="car_slug" placeholder="toyota-rav4-le-hybrid-2025" value="<?= $existing_car ? htmlspecialchars($existing_car['slug']) : '' ?>" oninput="scheduleAutoSave()">
                        </div>
                        <div class="form-group">
                            <label>Precio ($)</label>
                            <input type="number" id="car_price" step="0.01" placeholder="45000" value="<?= $existing_car ? $existing_car['price'] : '' ?>" oninput="schedulePreview(); scheduleAutoSave()">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="car_description" rows="4" placeholder="Describe el vehículo..." oninput="schedulePreview(); scheduleAutoSave()"><?= $existing_car ? htmlspecialchars($existing_car['description']) : '' ?></textarea>
                    </div>
                    <div class="form-row-3col">
                        <div class="form-group">
                            <label>Estado</label>
                            <select id="car_status" onchange="schedulePreview(); scheduleAutoSave()">
                                <option value="active" <?= $existing_car && $existing_car['status'] === 'active' ? 'selected' : '' ?>>Activo</option>
                                <option value="draft" <?= $existing_car && $existing_car['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                <option value="sold" <?= $existing_car && $existing_car['status'] === 'sold' ? 'selected' : '' ?>>Vendido</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label" style="margin-top: 24px;">
                                <input type="checkbox" id="car_featured" <?= $existing_car && $existing_car['featured'] ? 'checked' : '' ?> onchange="schedulePreview(); scheduleAutoSave()"> Destacado
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Imagen Principal</label>
                            <input type="file" id="car_image_file" accept="image/*" onchange="uploadPrimaryImage(this)">
                        </div>
                    </div>
                    <div id="primary-image-preview" style="margin-top: 10px;">
                        <?php if ($existing_car && !empty($existing_car['image_path'])): ?>
                        <img src="<?= $base_url . ltrim($existing_car['image_path'], '/') ?>" style="max-width: 200px; border-radius: 8px;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-section" id="section-specs">
                <div class="section-header" onclick="toggleSection('section-specs')">
                    <i class="fas fa-cogs"></i>
                    <h2>Especificaciones</h2>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="section-body">
                    <div id="specs-container"></div>
                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                        <button class="btn btn-secondary btn-sm" onclick="addSpecFieldRow()"><i class="fas fa-plus"></i> Campo Estándar</button>
                        <button class="btn btn-secondary btn-sm" onclick="addCustomSpecRow()"><i class="fas fa-plus"></i> Campo Personalizado</button>
                    </div>
                </div>
            </div>

            <div class="form-section" id="section-gallery">
                <div class="section-header" onclick="toggleSection('section-gallery')">
                    <i class="fas fa-images"></i>
                    <h2>Galería de Imágenes</h2>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="section-body">
                    <div class="image-upload-zone" id="gallery-upload-zone" onclick="document.getElementById('gallery-upload-input').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p id="gallery-upload-text">Haz clic o arrastra imágenes aquí</p>
                        <div id="gallery-progress-wrap" style="display:none; margin-top: 12px;">
                            <div class="upload-bar-track"><div id="gallery-progress" class="upload-bar-fill" style="width:0%"></div></div>
                            <span id="gallery-progress-text" style="font-size:0.8rem;color:var(--text-secondary);margin-top:4px;display:block;">0%</span>
                        </div>
                        <input type="file" id="gallery-upload-input" accept="image/*" multiple style="display: none;" onchange="uploadGalleryImages(this)">
                    </div>
                    <div class="gallery-grid" id="gallery-container"></div>
                </div>
            </div>

            <div class="form-section" id="section-components">
                <div class="section-header" onclick="toggleSection('section-components')">
                    <i class="fas fa-puzzle-piece"></i>
                    <h2>Componentes de Página</h2>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="section-body">
                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 10px;">Arrastra para reordenar. Activa/desactiva los que necesites.</p>
                    <div class="component-list" id="components-container"></div>
                    <div style="margin-top: 12px;">
                        <select id="new-component-type" class="form-group" style="width: auto; display: inline-block; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem;">
                            <option value="">Agregar componente...</option>
                            <option value="hero_slider">Hero Slider</option>
                            <option value="specs_destacadas">Specs Destacadas</option>
                            <option value="descripcion">Descripción</option>
                            <option value="exterior_interior">Exterior / Interior</option>
                            <option value="image_gallery">Galería de Imágenes</option>
                            <option value="specs_tabla">Tabla de Specs</option>
                            <option value="video">Video</option>
                            <option value="cta_whatsapp">CTA WhatsApp</option>
                            <option value="calculadora">Calculadora</option>
                            <option value="autos_relacionados">Autos Relacionados</option>
                            <option value="custom_html">HTML Personalizado</option>
                        </select>
                        <button class="btn btn-sm btn-primary" onclick="addComponent()" style="margin-left: 8px;"><i class="fas fa-plus"></i> Agregar</button>
                    </div>
                </div>
            </div>

            <div class="form-section" id="section-videos">
                <div class="section-header" onclick="toggleSection('section-videos')">
                    <i class="fas fa-play-circle"></i>
                    <h2>Videos</h2>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="section-body">
                    <div id="videos-container"></div>
                    <button class="btn btn-secondary btn-sm" onclick="addVideoRow()" style="margin-top: 10px;"><i class="fas fa-plus"></i> Agregar Video</button>
                </div>
            </div>
        </div>
    </div>

    <div class="auto-form-right" id="preview-panel">
        <div class="preview-header">
            <h3><i class="fas fa-eye"></i> Vista Previa en Vivo</h3>
            <div class="preview-actions">
                <?php if ($mode === 'edit' && $existing_car): ?>
                <a href="<?= $base_url ?>auto/<?= htmlspecialchars($existing_car['slug']) ?>?preview=1" target="_blank" class="btn btn-sm btn-secondary" style="color: #fff; background: rgba(255,255,255,0.15);"><i class="fas fa-external-link-alt"></i> Abrir</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="preview-frame-container">
            <div class="preview-loading" id="preview-loading">
                <i class="fas fa-spinner"></i>
                <p>Cargando vista previa...</p>
            </div>
            <iframe id="preview-iframe" style="display: none;" onload="document.getElementById('preview-loading').style.display='none'; this.style.display='block';"></iframe>
        </div>
    </div>
</div>

<div class="config-panel-overlay" id="config-overlay" onclick="closeConfigPanel()"></div>
<div class="config-panel" id="config-panel">
    <div class="config-panel-header">
        <h3 id="config-panel-title">Configurar Componente</h3>
        <button class="btn btn-sm btn-secondary" onclick="closeConfigPanel()"><i class="fas fa-times"></i></button>
    </div>
    <div class="config-panel-body" id="config-panel-body"></div>
    <div class="config-panel-footer">
        <button class="btn btn-secondary" onclick="closeConfigPanel()">Cancelar</button>
        <button class="btn btn-primary" onclick="saveComponentConfig()"><i class="fas fa-save"></i> Guardar</button>
    </div>
</div>

<div class="confirm-overlay" id="confirm-overlay"></div>
<div class="confirm-modal" id="confirm-modal">
    <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <p id="confirm-message">¿Estás seguro?</p>
    <div class="confirm-actions">
        <button class="btn btn-secondary" id="confirm-no"><i class="fas fa-times"></i> No</button>
        <button class="btn btn-danger" id="confirm-yes"><i class="fas fa-check"></i> Sí</button>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
window.baseAppUrl = '<?= $base_url ?>';
window.specFieldsData = <?= json_encode($spec_fields) ?>;
window.defaultComponents = <?= json_encode($default_components) ?>;
window.existingCar = <?= $existing_car ? json_encode($existing_car) : 'null' ?>;
</script>
<script src="<?= $base_url ?>admin/assets/js/auto-form.js?v=<?= time() ?>"></script>
</body>
</html>
