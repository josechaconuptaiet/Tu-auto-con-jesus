<?php
require_once __DIR__ . '/../api/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . $base_url . "admin");
    exit;
}

$toast_msg = '';
$toast_type = 'success'; // success or error

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_settings') {
            $settings_to_update = ['social_facebook', 'social_instagram', 'social_twitter', 'social_youtube', 'whatsapp_number', 'whatsapp_message_template', 'calc_min_price', 'calc_max_price', 'calc_min_downpayment', 'calc_max_downpayment', 'calc_default_apr', 'calc_terms', 'appointment_window_days'];
            foreach ($settings_to_update as $key) {
                if (isset($_POST[$key])) {
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $stmt->execute([$key, $_POST[$key]]);
                }
            }

            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            if (!empty($_FILES['logo']['name'])) {
                $logo_path = 'uploads/logo_' . time() . '_' . $_FILES['logo']['name'];
                if (move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../' . $logo_path)) {
                    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'logo'");
                    $stmt->execute([$logo_path]);
                }
            }

            if (!empty($_FILES['favicon']['name'])) {
                $fav_path = 'uploads/favicon_' . time() . '_' . $_FILES['favicon']['name'];
                if (move_uploaded_file($_FILES['favicon']['tmp_name'], __DIR__ . '/../' . $fav_path)) {
                    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'favicon'");
                    $stmt->execute([$fav_path]);
                }
            }
            $toast_msg = "Configuración guardada exitosamente.";
        }

        if ($action === 'add_car') {
            $title = $_POST['title'] ?? '';
            $price = $_POST['price'] ?? 0;
            
            $image_path = '';
            if (!empty($_FILES['car_image']['name'])) {
                $img_path = 'uploads/car_' . time() . '_' . $_FILES['car_image']['name'];
                if (move_uploaded_file($_FILES['car_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $image_path = $img_path;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO cars (title, price, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$title, $price, $image_path]);
            $toast_msg = "Auto agregado exitosamente.";
        }

        if ($action === 'delete_car') {
            $id = $_POST['car_id'];
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Auto eliminado.";
        }

        if ($action === 'bulk_delete_cars') {
            $car_ids = !empty($_POST['car_ids']) ? explode(',', $_POST['car_ids']) : [];
            if (!empty($car_ids)) {
                $placeholders = implode(',', array_fill(0, count($car_ids), '?'));
                $stmt = $pdo->prepare("DELETE FROM cars WHERE id IN ($placeholders)");
                $stmt->execute($car_ids);
                $toast_msg = count($car_ids) . " autos eliminados correctamente.";
            }
        }

        if ($action === 'edit_car') {
            $id = $_POST['car_id'];
            $title = $_POST['title'] ?? '';
            $price = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';

            if (!empty($_FILES['car_image']['name'])) {
                $img_path = 'uploads/car_' . time() . '_' . $_FILES['car_image']['name'];
                if (move_uploaded_file($_FILES['car_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $image_path = $img_path;
                    $stmt = $pdo->prepare("UPDATE cars SET title = ?, price = ?, description = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$title, $price, $description, $image_path, $id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE cars SET title = ?, price = ?, description = ? WHERE id = ?");
                $stmt->execute([$title, $price, $description, $id]);
            }
            $toast_msg = "Auto actualizado exitosamente.";
        }

        if ($action === 'add_carousel') {
            if (!empty($_FILES['carousel_image']['name'])) {
                $img_path = 'uploads/hero_' . time() . '_' . $_FILES['carousel_image']['name'];
                if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $stmt = $pdo->prepare("INSERT INTO carousel_images (image_path, is_active) VALUES (?, 1)");
                    $stmt->execute([$img_path]);
                    $toast_msg = "Imagen del carrusel agregada.";
                }
            }
        }
        
        if ($action === 'delete_carousel') {
            $id = $_POST['carousel_id'];
            $stmt = $pdo->prepare("DELETE FROM carousel_images WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Imagen eliminada.";
        }

        if ($action === 'edit_carousel') {
            $id = $_POST['carousel_id'];
            if (!empty($_FILES['carousel_image']['name'])) {
                $img_path = 'uploads/hero_' . time() . '_' . $_FILES['carousel_image']['name'];
                if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $image_path = $img_path;
                    $stmt = $pdo->prepare("UPDATE carousel_images SET image_path = ? WHERE id = ?");
                    $stmt->execute([$image_path, $id]);
                    $toast_msg = "Imagen del carrusel actualizada.";
                }
            }
        }

        if ($action === 'add_weekly_schedule') {
            $day_of_week = $_POST['day_of_week'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $slot_duration = $_POST['slot_duration'] ?? 60;

            $stmt = $pdo->prepare("INSERT INTO weekly_schedule (day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?)");
            $stmt->execute([$day_of_week, $start_time, $end_time, $slot_duration]);
            $toast_msg = "Horario semanal agregado.";
        }

        if ($action === 'delete_weekly_schedule') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM weekly_schedule WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Horario semanal eliminado.";
        }

        if ($action === 'add_schedule_exception') {
            $exception_date = $_POST['exception_date'];
            $is_closed = isset($_POST['is_closed']) ? 1 : 0;
            $start_time = $_POST['start_time'] ?: null;
            $end_time = $_POST['end_time'] ?: null;
            $slot_duration = $_POST['slot_duration'] ?? 60;

            $stmt = $pdo->prepare("INSERT INTO schedule_exceptions (exception_date, start_time, end_time, slot_duration, is_closed) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE start_time = ?, end_time = ?, slot_duration = ?, is_closed = ?");
            $stmt->execute([$exception_date, $start_time, $end_time, $slot_duration, $is_closed, $start_time, $end_time, $slot_duration, $is_closed]);
            $toast_msg = "Excepción de horario guardada.";
        }

        if ($action === 'delete_schedule_exception') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM schedule_exceptions WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Excepción de horario eliminada.";
        }

        if ($action === 'edit_schedule_exception') {
            $id = $_POST['id'];
            $exception_date = $_POST['exception_date'];
            $is_closed = isset($_POST['is_closed']) ? 1 : 0;
            $start_time = $_POST['start_time'] ?: null;
            $end_time = $_POST['end_time'] ?: null;
            $slot_duration = $_POST['slot_duration'] ?? 60;

            $stmt = $pdo->prepare("UPDATE schedule_exceptions SET exception_date = ?, start_time = ?, end_time = ?, slot_duration = ?, is_closed = ? WHERE id = ?");
            $stmt->execute([$exception_date, $start_time, $end_time, $slot_duration, $is_closed, $id]);
            $toast_msg = "Excepción de horario actualizada.";
        }

        if ($action === 'update_appointment') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $toast_msg = "Estado de la cita actualizado.";
        }

        if ($action === 'delete_appointment') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Cita eliminada.";
        }
    } catch (Exception $e) {
        $toast_msg = "Ocurrió un error: " . $e->getMessage();
        $toast_type = 'error';
    }
}

// Fetch current data
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_db = $stmt->fetchAll();
$settings = [];
foreach ($settings_db as $s) { $settings[$s['setting_key']] = $s['setting_value']; }

$stmt = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
$cars = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM carousel_images");
$carousels = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC");
$appointments = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM weekly_schedule ORDER BY day_of_week ASC, start_time ASC");
$weekly_schedules = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM schedule_exceptions ORDER BY exception_date DESC");
$schedule_exceptions = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Tu Auto Con</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0B192C;
            --primary-hover: #1a365d;
            --bg-color: #f4f7f6;
            --text-dark: #333;
            --text-light: #777;
            --border: #eaedf1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar { 
            width: 260px; 
            background-color: var(--primary); 
            color: white; 
            height: 100vh; 
            position: fixed; 
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sidebar-header i { font-size: 24px; }
        .sidebar-header h2 { font-size: 1.1rem; font-weight: 700; margin: 0; }

        .sidebar ul { list-style: none; padding: 15px 0; }
        .sidebar ul li a { 
            display: flex; 
            align-items: center;
            gap: 12px;
            padding: 15px 25px; 
            color: #cbd5e1; 
            text-decoration: none; 
            transition: 0.3s; 
            font-weight: 500;
            font-size: 0.95rem;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active { 
            background-color: rgba(255,255,255,0.05); 
            color: white; 
            border-left: 4px solid #3b82f6;
        }
        .sidebar ul li a i { width: 20px; text-align: center; }

        .close-sidebar {
            display: none;
            position: absolute;
            top: 25px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        /* Main Content */
        .main-wrapper { 
            flex: 1;
            margin-left: 260px; 
            display: flex;
            flex-direction: column;
            width: calc(100% - 260px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* Topbar */
        .topbar {
            background: white;
            height: 70px;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-dark);
            cursor: pointer;
        }

        .topbar-actions a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 6px;
            transition: 0.3s;
        }
        .topbar-actions a:hover { background: var(--bg-color); }
        .topbar-actions a.logout { color: var(--danger); }
        .topbar-actions a.logout:hover { background: #fef2f2; }

        /* Content Area */
        .content { padding: 30px; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--primary); }

        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
            margin-bottom: 30px; 
            border: 1px solid var(--border);
        }
        .card h3 { 
            margin-top: 0; 
            border-bottom: 1px solid var(--border); 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
            font-size: 1.1rem;
            color: var(--primary);
        }

        /* Buttons & Forms */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary); 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 0.9rem;
            transition: 0.3s;
            text-decoration: none;
        }
        .btn:hover { background-color: var(--primary-hover); }
        .btn-danger { background-color: var(--danger); }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-success { background-color: var(--success); }
        .btn-success:hover { background-color: #059669; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #4b5563; font-size: 0.9rem; }
        input[type="text"], input[type="number"], input[type="file"], input[type="date"], input[type="time"], select, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #d1d5db; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(11,25,44,0.1); }
        textarea { resize: vertical; min-height: 100px; }
        .placeholder-hint { font-size: 0.85rem; color: #64748b; margin-top: 8px; line-height: 1.5; }
        .placeholder-hint code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; }
        
        .img-preview { max-height: 50px; border-radius: 4px; border: 1px solid #ccc; padding: 2px; margin-bottom: 10px; display: block; }

        /* Tables */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        table th { background: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        table th, table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        table tr:hover td { background-color: #f8fafc; }
        table img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pendiente { background: #fef3c7; color: #d97706; }
        .status-confirmada { background: #d1fae5; color: #059669; }
        .status-cancelada { background: #fee2e2; color: #dc2626; }

        /* Tabs */
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .modal-header h2 { margin: 0; font-size: 1.2rem; color: var(--primary); }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
        .close-btn:hover { color: #333; }

        /* Toast Notifications */
        #toast-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 300px;
            transform: translateX(120%);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { border-color: var(--success); }
        .toast.error { border-color: var(--danger); }
        .toast i { font-size: 20px; }
        .toast.success i { color: var(--success); }
        .toast.error i { color: var(--danger); }
        .toast-body { flex: 1; font-size: 0.95rem; font-weight: 500; }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; width: 100%; }
            .menu-toggle { display: block; }
            .close-sidebar { display: block; }
            .overlay {
                display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); z-index: 999;
            }
            .overlay.show { display: block; }
        }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="overlay" id="mobileOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="close-sidebar" id="closeSidebar"><i class="fas fa-times"></i></button>
        <div class="sidebar-header">
            <i class="fas fa-car"></i>
            <h2>Tu Auto Con</h2>
        </div>
        <ul>
            <li><a href="#" class="tab-link active" data-tab="tab-cars"><i class="fas fa-car-side"></i> Inventario</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-carousel"><i class="fas fa-images"></i> Carrusel</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-appointments"><i class="fas fa-calendar-check"></i> Citas</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-availability"><i class="fas fa-clock"></i> Disponibilidad</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-services"><i class="fas fa-handshake"></i> Servicios</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-settings"><i class="fas fa-cog"></i> Ajustes</a></li>
        </ul>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <div class="topbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div style="flex: 1;"></div>
            <div class="topbar-actions">
                <a href="<?= $base_url ?>" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Sitio</a>
                <a href="<?= $base_url ?>admin/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">

            <!-- Tab: Cars -->
            <div id="tab-cars" class="tab-content active">
                <div class="page-header" style="margin-bottom: 15px;">
                    <h1>Inventario de Autos</h1>
                    <button class="btn" onclick="openModal('modalAddCar')"><i class="fas fa-plus"></i> Nuevo Auto</button>
                </div>

                <!-- Filters & Search & Bulk Actions -->
                <div class="card" style="margin-bottom: 20px; padding: 15px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; background: #fff; justify-content: space-between;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" id="searchCars" placeholder="Buscar por título o descripción..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    
                    <!-- Shorthand Filters -->
                    <div style="min-width: 160px;">
                        <select id="filterShorthandCars" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer;">
                            <option value="all">Todos los Autos</option>
                            <option value="recent">Recientes</option>
                            <option value="luxury">Gama Alta (De Lujo)</option>
                            <option value="budget">Económicos</option>
                        </select>
                    </div>

                    <!-- Price Category Filter -->
                    <div style="min-width: 160px;">
                        <select id="filterPriceCars" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer;">
                            <option value="">Cualquier Precio</option>
                            <option value="50k">&lt; $50,000</option>
                            <option value="100k">&lt; $100,000</option>
                            <option value="150k">&lt; $150,000</option>
                            <option value="150k_plus">&ge; $150,000</option>
                        </select>
                    </div>
                    
                    <!-- Bulk actions bar -->
                    <div id="bulkActionsCars" style="display: none; align-items: center; gap: 10px;">
                        <span id="bulkSelectCountCars" style="font-weight: 600; color: var(--text-dark);">0 autos seleccionados</span>
                        <button class="btn btn-danger" onclick="submitBulkDeleteCars()"><i class="fas fa-trash-alt"></i> Eliminar Selección</button>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllCars" style="width: 18px; height: 18px; cursor: pointer;"></th>
                                    <th>Imagen</th>
                                    <th>Título</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="carsTbody">
                                <tr><td colspan="5" style="text-align:center;">Cargando autos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <span id="carsPaginationInfo" style="color: var(--text-light); font-size: 0.9rem;">Mostrando 0 de 0</span>
                        <div style="display: flex; gap: 10px;">
                            <button id="btnPrevCars" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);"><i class="fas fa-chevron-left"></i> Anterior</button>
                            <button id="btnNextCars" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);">Siguiente <i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario oculto para borrado masivo de autos -->
            <form id="formBulkDeleteCars" method="POST" style="display:none;">
                <input type="hidden" name="action" value="bulk_delete_cars">
                <input type="hidden" name="car_ids" id="bulkDeleteCarIds">
            </form>

            <!-- Tab: Carousel -->
            <div id="tab-carousel" class="tab-content">
                <div class="page-header">
                    <h1>Carrusel Principal</h1>
                    <button class="btn" onclick="openModal('modalAddCarousel')"><i class="fas fa-plus"></i> Subir Imagen</button>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr><th>Imagen</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <?php if(empty($carousels)): ?><tr><td colspan="2" style="text-align:center;">No hay imágenes en el carrusel.</td></tr><?php endif; ?>
                                <?php foreach($carousels as $c): ?>
                                <tr>
                                    <td><img src="<?= htmlspecialchars($c['image_path']) ?>" alt="hero" style="width: 120px;"></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta imagen?')">
                                            <input type="hidden" name="action" value="delete_carousel">
                                            <input type="hidden" name="carousel_id" value="<?= $c['id'] ?>">
                                            <button type="button" class="btn btn-warning" style="background:#f59e0b; color:white; border:none;" onclick="editCarousel(<?= $c['id'] ?>)"><i class="fas fa-edit"></i></button>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab: Services -->
            <div id="tab-services" class="tab-content">
                <div class="page-header">
                    <h1>Nuestros Servicios</h1>
                    <button class="btn" onclick="openModal('modalAddService')"><i class="fas fa-plus"></i> Añadir Servicio</button>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr><th>Icono</th><th>Título</th><th>Descripción</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <?php if(empty($services)): ?><tr><td colspan="4" style="text-align:center;">No hay servicios configurados.</td></tr><?php endif; ?>
                                <?php foreach($services as $svc): ?>
                                <tr>
                                    <td><i class="<?= htmlspecialchars($svc['icon']) ?> fa-2x"></i><br><small><?= htmlspecialchars($svc['icon']) ?></small></td>
                                    <td><strong><?= htmlspecialchars($svc['title']) ?></strong></td>
                                    <td><?= htmlspecialchars(substr($svc['description'], 0, 50)) ?>...</td>
                                    <td>
                                        <form method="POST" action="<?= $base_url ?>api/services.php" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                            <button type="button" class="btn btn-warning" style="background:#f59e0b; color:white; border:none;" onclick="editService(<?= $svc['id'] ?>, '<?= htmlspecialchars(addslashes($svc['icon']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($svc['title']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($svc['description']), ENT_QUOTES) ?>')"><i class="fas fa-edit"></i></button>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab: Appointments -->
            <div id="tab-appointments" class="tab-content">
                <div class="page-header" style="margin-bottom: 15px;">
                    <h1>Gestión de Citas</h1>
                </div>

                <!-- Filters & Search -->
                <div class="card" style="margin-bottom: 20px; padding: 15px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; background: #fff;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" id="searchAppointments" placeholder="Buscar por nombre, apellido o teléfono..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div>
                        <input type="date" id="filterDateAppointments" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div>
                        <select id="filterShorthandAppointments" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                            <option value="all">Todas las Citas</option>
                            <option value="today">Citas de Hoy</option>
                            <option value="upcoming">Próximas Citas</option>
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTbody">
                                <tr><td colspan="5" style="text-align:center;">Cargando citas...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <span id="appointmentsPaginationInfo" style="color: var(--text-light); font-size: 0.9rem;">Mostrando 0 de 0</span>
                        <div style="display: flex; gap: 10px;">
                            <button id="btnPrevAppointments" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);"><i class="fas fa-chevron-left"></i> Anterior</button>
                            <button id="btnNextAppointments" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);">Siguiente <i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Availability -->
            <div id="tab-availability" class="tab-content">
                <div class="page-header">
                    <h1>Disponibilidad y Horarios</h1>
                </div>

                <!-- Weekly Schedule -->
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                        <h3 style="border:none; margin:0;">Horario Regular Semanal</h3>
                        <button class="btn" onclick="openModal('modalAddWeekly')"><i class="fas fa-plus"></i> Agregar Turno</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Día de la Semana</th>
                                    <th>Horario</th>
                                    <th>Duración de Cita</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $days_map = [1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes', 6=>'Sábado', 7=>'Domingo'];
                                    if(empty($weekly_schedules)): 
                                ?>
                                    <tr><td colspan="4" style="text-align:center;">No hay horarios semanales configurados.</td></tr>
                                <?php endif; ?>
                                <?php foreach($weekly_schedules as $ws): ?>
                                <tr>
                                    <td><strong><?= $days_map[$ws['day_of_week']] ?? 'Desconocido' ?></strong></td>
                                    <td><?= date('h:i A', strtotime($ws['start_time'])) ?> - <?= date('h:i A', strtotime($ws['end_time'])) ?></td>
                                    <td><?= htmlspecialchars($ws['slot_duration']) ?> min</td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este turno?')">
                                            <input type="hidden" name="action" value="delete_weekly_schedule">
                                            <input type="hidden" name="id" value="<?= $ws['id'] ?>">
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Schedule Exceptions -->
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                        <h3 style="border:none; margin:0;">Excepciones y Días Festivos</h3>
                        <button class="btn" onclick="openModal('modalAddException')"><i class="fas fa-calendar-times"></i> Agregar Excepción</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha Específica</th>
                                    <th>Estado / Horario</th>
                                    <th>Duración de Cita</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($schedule_exceptions)): ?><tr><td colspan="4" style="text-align:center;">No hay excepciones configuradas.</td></tr><?php endif; ?>
                                <?php foreach($schedule_exceptions as $ex): ?>
                                <tr>
                                    <td><strong><?= date('d M Y', strtotime($ex['exception_date'])) ?></strong></td>
                                    <td>
                                        <?php if($ex['is_closed'] == 1): ?>
                                            <span class="status-badge status-cancelada"><i class="fas fa-door-closed"></i> Cerrado</span>
                                        <?php else: ?>
                                            <?= date('h:i A', strtotime($ex['start_time'])) ?> - <?= date('h:i A', strtotime($ex['end_time'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $ex['is_closed'] ? '-' : htmlspecialchars($ex['slot_duration']) . ' min' ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta excepción?')">
                                            <input type="hidden" name="action" value="delete_schedule_exception">
                                            <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                                            <button type="button" class="btn btn-warning" style="background:#f59e0b; color:white; border:none;" onclick="editException(<?= $ex['id'] ?>, '<?= htmlspecialchars($ex['exception_date']) ?>', '<?= htmlspecialchars($ex['start_time']) ?>', '<?= htmlspecialchars($ex['end_time']) ?>', <?= $ex['slot_duration'] ?>, <?= $ex['is_closed'] ?>)"><i class="fas fa-edit"></i></button>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Interval Limits / Window Settings -->
                <div class="card" style="margin-top: 20px;">
                    <h3 style="border:none; margin-bottom: 20px;"><i class="fas fa-calendar-alt" style="color: var(--primary);"></i> Límite de Reserva Futura</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_settings">
                        <div class="form-group" style="max-width: 500px;">
                            <label style="font-weight: 600;">Límite de Días para Reservar en el Futuro</label>
                            <input type="number" name="appointment_window_days" value="<?= htmlspecialchars($settings['appointment_window_days'] ?? '30') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-top: 8px;">
                            <small style="color:#666; display: block; margin-top: 5px;">Define cuántos días en el futuro puede reservar un cliente (ej: 30, 60, 90 días).</small>
                        </div>
                        <button type="submit" class="btn" style="margin-top: 15px;"><i class="fas fa-save"></i> Guardar Límite</button>
                    </form>
                </div>
            </div>

            <!-- Tab: Settings -->
            <div id="tab-settings" class="tab-content">
                <div class="page-header">
                    <h1>Ajustes Generales</h1>
                </div>
                <div class="card">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="save_settings">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h3 style="font-size: 1rem; border: none;">Imágenes de Marca</h3>
                                <div class="form-group">
                                    <label>Logo Actual</label>
                                    <img src="<?= htmlspecialchars($settings['logo'] ?? '') ?>" class="img-preview" style="background:#0B192C;">
                                    <input type="file" name="logo" accept="image/*">
                                    <small style="color:#666;">Deja vacío si no deseas cambiarlo.</small>
                                </div>

                                <div class="form-group">
                                    <label>Favicon Actual</label>
                                    <img src="<?= htmlspecialchars($settings['favicon'] ?? '') ?>" class="img-preview" style="max-width: 32px;">
                                    <input type="file" name="favicon" accept="image/*">
                                </div>
                            </div>
                            
                            <div>
                                <h3 style="font-size: 1rem; border: none;">Contacto y Redes Sociales</h3>
                                <div class="form-group">
                                    <label><i class="fab fa-whatsapp" style="color:#25d366;"></i> Número de WhatsApp</label>
                                    <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>" placeholder="Ej: +521234567890">
                                </div>
                                <div class="form-group">
                                    <label><i class="fab fa-whatsapp" style="color:#25d366;"></i> Mensaje de WhatsApp (botón Detalles)</label>
                                    <textarea name="whatsapp_message_template" rows="4" placeholder="Escribe tu mensaje..."><?= htmlspecialchars($settings['whatsapp_message_template'] ?? 'Hola, estoy interesado en el vehículo {nombre} con precio {precio}. ¿Me pueden dar más información?') ?></textarea>
                                    <p class="placeholder-hint">
                                        Variables disponibles (cópialas donde quieras en el texto):
                                        <code>{nombre}</code> o <code>{titulo}</code> — nombre del auto,
                                        <code>{precio}</code> — precio formateado,
                                        <code>{descripcion}</code> — descripción del auto,
                                        <code>{id}</code> — ID del vehículo.
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label><i class="fab fa-facebook" style="color:#1877f2;"></i> Facebook URL</label>
                                    <input type="text" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fab fa-instagram" style="color:#e1306c;"></i> Instagram URL</label>
                                    <input type="text" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fab fa-twitter" style="color:#1da1f2;"></i> Twitter / X URL</label>
                                    <input type="text" name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube URL</label>
                                    <input type="text" name="social_youtube" value="<?= htmlspecialchars($settings['social_youtube'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
                        
                        <h3 style="font-size: 1.1rem; margin-bottom: 20px;"><i class="fas fa-calculator" style="color: var(--primary);"></i> Ajustes de la Calculadora de Préstamos</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Precio Mínimo del Vehículo ($)</label>
                                <input type="number" name="calc_min_price" value="<?= htmlspecialchars($settings['calc_min_price'] ?? '5000') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div class="form-group">
                                <label>Precio Máximo del Vehículo ($)</label>
                                <input type="number" name="calc_max_price" value="<?= htmlspecialchars($settings['calc_max_price'] ?? '100000') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div class="form-group">
                                <label>Tasa de Interés por Defecto (APR %)</label>
                                <input type="number" name="calc_default_apr" step="0.1" value="<?= htmlspecialchars($settings['calc_default_apr'] ?? '5') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Enganche Mínimo ($)</label>
                                <input type="number" name="calc_min_downpayment" value="<?= htmlspecialchars($settings['calc_min_downpayment'] ?? '0') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div class="form-group">
                                <label>Enganche Máximo ($)</label>
                                <input type="number" name="calc_max_downpayment" value="<?= htmlspecialchars($settings['calc_max_downpayment'] ?? '50000') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div class="form-group">
                                <label>Plazos de Préstamo (separados por coma)</label>
                                <input type="text" name="calc_terms" value="<?= htmlspecialchars($settings['calc_terms'] ?? '12,24,36,48,60,72,84') ?>" placeholder="Ej: 12,24,36,48,60" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                <small style="color:#666; display: block; margin-top: 5px;">Ej: 12,24,36,48,60,72,84</small>
                            </div>
                        </div>



                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Ajustes</button>
                    </form>
                </div>
            </div>

        </div> <!-- End Content -->
    </div> <!-- End Main Wrapper -->

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Modals -->
    <!-- Modal Add Car -->
    <div id="modalAddCar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-car"></i> Agregar Nuevo Auto</h2>
                <button class="close-btn" onclick="closeModal('modalAddCar')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_car">
                <div class="form-group">
                    <label>Título / Modelo</label>
                    <input type="text" name="title" required placeholder="Ej: BMW Serie 3 2024">
                </div>
                <div class="form-group">
                    <label>Precio ($)</label>
                    <input type="number" name="price" step="0.01" required placeholder="Ej: 45000">
                </div>
                <div class="form-group">
                    <label>Imagen del Auto</label>
                    <input type="file" name="car_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-check"></i> Guardar Auto</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Carousel -->
    <div id="modalAddCarousel" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-image"></i> Agregar al Carrusel</h2>
                <button class="close-btn" onclick="closeModal('modalAddCarousel')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_carousel">
                <div class="form-group">
                    <label>Seleccionar Imagen (Resolución recomendada: 1920x1080)</label>
                    <input type="file" name="carousel_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-upload"></i> Subir Imagen</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Weekly Schedule -->
    <div id="modalAddWeekly" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-clock"></i> Agregar Turno Semanal</h2>
                <button class="close-btn" onclick="closeModal('modalAddWeekly')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_weekly_schedule">
                <div class="form-group">
                    <label>Día de la Semana</label>
                    <select name="day_of_week" required>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                        <option value="7">Domingo</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Hora Inicio</label>
                        <input type="time" name="start_time" value="09:00" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Fin</label>
                        <input type="time" name="end_time" value="13:00" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Duración de la Cita (Minutos)</label>
                    <input type="number" name="slot_duration" value="60" min="15" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Turno</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Schedule Exception -->
    <div id="modalAddException" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-times"></i> Agregar Excepción / Festivo</h2>
                <button class="close-btn" onclick="closeModal('modalAddException')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_schedule_exception">
                <div class="form-group">
                    <label>Fecha Específica</label>
                    <input type="date" name="exception_date" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group" style="background:#f8fafc; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #e2e8f0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_closed" id="chkIsClosed" value="1" style="width:20px; height:20px; margin:0;" onchange="toggleExceptionTimes()">
                        <span style="font-weight:600; color:#dc2626;">Día Cerrado / Festivo (No habrá citas)</span>
                    </label>
                </div>
                <div id="exceptionTimes">
                    <p style="font-size:0.85rem; color:#666; margin-bottom:10px;">O especifica un horario especial para este día (ej. medio tiempo):</p>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label>Hora Inicio</label>
                            <input type="time" name="start_time" id="ex_start" value="09:00">
                        </div>
                        <div class="form-group">
                            <label>Hora Fin</label>
                            <input type="time" name="end_time" id="ex_end" value="13:00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Duración de la Cita (Minutos)</label>
                        <input type="number" name="slot_duration" id="ex_dur" value="60" min="15">
                    </div>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Excepción</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Service -->
    <div id="modalAddService" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-handshake"></i> Nuevo Servicio</h2>
                <button class="close-btn" onclick="closeModal('modalAddService')">&times;</button>
            </div>
            <form method="POST" action="<?= $base_url ?>api/services.php" id="formAddService">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Icono (Clase de FontAwesome, ej: fas fa-car)</label>
                    <input type="text" name="icon" required placeholder="fas fa-handshake">
                    <small style="color: #666; margin-top: 5px; display: block;">Busca iconos en <a href="https://fontawesome.com/v5/search?m=free" target="_blank" style="color:var(--primary);">FontAwesome 5</a></small>
                </div>
                <div class="form-group">
                    <label>Título del Servicio</label>
                    <input type="text" name="title" required placeholder="Ej: FINANCIAMIENTO">
                </div>
                <div class="form-group">
                    <label>Descripción Corta</label>
                    <textarea name="description" rows="3" required placeholder="Describe el servicio brevemente..."></textarea>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Servicio</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Car -->
    <div id="modalEditCar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Auto</h2>
                <button class="close-btn" onclick="closeModal('modalEditCar')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_car">
                <input type="hidden" name="car_id" id="edit_car_id">
                <div class="form-group">
                    <label>Título del Vehículo</label>
                    <input type="text" name="title" id="edit_car_title" required>
                </div>
                <div class="form-group">
                    <label>Precio ($)</label>
                    <input type="number" name="price" id="edit_car_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" id="edit_car_description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Nueva Imagen (Opcional)</label>
                    <input type="file" name="car_image" accept="image/*">
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Carousel -->
    <div id="modalEditCarousel" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Carrusel</h2>
                <button class="close-btn" onclick="closeModal('modalEditCarousel')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_carousel">
                <input type="hidden" name="carousel_id" id="edit_carousel_id">
                <div class="form-group">
                    <label>Nueva Imagen (Reemplazará la actual)</label>
                    <input type="file" name="carousel_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Service -->
    <div id="modalEditService" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Servicio</h2>
                <button class="close-btn" onclick="closeModal('modalEditService')">&times;</button>
            </div>
            <form method="POST" action="<?= $base_url ?>api/services.php" id="formEditService">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_service_id">
                <div class="form-group">
                    <label>Icono (Clase de FontAwesome)</label>
                    <input type="text" name="icon" id="edit_service_icon" required>
                </div>
                <div class="form-group">
                    <label>Título del Servicio</label>
                    <input type="text" name="title" id="edit_service_title" required>
                </div>
                <div class="form-group">
                    <label>Descripción Corta</label>
                    <textarea name="description" id="edit_service_description" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Schedule Exception -->
    <div id="modalEditException" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Excepción</h2>
                <button class="close-btn" onclick="closeModal('modalEditException')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_schedule_exception">
                <input type="hidden" name="id" id="edit_exception_id">
                <div class="form-group">
                    <label>Fecha Específica</label>
                    <input type="date" name="exception_date" id="edit_exception_date" required>
                </div>
                <div class="form-group" style="background:#f8fafc; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #e2e8f0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_closed" id="edit_chkIsClosed" value="1" style="width:20px; height:20px; margin:0;" onchange="toggleEditExceptionTimes()">
                        <span style="font-weight:600; color:#dc2626;">Día Cerrado / Festivo</span>
                    </label>
                </div>
                <div id="edit_exceptionTimes">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label>Hora Inicio</label>
                            <input type="time" name="start_time" id="edit_ex_start">
                        </div>
                        <div class="form-group">
                            <label>Hora Fin</label>
                            <input type="time" name="end_time" id="edit_ex_end">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Duración (Minutos)</label>
                        <input type="number" name="slot_duration" id="edit_ex_dur" min="15">
                    </div>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        window.baseAppUrl = '<?= $base_url ?>';
        // Tab Navigation
        const tabs = document.querySelectorAll('.tab-link');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = tab.getAttribute('data-tab');
                
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
                
                // Guardar pestaña en localStorage
                localStorage.setItem('activeDashboardTab', targetTab);
                
                // Close sidebar on mobile after click
                if(window.innerWidth <= 1024) {
                    toggleSidebar();
                }
            });
        });

        // Retain tab state using localStorage (or fallback to URL hash)
        const savedTab = localStorage.getItem('activeDashboardTab');
        if (savedTab) {
            const activeTab = document.querySelector(`.tab-link[data-tab="${savedTab}"]`);
            if (activeTab) {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                activeTab.classList.add('active');
                document.getElementById(savedTab).classList.add('active');
            }
        } else {
            const hash = window.location.hash;
            if (hash) {
                const activeTab = document.querySelector(`.tab-link[data-tab="${hash.replace('#', '')}"]`);
                if (activeTab) activeTab.click();
            }
        }

        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        document.getElementById('menuToggle').addEventListener('click', toggleSidebar);
        document.getElementById('closeSidebar').addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Modals Logic
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.addEventListener('click', (e) => {
            if(e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon}"></i>
                <div class="toast-body">${message}</div>
            `;
            
            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Exception UI logic
        function toggleExceptionTimes() {
            const isClosed = document.getElementById('chkIsClosed').checked;
            const timesDiv = document.getElementById('exceptionTimes');
            const inputs = timesDiv.querySelectorAll('input');
            
            if(isClosed) {
                timesDiv.style.opacity = '0.5';
                inputs.forEach(i => i.disabled = true);
            } else {
                timesDiv.style.opacity = '1';
                inputs.forEach(i => i.disabled = false);
            }
        }

        // Appointments Paginated Table System (AJAX)
        let appointmentsPage = 1;
        const appointmentsLimit = 10;
        let appointmentsSearchTimer = null;

        function fetchAppointments() {
            const search = document.getElementById('searchAppointments').value;
            const dateFilter = document.getElementById('filterDateAppointments').value;
            const shorthand = document.getElementById('filterShorthandAppointments').value;
            const tbody = document.getElementById('appointmentsTbody');
            const offset = (appointmentsPage - 1) * appointmentsLimit;

            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Cargando citas...</td></tr>';

            const url = `${window.baseAppUrl}api/get_appointments.php?limit=${appointmentsLimit}&offset=${offset}&search=${encodeURIComponent(search)}&date_filter=${dateFilter}&shorthand=${shorthand}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (!data.appointments || data.appointments.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay citas registradas con estos filtros.</td></tr>';
                        document.getElementById('appointmentsPaginationInfo').textContent = 'Mostrando 0 de 0';
                        document.getElementById('btnPrevAppointments').disabled = true;
                        document.getElementById('btnNextAppointments').disabled = true;
                        return;
                    }

                    data.appointments.forEach(appt => {
                        const dateFormatted = new Date(appt.appointment_date + 'T00:00:00').toLocaleDateString('es-ES', {
                            day: '2-digit', month: 'short', year: 'numeric'
                        });
                        
                        // Parse time (e.g. 09:00:00 to 09:00 AM)
                        const timeParts = appt.appointment_time.split(':');
                        const hours = parseInt(timeParts[0]);
                        const minutes = timeParts[1];
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        const displayHours = hours % 12 || 12;
                        const timeFormatted = `${displayHours}:${minutes} ${ampm}`;

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${dateFormatted}</strong><br><small>${timeFormatted}</small></td>
                            <td>${appt.first_name} ${appt.last_name}</td>
                            <td><a href="tel:${appt.phone}">${appt.phone}</a></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_appointment">
                                    <input type="hidden" name="id" value="${appt.id}">
                                    <select name="status" onchange="this.form.submit()" style="width: auto; padding: 6px; border-radius: 4px;">
                                        <option value="pendiente" ${appt.status === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                                        <option value="confirmada" ${appt.status === 'confirmada' ? 'selected' : ''}>Confirmada</option>
                                        <option value="cancelada" ${appt.status === 'cancelada' ? 'selected' : ''}>Cancelada</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta cita?')">
                                    <input type="hidden" name="action" value="delete_appointment">
                                    <input type="hidden" name="id" value="${appt.id}">
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Update pagination info
                    const startCount = offset + 1;
                    const endCount = Math.min(offset + data.appointments.length, data.total);
                    document.getElementById('appointmentsPaginationInfo').textContent = `Mostrando ${startCount}-${endCount} de ${data.total}`;

                    // Update buttons
                    document.getElementById('btnPrevAppointments').disabled = (appointmentsPage === 1);
                    document.getElementById('btnNextAppointments').disabled = (offset + appointmentsLimit >= data.total);
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error al cargar las citas.</td></tr>';
                    console.error(err);
                });
        }

        // Debounce search
        document.getElementById('searchAppointments').addEventListener('input', () => {
            clearTimeout(appointmentsSearchTimer);
            appointmentsSearchTimer = setTimeout(() => {
                appointmentsPage = 1;
                fetchAppointments();
            }, 300);
        });

        // Filter listeners
        document.getElementById('filterDateAppointments').addEventListener('change', () => {
            appointmentsPage = 1;
            fetchAppointments();
        });

        document.getElementById('filterShorthandAppointments').addEventListener('change', () => {
            appointmentsPage = 1;
            fetchAppointments();
        });

        // Pagination buttons
        document.getElementById('btnPrevAppointments').addEventListener('click', () => {
            if (appointmentsPage > 1) {
                appointmentsPage--;
                fetchAppointments();
            }
        });

        document.getElementById('btnNextAppointments').addEventListener('click', () => {
            appointmentsPage++;
            fetchAppointments();
        });

        // Load initially
        document.addEventListener('DOMContentLoaded', () => {
            fetchAppointments();
        });

        // Cars Paginated Table System (AJAX)
        let carsPage = 1;
        const carsLimit = 10;
        let carsSearchTimer = null;

        function fetchCars() {
            const search = document.getElementById('searchCars').value;
            const shorthand = document.getElementById('filterShorthandCars').value;
            const priceFilter = document.getElementById('filterPriceCars').value;
            const tbody = document.getElementById('carsTbody');
            const offset = (carsPage - 1) * carsLimit;

            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Cargando autos...</td></tr>';

            const url = `${window.baseAppUrl}api/get_cars.php?limit=${carsLimit}&offset=${offset}&search=${encodeURIComponent(search)}&shorthand=${shorthand}&price_filter=${priceFilter}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';
                    // Reset select all checkbox and bulk actions bar
                    document.getElementById('selectAllCars').checked = false;
                    updateBulkActionsCarsVisibility();

                    if (!data.cars || data.cars.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay autos registrados con estos criterios.</td></tr>';
                        document.getElementById('carsPaginationInfo').textContent = 'Mostrando 0 de 0';
                        document.getElementById('btnPrevCars').disabled = true;
                        document.getElementById('btnNextCars').disabled = true;
                        return;
                    }

                    data.cars.forEach(car => {
                        const tr = document.createElement('tr');
                        // Format price
                        const formattedPrice = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(car.price);
                        
                        tr.innerHTML = `
                            <td style="text-align: center;"><input type="checkbox" class="car-checkbox" value="${car.id}" style="width: 18px; height: 18px; cursor: pointer;"></td>
                            <td><img src="${car.image_path || (window.baseAppUrl + 'uploads/placeholder_car.jpg')}" alt="car" style="width: 80px; border-radius: 4px;"></td>
                            <td><strong>${car.title}</strong></td>
                            <td>${formattedPrice}</td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este auto?')">
                                    <input type="hidden" name="action" value="delete_car">
                                    <input type="hidden" name="car_id" value="${car.id}">
                                    <button type="button" class="btn btn-warning" style="background:#f59e0b; color:white; border:none;" onclick="editCar(${car.id}, '${car.title.replace(/'/g, "\\'")}', ${car.price}, '${(car.description || '').replace(/'/g, "\\'").replace(/\n/g, '\\n')}')"><i class="fas fa-edit"></i></button>
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Add change listeners to new checkboxes
                    document.querySelectorAll('.car-checkbox').forEach(chk => {
                        chk.addEventListener('change', updateBulkActionsCarsVisibility);
                    });

                    // Update pagination info
                    const startCount = offset + 1;
                    const endCount = Math.min(offset + data.cars.length, data.total);
                    document.getElementById('carsPaginationInfo').textContent = `Mostrando ${startCount}-${endCount} de ${data.total}`;

                    // Update buttons
                    document.getElementById('btnPrevCars').disabled = (carsPage === 1);
                    document.getElementById('btnNextCars').disabled = (offset + carsLimit >= data.total);
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error al cargar el inventario.</td></tr>';
                    console.error(err);
                });
        }

        // Checkbox listeners
        document.getElementById('selectAllCars').addEventListener('change', (e) => {
            const checked = e.target.checked;
            document.querySelectorAll('.car-checkbox').forEach(chk => {
                chk.checked = checked;
            });
            updateBulkActionsCarsVisibility();
        });

        function updateBulkActionsCarsVisibility() {
            const checkedBoxes = document.querySelectorAll('.car-checkbox:checked');
            const totalChecked = checkedBoxes.length;
            const bulkBar = document.getElementById('bulkActionsCars');
            
            if (totalChecked > 0) {
                bulkBar.style.display = 'flex';
                document.getElementById('bulkSelectCountCars').textContent = `${totalChecked} auto(s) seleccionado(s)`;
            } else {
                bulkBar.style.display = 'none';
            }
        }

        function submitBulkDeleteCars() {
            const checkedBoxes = document.querySelectorAll('.car-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(chk => chk.value);
            
            if (ids.length === 0) return;
            
            if (confirm(`¿Seguro que deseas eliminar los ${ids.length} autos seleccionados? Esta acción es irreversible.`)) {
                document.getElementById('bulkDeleteCarIds').value = ids.join(',');
                document.getElementById('formBulkDeleteCars').submit();
            }
        }

        // Debounce search
        document.getElementById('searchCars').addEventListener('input', () => {
            clearTimeout(carsSearchTimer);
            carsSearchTimer = setTimeout(() => {
                carsPage = 1;
                fetchCars();
            }, 300);
        });

        // Filter listeners
        document.getElementById('filterShorthandCars').addEventListener('change', () => {
            carsPage = 1;
            fetchCars();
        });

        document.getElementById('filterPriceCars').addEventListener('change', () => {
            carsPage = 1;
            fetchCars();
        });

        // Pagination buttons
        document.getElementById('btnPrevCars').addEventListener('click', () => {
            if (carsPage > 1) {
                carsPage--;
                fetchCars();
            }
        });

        document.getElementById('btnNextCars').addEventListener('click', () => {
            carsPage++;
            fetchCars();
        });

        // Load initially
        document.addEventListener('DOMContentLoaded', () => {
            fetchCars();
        });

        // Edit Functions
        function editCar(id, title, price, description) {
            document.getElementById('edit_car_id').value = id;
            document.getElementById('edit_car_title').value = title;
            document.getElementById('edit_car_price').value = price;
            document.getElementById('edit_car_description').value = description;
            openModal('modalEditCar');
        }

        function editCarousel(id) {
            document.getElementById('edit_carousel_id').value = id;
            openModal('modalEditCarousel');
        }

        function editService(id, icon, title, description) {
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_service_icon').value = icon;
            document.getElementById('edit_service_title').value = title;
            document.getElementById('edit_service_description').value = description;
            openModal('modalEditService');
        }

        function editException(id, date, startTime, endTime, duration, isClosed) {
            document.getElementById('edit_exception_id').value = id;
            document.getElementById('edit_exception_date').value = date;
            document.getElementById('edit_chkIsClosed').checked = (isClosed == 1);
            document.getElementById('edit_ex_start').value = startTime || '';
            document.getElementById('edit_ex_end').value = endTime || '';
            document.getElementById('edit_ex_dur').value = duration || 60;
            toggleEditExceptionTimes();
            openModal('modalEditException');
        }

        function toggleEditExceptionTimes() {
            const isClosed = document.getElementById('edit_chkIsClosed').checked;
            const timesDiv = document.getElementById('edit_exceptionTimes');
            const inputs = timesDiv.querySelectorAll('input');
            
            if(isClosed) {
                timesDiv.style.opacity = '0.5';
                inputs.forEach(i => i.disabled = true);
            } else {
                timesDiv.style.opacity = '1';
                inputs.forEach(i => i.disabled = false);
            }
        }

        // Trigger toast from PHP
        <?php if($toast_msg): ?>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("<?= htmlspecialchars($toast_msg) ?>", "<?= $toast_type ?>");
            });
        <?php endif; ?>
    </script>
</body>
</html>
