(function() {
    'use strict';

    const baseUrl = window.baseAppUrl || '/';
    const carData = window.carData || {};
    let lightboxImages = [];
    let lightboxIndex = 0;

    function init() {
        initLightbox();
        initCalculator();
        loadRelatedCars();
        initAppointmentModal();
        initExteriorInteriorTabs();
    }

    function initLightbox() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        lightboxImages = Array.from(galleryItems).map(item => {
            const img = item.querySelector('img');
            return img ? img.src : '';
        }).filter(Boolean);

        window.openLightbox = function(src) {
            lightboxIndex = lightboxImages.indexOf(src);
            if (lightboxIndex === -1) lightboxIndex = 0;
            showLightboxImage();
            document.getElementById('image-lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        window.closeLightbox = function() {
            document.getElementById('image-lightbox').classList.remove('active');
            document.body.style.overflow = '';
        };

        window.navigateLightbox = function(dir) {
            lightboxIndex = (lightboxIndex + dir + lightboxImages.length) % lightboxImages.length;
            showLightboxImage();
        };

        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('image-lightbox').classList.contains('active')) return;
            if (e.key === 'Escape') window.closeLightbox();
            if (e.key === 'ArrowLeft') window.navigateLightbox(-1);
            if (e.key === 'ArrowRight') window.navigateLightbox(1);
        });
    }

    function showLightboxImage() {
        const img = document.getElementById('lightbox-img');
        if (img && lightboxImages[lightboxIndex]) {
            img.src = lightboxImages[lightboxIndex];
        }
    }

    function initCalculator() {
        if (!document.getElementById('calc_price')) return;
        window.formatCalcInput = function(el) {
            const raw = el.value.replace(/,/g, '');
            const num = parseFloat(raw);
            if (!isNaN(num)) el.value = num.toLocaleString('en-US');
        };

        window.getCalcValue = function(id) {
            const el = document.getElementById(id);
            return parseFloat(el.value.replace(/,/g, '')) || 0;
        };

        window.updateCalculator = function() {
            const price = window.getCalcValue('calc_price');
            const downpayment = window.getCalcValue('calc_downpayment');
            const apr = parseFloat(document.getElementById('calc_apr').value) || 0;
            const term = parseInt(document.getElementById('calc_term').value) || 12;
            const principal = price - downpayment;

            if (principal <= 0) {
                document.getElementById('res_monthly').innerText = '$0.00';
                document.getElementById('res_principal').innerText = '$0.00';
                document.getElementById('res_interest').innerText = '$0.00';
                document.getElementById('res_total').innerText = '$0.00';
                document.getElementById('bar_principal').style.width = '100%';
                document.getElementById('bar_interest').style.width = '0%';
                return;
            }

            const r = (apr / 100) / 12;
            const n = term;
            let monthly = 0, totalInterest = 0;

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
        };

        window.updateCalculator();
    }

    function loadRelatedCars() {
        const grid = document.getElementById('related-cars-grid');
        if (!grid) return;

        fetch(baseUrl + 'api/cars.php?action=list&limit=4')
            .then(res => res.json())
            .then(data => {
                if (!data.items || data.items.length === 0) {
                    grid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#64748b;">No hay autos relacionados.</p>';
                    return;
                }

                const related = data.items.filter(c => c.id !== carData.id).slice(0, 4);
                if (related.length === 0) {
                    grid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#64748b;">No hay autos relacionados.</p>';
                    return;
                }

                grid.innerHTML = related.map(car => {
                    const priceFormatted = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(car.price);
                    return `
                        <a href="${baseUrl}auto/${car.slug}" class="catalog-card">
                            <div class="catalog-card-img">
                                <img src="${car.image_path || ''}" alt="${escapeHtml(car.title)}" loading="lazy">
                            </div>
                            <div class="catalog-card-body">
                                <div class="catalog-card-title">${escapeHtml(car.title)}</div>
                                <div class="catalog-card-price">${priceFormatted}</div>
                            </div>
                        </a>
                    `;
                }).join('');
            })
            .catch(() => {
                grid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#64748b;">Error al cargar autos relacionados.</p>';
            });
    }

    function initAppointmentModal() {
        window.openAppointmentModal = function() {
            document.getElementById('appointmentModal').style.display = 'flex';
            initFlatpickr();
        };

        window.closeAppointmentModal = function() {
            document.getElementById('appointmentModal').style.display = 'none';
            document.getElementById('appointmentForm').reset();
            document.getElementById('appt_date').value = '';
            document.getElementById('appt_time').value = '';
            document.getElementById('slots-container').innerHTML = `
                <p id="slots-placeholder" style="grid-column: span 2; color: #94a3b8; font-size: 0.85rem; text-align: center; margin: 40px auto 0 auto;">Selecciona un día disponible.</p>
            `;
            if (window.fpInstance) { window.fpInstance.destroy(); window.fpInstance = null; }
            document.getElementById('appointmentMessage').style.display = 'none';
        };

        window.closeAppointmentModal = window.closeAppointmentModal;

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('appointmentModal');
            if (event.target === modal) window.closeAppointmentModal();
        });
    }

    function initFlatpickr() {
        fetch(baseUrl + 'api/appointments.php?action=available_dates')
            .then(res => res.json())
            .then(data => {
                const availableDates = data.dates || [];
                window.fpInstance = flatpickr('#calendar-inline', {
                    inline: true,
                    locale: 'es',
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    enable: [function(date) {
                        const dStr = flatpickr.formatDate(date, 'Y-m-d');
                        return availableDates.includes(dStr);
                    }],
                    onChange: function(selectedDates, dateStr) {
                        document.getElementById('appt_date').value = dateStr;
                        fetchAvailableSlots(dateStr);
                    }
                });
            })
            .catch(err => console.error('Error initializing calendar:', err));
    }

    function fetchAvailableSlots(date) {
        const slotsContainer = document.getElementById('slots-container');
        document.getElementById('appt_time').value = '';
        slotsContainer.innerHTML = '<p style="grid-column: span 2; color: #666; font-size: 0.85rem; text-align: center; margin-top: 30px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';

        fetch(baseUrl + `api/appointments.php?action=available_slots&date=${date}`)
            .then(res => res.json())
            .then(data => {
                slotsContainer.innerHTML = '';
                if (data.slots && data.slots.length > 0) {
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
                            padding: 12px 10px; border: 1px solid #e2e8f0; border-radius: 10px;
                            background: #fff; color: #334155; font-size: 0.85rem; font-weight: 600;
                            cursor: pointer; transition: all 0.2s ease; text-align: center;
                        `;

                        btn.addEventListener('click', function() {
                            document.querySelectorAll('.time-slot-btn').forEach(b => {
                                b.style.background = '#fff'; b.style.color = '#334155'; b.style.borderColor = '#e2e8f0';
                            });
                            btn.style.background = 'var(--primary-color)';
                            btn.style.color = '#fff';
                            btn.style.borderColor = 'var(--primary-color)';
                            document.getElementById('appt_time').value = slot;
                        });

                        slotsContainer.appendChild(btn);
                    });
                } else {
                    slotsContainer.innerHTML = '<p style="grid-column: span 2; color: #dc2626; font-size: 0.85rem; text-align: center; margin-top: 30px; font-weight:600;">Sin horarios disponibles.</p>';
                }
            })
            .catch(() => {
                slotsContainer.innerHTML = '<p style="grid-column: span 2; color: #dc2626; font-size: 0.85rem; text-align: center; margin-top: 30px;">Error al cargar horarios.</p>';
            });
    }

    window.submitAppointment = function(e) {
        e.preventDefault();
        const dateVal = document.getElementById('appt_date').value;
        const timeVal = document.getElementById('appt_time').value;

        if (!dateVal || !timeVal) {
            const msgDiv = document.getElementById('appointmentMessage');
            msgDiv.style.display = 'block';
            msgDiv.style.backgroundColor = '#f8d7da';
            msgDiv.style.color = '#721c24';
            msgDiv.innerText = 'Por favor, selecciona un día y una hora.';
            return;
        }

        const formData = new FormData();
        formData.append('action', 'book');
        formData.append('first_name', document.getElementById('appt_first_name').value);
        formData.append('last_name', document.getElementById('appt_last_name').value);
        formData.append('phone', document.getElementById('appt_phone').value);
        formData.append('appointment_date', dateVal);
        formData.append('appointment_time', timeVal);

        fetch(baseUrl + 'api/appointments.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                const msgDiv = document.getElementById('appointmentMessage');
                msgDiv.style.display = 'block';
                if (data.success) {
                    msgDiv.style.backgroundColor = '#d4edda';
                    msgDiv.style.color = '#155724';
                    msgDiv.innerText = 'Cita reservada exitosamente.';
                    document.getElementById('appointmentForm').reset();
                    document.getElementById('appt_date').value = '';
                    document.getElementById('appt_time').value = '';
                    setTimeout(window.closeAppointmentModal, 3000);
                } else {
                    msgDiv.style.backgroundColor = '#f8d7da';
                    msgDiv.style.color = '#721c24';
                    msgDiv.innerText = data.error || 'Ocurrió un error';
                }
            })
            .catch(err => console.error('Error submitting appointment:', err));
    };

    function initExteriorInteriorTabs() {
        const tabContainers = document.querySelectorAll('.ei-tabs');
        tabContainers.forEach(container => {
            const tabs = container.querySelectorAll('.ei-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabGroup = this.closest('.car-exterior-interior');
                    const target = this.dataset.target;
                    tabGroup.querySelectorAll('.ei-tab').forEach(t => t.classList.remove('active'));
                    tabGroup.querySelectorAll('.ei-content').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    const content = tabGroup.querySelector('.ei-content[data-content="' + target + '"]');
                    if (content) content.classList.add('active');
                });
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
