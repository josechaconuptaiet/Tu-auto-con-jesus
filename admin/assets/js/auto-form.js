var carData = {
    mode: '',
    carId: '',
    image_path: '',
    specs: [],
    images: [],
    components: [],
    videos: []
};

var lastSavedState = {};

var currentConfigIdx = null;
var currentConfigType = '';
var previewTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    initCarData();
    loadExistingData();
    initDragDrop();
    initGalleryDragDrop();
});

function initCarData() {
    var modeInput = document.getElementById('formMode');
    var carIdInput = document.getElementById('formCarId');

    if (modeInput) carData.mode = modeInput.value || 'create';
    if (carIdInput) carData.carId = carIdInput.value || '';

    if (!carData.carId || carData.carId === '0') {
        var pathParts = window.location.pathname.split('/');
        var idIndex = pathParts.indexOf('editar');
        if (idIndex !== -1 && pathParts[idIndex + 1]) {
            carData.carId = pathParts[idIndex + 1].replace(/[^0-9]/g, '');
        }
        if (!carData.carId || carData.carId === '0') {
            var params = new URLSearchParams(window.location.search);
            carData.carId = params.get('id') || '';
        }
    }

    if (carData.mode === 'edit' && (!carData.carId || carData.carId === '0')) {
        showToast('No se pudo identificar el auto. Volviendo al dashboard...', 'error');
        setTimeout(function() { window.location.href = window.baseAppUrl + 'admin/dashboard'; }, 2000);
        return;
    }

    if (carIdInput) carIdInput.value = carData.carId;
}

function loadExistingData() {
    if (carData.mode === 'edit' && carData.carId && carData.carId !== '0') {
        fetch(window.baseAppUrl + 'api/get_car_full.php?id=' + carData.carId + '&_=' + Date.now())
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.car) {
                    carData.image_path = data.car.image_path || '';
                    carData.specs = data.car.specs || [];
                    carData.images = data.car.images || [];
                    carData.components = data.car.components || [];
                    carData.videos = data.car.videos || [];
                    if (carData.components.length === 0 && window.defaultComponents) {
                        window.defaultComponents.forEach(function(c) {
                            var config = {};
                            try { config = JSON.parse(c.config || '{}'); } catch(e) {}
                            carData.components.push({
                                component_type: c.component_type,
                                config: config,
                                is_active: c.is_active == 1 || c.is_active === true
                            });
                        });
                    }
                    renderSpecs();
                    renderGallery();
                    renderComponents();
                    renderVideos();
                    captureSavedState();
                    updatePreview();
                } else if (data.error) {
                    showToast(data.error, 'error');
                    initDefaults();
                }
            })
            .catch(function() {
                showToast('Error al cargar datos del auto', 'error');
                initDefaults();
            });
    } else {
        initDefaults();
    }
}

function initDefaults() {
    window.specFieldsData.forEach(function(f) {
        if (f.obligatorio == 1 || f.obligatorio === true) {
            carData.specs.push({ spec_field_id: f.id, value: '' });
        }
    });
    renderSpecs();

    if (window.defaultComponents && window.defaultComponents.length > 0) {
        window.defaultComponents.forEach(function(c) {
            var config = {};
            try { config = JSON.parse(c.config || '{}'); } catch(e) {}
            carData.components.push({
                component_type: c.component_type,
                config: config,
                is_active: c.is_active == 1 || c.is_active === true
            });
        });
    }
    renderComponents();
    renderGallery();
    renderVideos();
    captureSavedState();
}

function autoGenerateSlug() {
    var title = document.getElementById('car_title').value;
    var s = title;
    s = s.replace(/[áàäâã]/gu, 'a');
    s = s.replace(/[éèëê]/gu, 'e');
    s = s.replace(/[íìïî]/gu, 'i');
    s = s.replace(/[óòöôõ]/gu, 'o');
    s = s.replace(/[úùüûũ]/gu, 'u');
    s = s.toLowerCase().replace(/[^a-z0-9-]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('car_slug').value = s;
    schedulePreview();
    scheduleAutoSave();
}

function toggleSection(sectionId) {
    var section = document.getElementById(sectionId);
    var header = section.querySelector('.section-header');
    var body = section.querySelector('.section-body');
    header.classList.toggle('collapsed');
    body.classList.toggle('hidden');
}

function togglePreview() {
    var panel = document.getElementById('preview-panel');
    panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
}

function schedulePreview() {
    checkUnsavedChanges();
    scheduleAutoSave();
}

function updatePreview() {
    if (!carData.carId || carData.carId === '0' || carData.carId === '') return;
    var iframe = document.getElementById('preview-iframe');
    var loading = document.getElementById('preview-loading');
    if (loading) loading.style.display = 'block';
    if (iframe) {
        var scrollY = 0;
        try { scrollY = iframe.contentWindow.scrollY || 0; } catch(e) {}
        iframe.style.display = 'none';
        var slug = document.getElementById('car_slug').value || 'preview';
        iframe.src = window.baseAppUrl + 'auto/' + slug + '?preview=1&car_id=' + carData.carId + '&s=' + scrollY + '&t=' + Date.now();
    }
}

function captureSavedState() {
    lastSavedState = {
        title: (document.getElementById('car_title') || {}).value || '',
        slug: (document.getElementById('car_slug') || {}).value || '',
        marca_id: (document.getElementById('car_marca_id') || {}).value || '',
        modelo: (document.getElementById('car_modelo') || {}).value || '',
        price: (document.getElementById('car_price') || {}).value || '',
        description: (document.getElementById('car_description') || {}).value || '',
        status: (document.getElementById('car_status') || {}).value || 'active',
        featured: (document.getElementById('car_featured') || {}).checked || false,
        image_path: carData.image_path || '',
        _images: JSON.stringify(carData.images || []),
        _specs: JSON.stringify(carData.specs || []),
        _components: JSON.stringify(carData.components || []),
        _videos: JSON.stringify(carData.videos || []),
    };
    checkUnsavedChanges();
}

function checkUnsavedChanges() {
    var badge = document.getElementById('unsaved-badge');
    if (!badge) return;
    var changed =
        ((document.getElementById('car_title') || {}).value || '') !== lastSavedState.title ||
        ((document.getElementById('car_slug') || {}).value || '') !== lastSavedState.slug ||
        ((document.getElementById('car_marca_id') || {}).value || '') !== lastSavedState.marca_id ||
        ((document.getElementById('car_modelo') || {}).value || '') !== lastSavedState.modelo ||
        ((document.getElementById('car_price') || {}).value || '') !== lastSavedState.price ||
        ((document.getElementById('car_description') || {}).value || '') !== lastSavedState.description ||
        ((document.getElementById('car_status') || {}).value || 'active') !== lastSavedState.status ||
        ((document.getElementById('car_featured') || {}).checked || false) !== lastSavedState.featured ||
        carData.image_path !== lastSavedState.image_path ||
        JSON.stringify(carData.images || []) !== lastSavedState._images ||
        JSON.stringify(carData.specs || []) !== lastSavedState._specs ||
        JSON.stringify(carData.components || []) !== lastSavedState._components ||
        JSON.stringify(carData.videos || []) !== lastSavedState._videos;
    badge.style.display = changed ? 'flex' : 'none';
}

var autoSaveTimer = null;

function scheduleAutoSave() {
    if (autoSaveTimer) clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        autoSaveTimer = null;
        performAutoSave();
    }, 300);
}

function performAutoSave() {
    var carId = carData.carId;
    var isNew = !carId || carId === '0' || carId === '';
    var title = document.getElementById('car_title').value.trim();
    var slug = document.getElementById('car_slug').value.trim();
    var marcaId = parseInt(document.getElementById('car_marca_id').value) || 0;
    var price = parseFloat(document.getElementById('car_price').value) || 0;

    if (isNew && (!title || !slug || marcaId === 0 || price === 0)) return;

    saveAll({ silent: true });
}

function showToast(msg, type) {
    type = type || 'success';
    var toast = document.getElementById('toast');
    if (!toast) return;
    toast.className = 'toast ' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + '"></i> ' + msg;
    toast.classList.add('show');
    setTimeout(function() { toast.classList.remove('show'); }, 3000);
}

function saveAll(options) {
    options = options || {};
    var silent = options.silent || false;
    var mode = carData.mode;
    var carId = carData.carId;

    if (mode === 'edit' && (!carId || carId === '0')) {
        if (!silent) showToast('Error: No se puede guardar, ID del auto no válido', 'error');
        return;
    }

    var btn = document.getElementById('btnSave');
    if (btn && !silent) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    }

    var payload = {
        mode: 'save_all',
        car_id: mode === 'edit' ? parseInt(carId) : 0,
        title: document.getElementById('car_title').value,
        slug: document.getElementById('car_slug').value,
        marca_id: parseInt(document.getElementById('car_marca_id').value) || 0,
        modelo: document.getElementById('car_modelo').value,
        price: parseFloat(document.getElementById('car_price').value) || 0,
        description: document.getElementById('car_description').value,
        status: document.getElementById('car_status').value,
        featured: document.getElementById('car_featured').checked,
        image_path: carData.image_path,
        specs: carData.specs,
        components: carData.components,
        images: carData.images,
        videos: carData.videos
    };

    fetch(window.baseAppUrl + 'api/save_car_full.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (btn && !silent) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar'; }
        if (!data.success) {
            if (!silent) showToast(data.error || 'Error al guardar', 'error');
            return;
        }

        if (!silent) showToast(data.message || 'Guardado exitosamente');

        if (mode === 'create') {
            carData.carId = data.car_id.toString();
            carData.mode = 'edit';
            var carIdInput = document.getElementById('formCarId');
            var modeInput = document.getElementById('formMode');
            if (carIdInput) carIdInput.value = carData.carId;
            if (modeInput) modeInput.value = 'edit';
        }

        // Update preview after save to show actual saved data
        captureSavedState();
        updatePreview();

        if (mode === 'create' && !silent) {
            setTimeout(function() {
                window.location.href = window.baseAppUrl + 'admin/auto/editar/' + carData.carId + '?id=' + carData.carId;
            }, 1500);
        }
    })
    .catch(function() {
        if (!silent) showToast('Error de conexión', 'error');
        if (btn && !silent) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar'; }
    });
}

var uploadGalleryBusy = false;

function resizeImage(file, maxDim) {
    return new Promise(function(resolve) {
        if (file.type !== 'image/jpeg' && file.type !== 'image/png' && file.type !== 'image/webp') {
            resolve(file);
            return;
        }
        var img = new Image();
        var url = URL.createObjectURL(file);
        img.onload = function() {
            URL.revokeObjectURL(url);
            var w = img.width, h = img.height;
            if (w <= maxDim && h <= maxDim) {
                resolve(file);
                return;
            }
            var ratio = Math.min(maxDim / w, maxDim / h);
            var nw = Math.round(w * ratio), nh = Math.round(h * ratio);
            var canvas = document.createElement('canvas');
            canvas.width = nw;
            canvas.height = nh;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, nw, nh);
            canvas.toBlob(function(blob) {
                if (!blob) { resolve(file); return; }
                var resized = new File([blob], file.name, { type: file.type, lastModified: file.lastModified });
                resolve(resized);
            }, file.type, 0.85);
        };
        img.onerror = function() { resolve(file); };
        img.src = url;
    });
}

function showUploadOverlay(status) {
    var overlay = document.getElementById('upload-overlay');
    if (!overlay) return;
    var statusEl = document.getElementById('upload-status');
    var bar = document.getElementById('upload-progress');
    var pct = document.getElementById('upload-percent');
    if (status === false) {
        overlay.style.display = 'none';
        return;
    }
    overlay.style.display = 'flex';
    if (statusEl) statusEl.textContent = status || 'Subiendo imagen...';
    if (bar) bar.style.width = '0%';
    if (pct) pct.textContent = '0%';
}

function setUploadProgress(pct) {
    var bar = document.getElementById('upload-progress');
    var pctEl = document.getElementById('upload-percent');
    if (bar) bar.style.width = pct + '%';
    if (pctEl) pctEl.textContent = pct + '%';
}

function setGalleryProgress(pct, text) {
    var wrap = document.getElementById('gallery-progress-wrap');
    var bar = document.getElementById('gallery-progress');
    var txt = document.getElementById('gallery-progress-text');
    var zoneText = document.getElementById('gallery-upload-text');
    if (pct === false) {
        if (wrap) wrap.style.display = 'none';
        if (zoneText) zoneText.textContent = 'Haz clic o arrastra imágenes aquí';
        return;
    }
    if (wrap) wrap.style.display = 'block';
    if (bar) bar.style.width = pct + '%';
    if (txt) txt.textContent = text || pct + '%';
    if (zoneText && text) zoneText.textContent = text;
}

function uploadPrimaryImage(input) {
    if (!input.files || !input.files[0]) return;
    showUploadOverlay('Comprimiendo imagen...');

    resizeImage(input.files[0], 1920).then(function(resized) {
        showUploadOverlay('Subiendo imagen...');
        var fd = new FormData();
        fd.append('action', 'upload_image');
        fd.append('car_id', '0');
        fd.append('image', resized);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.baseAppUrl + 'api/car_media.php', true);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var pct = Math.round((e.loaded / e.total) * 100);
                setUploadProgress(pct);
            }
        };

        xhr.onload = function() {
            showUploadOverlay(false);
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    carData.image_path = data.path;
                    var preview = document.getElementById('primary-image-preview');
                    if (preview) preview.innerHTML = '<img src="' + imgUrl(data.path) + '" style="max-width: 200px; border-radius: 8px;">';
                    showToast('Imagen principal subida');
                    schedulePreview();
                    scheduleAutoSave();
                } else {
                    showToast(data.error || 'Error al subir imagen', 'error');
                }
            } catch(e) {
                showToast('Error al procesar la respuesta del servidor', 'error');
            }
            input.value = '';
        };

        xhr.onerror = function() {
            showUploadOverlay(false);
            showToast('Error de conexión al subir imagen. Verifica que el servidor acepte archivos.', 'error');
            input.value = '';
        };

        xhr.ontimeout = function() {
            showUploadOverlay(false);
            showToast('La subida tardó demasiado. Intenta con una imagen más pequeña.', 'error');
            input.value = '';
        };

        xhr.timeout = 120000;
        xhr.send(fd);
    });
}

function uploadGalleryImages(input) {
    if (!input.files || input.files.length === 0) return;
    if (uploadGalleryBusy) return;
    uploadGalleryBusy = true;
    var files = Array.from(input.files);
    input.value = '';

    var total = files.length;
    var completed = 0;
    var failed = 0;
    var results = [];

    setGalleryProgress(0, 'Preparando ' + total + ' imagen(es)...');

    function uploadNext(idx) {
        if (idx >= files.length) {
            uploadGalleryBusy = false;
            setGalleryProgress(false);
            renderGallery();
            if (failed === 0) {
                showToast(total + ' imagen(es) subida(s)');
            } else {
                showToast((total - failed) + ' subida(s), ' + failed + ' fallida(s)', 'error');
            }
            schedulePreview();
            scheduleAutoSave();
            return;
        }

        setGalleryProgress(Math.round((idx / total) * 100), 'Procesando ' + (idx + 1) + ' de ' + total + '...');

        resizeImage(files[idx], 1920).then(function(resized) {
            var fd = new FormData();
            fd.append('action', 'upload_image');
            fd.append('car_id', '0');
            fd.append('image', resized);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.baseAppUrl + 'api/car_media.php', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var filePct = Math.round((e.loaded / e.total) * 100);
                    var overallPct = Math.round(((idx + filePct / 100) / total) * 100);
                    setGalleryProgress(overallPct, 'Subiendo ' + (idx + 1) + ' de ' + total + ' (' + filePct + '%)');
                }
            };

            xhr.onload = function() {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        var dup = false;
                        for (var i = 0; i < carData.images.length; i++) {
                            if (carData.images[i].image_path === data.path) { dup = true; break; }
                        }
                        if (!dup) {
                            carData.images.push({
                                id: data.id,
                                image_path: data.path,
                                is_primary: false
                            });
                        }
                    } else {
                        failed++;
                    }
                } catch(e) {
                    failed++;
                }
                completed++;
                uploadNext(idx + 1);
            };

            xhr.onerror = function() {
                failed++;
                completed++;
                uploadNext(idx + 1);
            };

            xhr.ontimeout = function() {
                failed++;
                completed++;
                uploadNext(idx + 1);
            };

            xhr.timeout = 120000;
            xhr.send(fd);
        });
    }

    uploadNext(0);
}

function imgUrl(path) {
    if (!path) return '';
    if (path.indexOf('http://') === 0 || path.indexOf('https://') === 0) return path;
    return window.baseAppUrl + path;
}

function renderGallery() {
    var container = document.getElementById('gallery-container');
    
    // Dedup carData.images by image_path
    var seen = {};
    carData.images = carData.images.filter(function(img) {
        if (seen[img.image_path]) return false;
        seen[img.image_path] = true;
        return true;
    });

    if (!carData.images || carData.images.length === 0) {
        container.innerHTML = '<p style="color: var(--text-secondary); font-size: 0.85rem; text-align: center; grid-column: 1 / -1; padding: 20px;">No hay imágenes en la galería.</p>';
        return;
    }

    container.innerHTML = carData.images.map(function(img, idx) {
        return '<div class="gallery-item' + (img.is_primary ? ' is-primary' : '') + '" draggable="true" data-idx="' + idx + '">' +
            '<img src="' + imgUrl(img.image_path) + '" alt="gallery">' +
            (img.is_primary ? '<span class="primary-badge"><i class="fas fa-star"></i> Principal</span>' : '') +
            '<div class="gallery-overlay">' +
                '<button class="set-primary" onclick="setPrimaryImage(' + idx + ')" title="Establecer como principal"><i class="fas fa-star"></i></button>' +
                '<button class="delete-img" onclick="deleteGalleryImage(' + idx + ')" title="Eliminar"><i class="fas fa-trash"></i></button>' +
            '</div>' +
        '</div>';
    }).join('');

    initGalleryDragDrop();
}

function setPrimaryImage(idx) {
    carData.images.forEach(function(img, i) {
        img.is_primary = (i === idx);
    });
    renderGallery();
    schedulePreview();
    scheduleAutoSave();
}

function deleteGalleryImage(idx) {
    showConfirm('¿Eliminar esta imagen?', function() {
        var img = carData.images[idx];
        if (img && img.id) {
            var fd = new FormData();
            fd.append('action', 'delete_image');
            fd.append('id', img.id);
            fetch(window.baseAppUrl + 'api/car_media.php', { method: 'POST', body: fd });
        }
        carData.images.splice(idx, 1);
        renderGallery();
        schedulePreview();
        scheduleAutoSave();
    });
}

function initGalleryDragDrop() {
    var container = document.getElementById('gallery-container');
    var dragIdx = null;

    container.querySelectorAll('.gallery-item').forEach(function(item) {
        item.addEventListener('dragstart', function(e) {
            dragIdx = parseInt(this.dataset.idx);
            this.style.opacity = '0.5';
        });
        item.addEventListener('dragend', function() {
            this.style.opacity = '1';
        });
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            var dropIdx = parseInt(this.dataset.idx);
            if (dragIdx !== null && dragIdx !== dropIdx) {
                var moved = carData.images.splice(dragIdx, 1)[0];
                carData.images.splice(dropIdx, 0, moved);
                renderGallery();
                schedulePreview();
                scheduleAutoSave();
            }
        });
    });
}

function renderSpecs() {
    var container = document.getElementById('specs-container');
    container.innerHTML = '';

    carData.specs.forEach(function(spec, idx) {
        if (spec.spec_field_id) {
            var row = document.createElement('div');
            row.className = 'spec-row';
            var options = window.specFieldsData.map(function(f) {
                return '<option value="' + f.id + '"' + (spec.spec_field_id == f.id ? ' selected' : '') + '>' + f.nombre + '</option>';
            }).join('');
            row.innerHTML = '<select onchange="carData.specs[' + idx + '].spec_field_id=parseInt(this.value); schedulePreview(); scheduleAutoSave();">' +
                '<option value="">-- Campo --</option>' + options + '</select>' +
                '<input type="text" placeholder="Valor" value="' + (spec.value || '').replace(/"/g, '&quot;') + '" oninput="carData.specs[' + idx + '].value=this.value; schedulePreview(); scheduleAutoSave();">' +
                '<button type="button" class="btn-remove" onclick="removeSpec(' + idx + ')"><i class="fas fa-times"></i></button>';
            container.appendChild(row);
        } else {
            var row = document.createElement('div');
            row.className = 'spec-row';
            row.innerHTML = '<input type="text" placeholder="Etiqueta" value="' + (spec.label || '').replace(/"/g, '&quot;') + '" oninput="carData.specs[' + idx + '].label=this.value; schedulePreview(); scheduleAutoSave();">' +
                '<input type="text" placeholder="Valor" value="' + (spec.value || '').replace(/"/g, '&quot;') + '" oninput="carData.specs[' + idx + '].value=this.value; schedulePreview(); scheduleAutoSave();">' +
                '<button type="button" class="btn-remove" onclick="removeSpec(' + idx + ')"><i class="fas fa-times"></i></button>';
            container.appendChild(row);
        }
    });
}

function addSpecFieldRow() {
    carData.specs.push({ spec_field_id: null, value: '' });
    renderSpecs();
    schedulePreview();
    scheduleAutoSave();
}

function addCustomSpecRow() {
    carData.specs.push({ label: '', value: '' });
    renderSpecs();
    schedulePreview();
    scheduleAutoSave();
}

function removeSpec(idx) {
    carData.specs.splice(idx, 1);
    renderSpecs();
    schedulePreview();
    scheduleAutoSave();
}

var componentNames = {
    hero_slider: 'Hero Slider',
    specs_destacadas: 'Specs Destacadas',
    descripcion: 'Descripción',
    exterior_interior: 'Exterior / Interior',
    image_gallery: 'Galería de Imágenes',
    specs_tabla: 'Tabla de Specs',
    video: 'Video',
    cta_whatsapp: 'CTA WhatsApp',
    calculadora: 'Calculadora',
    autos_relacionados: 'Autos Relacionados',
    custom_html: 'HTML Personalizado'
};

var componentIcons = {
    hero_slider: 'fa-image',
    specs_destacadas: 'fa-tachometer-alt',
    descripcion: 'fa-file-alt',
    exterior_interior: 'fa-car-side',
    image_gallery: 'fa-images',
    specs_tabla: 'fa-table',
    video: 'fa-play-circle',
    cta_whatsapp: 'fa-whatsapp',
    calculadora: 'fa-calculator',
    autos_relacionados: 'fa-car',
    custom_html: 'fa-code'
};

var componentsWithConfig = ['hero_slider', 'specs_destacadas', 'descripcion', 'exterior_interior', 'image_gallery', 'autos_relacionados', 'custom_html'];

function renderComponents() {
    var container = document.getElementById('components-container');
    if (!carData.components || carData.components.length === 0) {
        container.innerHTML = '<p style="color: var(--text-secondary); font-size: 0.85rem; text-align: center; padding: 20px;">No hay componentes. Agrega uno desde el selector de abajo.</p>';
        return;
    }

    container.innerHTML = carData.components.map(function(comp, idx) {
        var name = componentNames[comp.component_type] || comp.component_type;
        var icon = componentIcons[comp.component_type] || 'fa-puzzle-piece';
        var hasConfig = componentsWithConfig.indexOf(comp.component_type) !== -1;
        var isActive = comp.is_active;

        return '<div class="component-item" draggable="true" data-idx="' + idx + '">' +
            '<i class="fas fa-grip-vertical drag-handle"></i>' +
            '<div class="comp-icon"><i class="fas ' + icon + '"></i></div>' +
            '<div class="comp-info"><strong>' + name + '</strong><small>' + comp.component_type + '</small></div>' +
            '<div class="comp-actions">' +
                '<div class="toggle-switch' + (isActive ? ' active' : '') + '" onclick="toggleComponent(' + idx + ')"></div>' +
                (hasConfig ? '<button class="btn btn-sm btn-warning" onclick="openComponentConfig(' + idx + ')"><i class="fas fa-cog"></i></button>' : '') +
                '<button class="btn btn-sm btn-danger" onclick="removeComponent(' + idx + ')"><i class="fas fa-trash"></i></button>' +
            '</div>' +
        '</div>';
    }).join('');

    initDragDrop();
}

function toggleComponent(idx) {
    carData.components[idx].is_active = !carData.components[idx].is_active;
    renderComponents();
    schedulePreview();
    scheduleAutoSave();
}

function removeComponent(idx) {
    showConfirm('¿Eliminar este componente?', function() {
        carData.components.splice(idx, 1);
        renderComponents();
        schedulePreview();
        scheduleAutoSave();
    });
}

function addComponent() {
    var select = document.getElementById('new-component-type');
    var type = select.value;
    if (!type) return;

    var defaultConfig = {};
    if (type === 'hero_slider') { defaultConfig.show_title = true; defaultConfig.show_price = true; }
    else if (type === 'specs_destacadas') { defaultConfig.max_items = 6; }
    else if (type === 'descripcion') { defaultConfig.image_position = 'left'; }
    else if (type === 'exterior_interior') { defaultConfig.exterior_title = 'Exterior'; defaultConfig.interior_title = 'Interior'; }
    else if (type === 'image_gallery') { defaultConfig.layout = 'grid'; }
    else if (type === 'autos_relacionados') { defaultConfig.max_items = 4; }
    else if (type === 'custom_html') { defaultConfig.html = '<div style="padding: 40px; text-align: center;"><h2>Tu Contenido Aquí</h2><p>Edita este componente para agregar tu HTML personalizado.</p></div>'; defaultConfig.css = ''; defaultConfig.js = ''; defaultConfig.images = []; }

    carData.components.push({
        component_type: type,
        config: defaultConfig,
        is_active: false
    });

    select.value = '';
    renderComponents();
    schedulePreview();
    scheduleAutoSave();
}

function initDragDrop() {
    var container = document.getElementById('components-container');
    var dragIdx = null;

    container.querySelectorAll('.component-item').forEach(function(item) {
        item.addEventListener('dragstart', function() {
            dragIdx = parseInt(this.dataset.idx);
            this.classList.add('dragging');
        });
        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
        item.addEventListener('dragover', function(e) { e.preventDefault(); });
        item.addEventListener('dragenter', function(e) {
            e.preventDefault();
            if (dragIdx !== null) {
                var dropIdx = parseInt(this.dataset.idx);
                    if (dragIdx !== dropIdx) {
                        var moved = carData.components.splice(dragIdx, 1)[0];
                        carData.components.splice(dropIdx, 0, moved);
                        renderComponents();
                        schedulePreview();
                        scheduleAutoSave();
                    }
            }
        });
    });
}

function openComponentConfig(idx) {
    currentConfigIdx = idx;
    currentConfigType = carData.components[idx].component_type;
    var comp = carData.components[idx];
    var config = comp.config || {};

    var title = componentNames[currentConfigType] || currentConfigType;
    document.getElementById('config-panel-title').textContent = 'Configurar: ' + title;

    var body = document.getElementById('config-panel-body');
    var html = '';

    if (currentConfigType === 'hero_slider') {
        html += '<div class="form-group"><label class="checkbox-label"><input type="checkbox" id="cfg_show_title"' + (config.show_title !== false ? ' checked' : '') + '> Mostrar Título</label></div>';
        html += '<div class="form-group"><label class="checkbox-label"><input type="checkbox" id="cfg_show_price"' + (config.show_price !== false ? ' checked' : '') + '> Mostrar Precio</label></div>';
        html += '<div class="form-group"><label>Imagen de Fondo (opcional)</label>' + renderCompImageUpload('hero_slider', 'background', config.background) + '</div>';
    } else if (currentConfigType === 'specs_destacadas') {
        html += '<div class="form-group"><label>Máx. items</label><input type="number" id="cfg_max_items" value="' + (config.max_items || 6) + '" min="1" max="12"></div>';
    } else if (currentConfigType === 'descripcion') {
        html += '<div class="form-group"><label>Imagen</label>' + renderCompImageUpload('descripcion', 'image', config.image) + '</div>';
        html += '<div class="form-group"><label>Posición de imagen</label><select id="cfg_image_position"><option value="left"' + (config.image_position !== 'right' ? ' selected' : '') + '>Izquierda</option><option value="right"' + (config.image_position === 'right' ? ' selected' : '') + '>Derecha</option></select></div>';
    } else if (currentConfigType === 'exterior_interior') {
        html += '<div class="form-row-2col">';
        html += '<div><h4 style="margin: 0 0 15px; color: var(--primary-color);">Exterior</h4>';
        html += '<div class="form-group"><label>Título</label><input type="text" id="cfg_exterior_title" value="' + escHtml(config.exterior_title || 'Exterior') + '"></div>';
        html += '<div class="form-group"><label>Descripción</label><textarea id="cfg_exterior_description" rows="3">' + escHtml(config.exterior_description || '') + '</textarea></div>';
        html += '<div class="form-group"><label>Imagen</label>' + renderCompImageUpload('exterior_interior', 'exterior_image', config.exterior_image) + '</div></div>';
        html += '<div><h4 style="margin: 0 0 15px; color: var(--primary-color);">Interior</h4>';
        html += '<div class="form-group"><label>Título</label><input type="text" id="cfg_interior_title" value="' + escHtml(config.interior_title || 'Interior') + '"></div>';
        html += '<div class="form-group"><label>Descripción</label><textarea id="cfg_interior_description" rows="3">' + escHtml(config.interior_description || '') + '</textarea></div>';
        html += '<div class="form-group"><label>Imagen</label>' + renderCompImageUpload('exterior_interior', 'interior_image', config.interior_image) + '</div></div>';
        html += '</div>';
    } else if (currentConfigType === 'image_gallery') {
        html += '<div class="form-group"><label>Layout</label><select id="cfg_layout"><option value="grid"' + (config.layout !== 'masonry' ? ' selected' : '') + '>Grid</option><option value="masonry"' + (config.layout === 'masonry' ? ' selected' : '') + '>Masonry</option></select></div>';
        html += '<div class="form-group"><label>Imagen de Encabezado (opcional)</label>' + renderCompImageUpload('image_gallery', 'header_image', config.header_image) + '</div>';
    } else if (currentConfigType === 'autos_relacionados') {
        html += '<div class="form-group"><label>Máx. autos</label><input type="number" id="cfg_max_items" value="' + (config.max_items || 4) + '" min="1" max="8"></div>';
        html += '<div class="form-group"><label>Imagen de Fondo (opcional)</label>' + renderCompImageUpload('autos_relacionados', 'background', config.background) + '</div>';
    } else if (currentConfigType === 'custom_html') {
        html += '<div class="form-group"><label>HTML</label><div class="code-editor-label"><label>Código HTML</label></div><textarea class="code-editor" id="cfg_html" rows="10">' + escHtml(config.html || '') + '</textarea></div>';
        html += '<div class="form-group"><label>CSS (opcional)</label><div class="code-editor-label"><label>Código CSS</label></div><textarea class="code-editor" id="cfg_css" rows="5">' + escHtml(config.css || '') + '</textarea></div>';
        html += '<div class="form-group"><label>JavaScript (opcional)</label><div class="code-editor-label"><label>Código JS</label></div><textarea class="code-editor" id="cfg_js" rows="5">' + escHtml(config.js || '') + '</textarea></div>';
        html += '<div class="form-group"><label>Imágenes del Componente</label>';
        html += '<div class="image-upload-zone" onclick="document.getElementById(\'custom-html-img-upload\').click()"><i class="fas fa-cloud-upload-alt"></i><p>Subir imagen</p><input type="file" id="custom-html-img-upload" accept="image/*" style="display:none;" onchange="uploadCustomHtmlImage(this)"></div>';
        html += '<div class="component-images-list" id="custom-html-images-list">';
        var compImages = config.images || [];
        compImages.forEach(function(imgPath, imgIdx) {
            html += '<div class="component-image-thumb"><img src="' + imgUrl(imgPath) + '" alt="img"><button class="remove-comp-img" onclick="removeCustomHtmlImage(' + imgIdx + ')"><i class="fas fa-times"></i></button></div>';
        });
        html += '</div></div>';
        html += '<div class="form-group"><label>Preview del HTML</label><div id="custom-html-preview" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; min-height: 100px; background: #f8fafc;">' + (config.html || '<p style="color: var(--text-secondary);">Sin contenido HTML</p>') + '</div></div>';
    }

    body.innerHTML = html;
    document.getElementById('config-panel').classList.add('open');
    document.getElementById('config-overlay').classList.add('open');
}

function renderCompImageUpload(compType, field, currentPath) {
    var html = '';
    if (currentPath) {
        html += '<div style="margin-bottom: 8px;"><img src="' + imgUrl(currentPath) + '" style="max-width: 150px; border-radius: 6px; margin-bottom: 6px;"><br>';
        html += '<button class="btn btn-sm btn-danger" onclick="removeCompImage(\'' + compType + '\', \'' + field + '\')"><i class="fas fa-trash"></i> Quitar</button></div>';
    }
    html += '<input type="file" accept="image/*" onchange="uploadCompImage(this, \'' + compType + '\', \'' + field + '\')">';
    return html;
}

function uploadCompImage(input, compType, field) {
    if (!input.files || !input.files[0]) return;
    var fd = new FormData();
    fd.append('action', 'upload_component_image');
    fd.append('car_id', carData.carId || '0');
    fd.append('component_type', compType);
    fd.append('field', field);
    fd.append('image', input.files[0]);

    fetch(window.baseAppUrl + 'api/car_media.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                carData.components[currentConfigIdx].config[field] = data.path;
                openComponentConfig(currentConfigIdx);
                showToast('Imagen subida');
                schedulePreview();
                scheduleAutoSave();
            }
        });
}

function removeCompImage(compType, field) {
    carData.components[currentConfigIdx].config[field] = '';
    openComponentConfig(currentConfigIdx);
    schedulePreview();
    scheduleAutoSave();
}

function uploadCustomHtmlImage(input) {
    if (!input.files || !input.files[0]) return;
    var fd = new FormData();
    fd.append('action', 'upload_component_image');
    fd.append('car_id', carData.carId || '0');
    fd.append('component_type', 'custom_html');
    fd.append('field', 'image_' + Date.now());
    fd.append('image', input.files[0]);

    fetch(window.baseAppUrl + 'api/car_media.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                if (!carData.components[currentConfigIdx].config.images) {
                    carData.components[currentConfigIdx].config.images = [];
                }
                carData.components[currentConfigIdx].config.images.push(data.path);
                openComponentConfig(currentConfigIdx);
                showToast('Imagen subida');
                scheduleAutoSave();
            }
        });

    input.value = '';
}

function removeCustomHtmlImage(imgIdx) {
    carData.components[currentConfigIdx].config.images.splice(imgIdx, 1);
    openComponentConfig(currentConfigIdx);
}

function saveComponentConfig() {
    var config = {};

    if (currentConfigType === 'hero_slider') {
        config.show_title = document.getElementById('cfg_show_title').checked;
        config.show_price = document.getElementById('cfg_show_price').checked;
        config.background = carData.components[currentConfigIdx].config.background || '';
    } else if (currentConfigType === 'specs_destacadas') {
        config.max_items = parseInt(document.getElementById('cfg_max_items').value) || 6;
    } else if (currentConfigType === 'descripcion') {
        config.image = carData.components[currentConfigIdx].config.image || '';
        config.image_position = document.getElementById('cfg_image_position').value;
    } else if (currentConfigType === 'exterior_interior') {
        config.exterior_title = document.getElementById('cfg_exterior_title').value;
        config.exterior_description = document.getElementById('cfg_exterior_description').value;
        config.exterior_image = carData.components[currentConfigIdx].config.exterior_image || '';
        config.interior_title = document.getElementById('cfg_interior_title').value;
        config.interior_description = document.getElementById('cfg_interior_description').value;
        config.interior_image = carData.components[currentConfigIdx].config.interior_image || '';
    } else if (currentConfigType === 'image_gallery') {
        config.layout = document.getElementById('cfg_layout').value;
        config.header_image = carData.components[currentConfigIdx].config.header_image || '';
    } else if (currentConfigType === 'autos_relacionados') {
        config.max_items = parseInt(document.getElementById('cfg_max_items').value) || 4;
        config.background = carData.components[currentConfigIdx].config.background || '';
    } else if (currentConfigType === 'custom_html') {
        config.html = document.getElementById('cfg_html').value;
        config.css = document.getElementById('cfg_css').value;
        config.js = document.getElementById('cfg_js').value;
        config.images = carData.components[currentConfigIdx].config.images || [];
    }

    carData.components[currentConfigIdx].config = config;
    closeConfigPanel();
    renderComponents();
    schedulePreview();
    scheduleAutoSave();
    showToast('Configuración guardada');
}

function closeConfigPanel() {
    document.getElementById('config-panel').classList.remove('open');
    document.getElementById('config-overlay').classList.remove('open');
    currentConfigIdx = null;
}

function escHtml(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function renderVideos() {
    var container = document.getElementById('videos-container');
    if (!carData.videos || carData.videos.length === 0) {
        container.innerHTML = '<p style="color: var(--text-secondary); font-size: 0.85rem;">No hay videos agregados.</p>';
        return;
    }

    container.innerHTML = carData.videos.map(function(vid, idx) {
        return '<div class="video-row">' +
            '<input type="text" placeholder="URL de YouTube" value="' + (vid.url || '').replace(/"/g, '&quot;') + '" oninput="carData.videos[' + idx + '].url=this.value; schedulePreview(); scheduleAutoSave();">' +
            '<input type="text" placeholder="Título (opcional)" value="' + (vid.titulo || '').replace(/"/g, '&quot;') + '" oninput="carData.videos[' + idx + '].titulo=this.value; schedulePreview(); scheduleAutoSave();">' +
            '<button class="btn-remove" onclick="removeVideo(' + idx + ')"><i class="fas fa-times"></i></button>' +
        '</div>';
    }).join('');
}

function addVideoRow() {
    carData.videos.push({ url: '', titulo: '' });
    renderVideos();
    schedulePreview();
    scheduleAutoSave();
}

function removeVideo(idx) {
    carData.videos.splice(idx, 1);
    renderVideos();
    schedulePreview();
    scheduleAutoSave();
}

function showConfirm(msg, onConfirm, onCancel) {
    var overlay = document.getElementById('confirm-overlay');
    var modal = document.getElementById('confirm-modal');
    var message = document.getElementById('confirm-message');
    if (!overlay || !modal || !message) return;

    message.textContent = msg;
    overlay.classList.add('open');
    modal.classList.add('open');

    var yesBtn = document.getElementById('confirm-yes');
    var noBtn = document.getElementById('confirm-no');

    function cleanup() {
        overlay.classList.remove('open');
        modal.classList.remove('open');
        if (yesBtn) yesBtn.onclick = null;
        if (noBtn) noBtn.onclick = null;
    }

    if (yesBtn) {
        yesBtn.onclick = function() {
            cleanup();
            if (onConfirm) onConfirm();
        };
    }

    if (noBtn) {
        noBtn.onclick = function() {
            cleanup();
            if (onCancel) onCancel();
        };
    }
}
