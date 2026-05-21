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
$toast_type = 'success';

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
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $marca_id = (int)($_POST['marca_id'] ?? 0);
            $modelo = trim($_POST['modelo'] ?? '');
            $price = $_POST['price'] ?? 0;
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $featured = isset($_POST['featured']) ? 1 : 0;

            if (empty($slug)) {
                $s = $title;
                $s = preg_replace('/[áàäâã]/u', 'a', $s);
                $s = preg_replace('/[éèëê]/u', 'e', $s);
                $s = preg_replace('/[íìïî]/u', 'i', $s);
                $s = preg_replace('/[óòöôõ]/u', 'o', $s);
                $s = preg_replace('/[úùüûũ]/u', 'u', $s);
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
                $slug = preg_replace('/-+/', '-', $slug);
                $slug = trim($slug, '-');
            }

            $image_path = '';
            if (!empty($_FILES['car_image']['name'])) {
                $img_path = 'uploads/car_' . time() . '_' . $_FILES['car_image']['name'];
                if (move_uploaded_file($_FILES['car_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $image_path = $img_path;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO cars (marca_id, modelo, title, slug, price, image_path, description, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$marca_id, $modelo, $title, $slug, $price, $image_path, $description, $status, $featured]);
            $car_id = $pdo->lastInsertId();

            $comp_stmt = $pdo->prepare("INSERT INTO car_components (car_id, component_type, config, is_active, sort_order) SELECT ?, component_type, config, is_active, sort_order FROM car_components WHERE car_id IS NULL");
            $comp_stmt->execute([$car_id]);

            if (!empty($_POST['spec_field_id']) && !empty($_POST['spec_value'])) {
                $spec_insert = $pdo->prepare("INSERT INTO car_specs (car_id, spec_field_id, valor, sort_order) VALUES (?, ?, ?, ?)");
                foreach ($_POST['spec_field_id'] as $idx => $field_id) {
                    $val = trim($_POST['spec_value'][$idx] ?? '');
                    if ($val !== '') {
                        $spec_insert->execute([$car_id, $field_id, $val, (int)$idx + 1]);
                    }
                }
            }

            if (!empty($_POST['spec_custom_label']) && !empty($_POST['spec_custom_value'])) {
                $spec_insert2 = $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, ?, ?, ?)");
                $offset = count($_POST['spec_field_id'] ?? []);
                foreach ($_POST['spec_custom_label'] as $idx => $label) {
                    $lbl = trim($label);
                    $val = trim($_POST['spec_custom_value'][$idx] ?? '');
                    if ($lbl !== '' && $val !== '') {
                        $spec_insert2->execute([$car_id, $lbl, $val, $offset + (int)$idx + 1]);
                    }
                }
            }

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
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $marca_id = (int)($_POST['marca_id'] ?? 0);
            $modelo = trim($_POST['modelo'] ?? '');
            $price = $_POST['price'] ?? 0;
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $featured = isset($_POST['featured']) ? 1 : 0;

            if (empty($slug)) {
                $s = $title;
                $s = preg_replace('/[áàäâã]/u', 'a', $s);
                $s = preg_replace('/[éèëê]/u', 'e', $s);
                $s = preg_replace('/[íìïî]/u', 'i', $s);
                $s = preg_replace('/[óòöôõ]/u', 'o', $s);
                $s = preg_replace('/[úùüûũ]/u', 'u', $s);
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
                $slug = preg_replace('/-+/', '-', $slug);
                $slug = trim($slug, '-');
            }

            if (!empty($_FILES['car_image']['name'])) {
                $img_path = 'uploads/car_' . time() . '_' . $_FILES['car_image']['name'];
                if (move_uploaded_file($_FILES['car_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $stmt = $pdo->prepare("UPDATE cars SET marca_id = ?, modelo = ?, title = ?, slug = ?, price = ?, description = ?, status = ?, featured = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$marca_id, $modelo, $title, $slug, $price, $description, $status, $featured, $img_path, $id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE cars SET marca_id = ?, modelo = ?, title = ?, slug = ?, price = ?, description = ?, status = ?, featured = ? WHERE id = ?");
                $stmt->execute([$marca_id, $modelo, $title, $slug, $price, $description, $status, $featured, $id]);
            }

            $pdo->prepare("DELETE FROM car_specs WHERE car_id = ?")->execute([$id]);
            if (!empty($_POST['spec_field_id']) && !empty($_POST['spec_value'])) {
                $spec_insert = $pdo->prepare("INSERT INTO car_specs (car_id, spec_field_id, valor, sort_order) VALUES (?, ?, ?, ?)");
                foreach ($_POST['spec_field_id'] as $idx => $field_id) {
                    $val = trim($_POST['spec_value'][$idx] ?? '');
                    if ($val !== '') {
                        $spec_insert->execute([$id, $field_id, $val, (int)$idx + 1]);
                    }
                }
            }
            if (!empty($_POST['spec_custom_label']) && !empty($_POST['spec_custom_value'])) {
                $spec_insert2 = $pdo->prepare("INSERT INTO car_specs (car_id, etiqueta, valor, sort_order) VALUES (?, ?, ?, ?)");
                $offset = count($_POST['spec_field_id'] ?? []);
                foreach ($_POST['spec_custom_label'] as $idx => $label) {
                    $lbl = trim($label);
                    $val = trim($_POST['spec_custom_value'][$idx] ?? '');
                    if ($lbl !== '' && $val !== '') {
                        $spec_insert2->execute([$id, $lbl, $val, $offset + (int)$idx + 1]);
                    }
                }
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

        if ($action === 'add_marca') {
            $nombre = trim($_POST['nombre'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (empty($nombre)) {
                $toast_msg = "El nombre de la marca es requerido.";
                $toast_type = 'error';
            } else {
                if (empty($slug)) {
                    $s = preg_replace('/[áàäâã]/u', 'a', $nombre);
                    $s = preg_replace('/[éèëê]/u', 'e', $s);
                    $s = preg_replace('/[íìïî]/u', 'i', $s);
                    $s = preg_replace('/[óòöôõ]/u', 'o', $s);
                    $s = preg_replace('/[úùüûũ]/u', 'u', $s);
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
                    $slug = preg_replace('/-+/', '-', $slug);
                    $slug = trim($slug, '-');
                }
                $logo_path = null;
                if (!empty($_FILES['marca_logo']['name'])) {
                    $upload_dir = __DIR__ . '/../uploads/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $logo_path = 'uploads/marca_' . time() . '_' . $_FILES['marca_logo']['name'];
                    if (!move_uploaded_file($_FILES['marca_logo']['tmp_name'], __DIR__ . '/../' . $logo_path)) {
                        $logo_path = null;
                    }
                }
                $stmt = $pdo->prepare("INSERT INTO marcas (nombre, slug, logo) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $slug, $logo_path]);
                $toast_msg = "Marca '{$nombre}' agregada exitosamente.";
            }
        }

        if ($action === 'edit_marca') {
            $id = (int)($_POST['marca_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (empty($id) || empty($nombre)) {
                $toast_msg = "ID y nombre son requeridos.";
                $toast_type = 'error';
            } else {
                if (empty($slug)) {
                    $s = preg_replace('/[áàäâã]/u', 'a', $nombre);
                    $s = preg_replace('/[éèëê]/u', 'e', $s);
                    $s = preg_replace('/[íìïî]/u', 'i', $s);
                    $s = preg_replace('/[óòöôõ]/u', 'o', $s);
                    $s = preg_replace('/[úùüûũ]/u', 'u', $s);
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
                    $slug = preg_replace('/-+/', '-', $slug);
                    $slug = trim($slug, '-');
                }
                $logo_path = null;
                if (!empty($_FILES['marca_logo']['name'])) {
                    $upload_dir = __DIR__ . '/../uploads/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $logo_path = 'uploads/marca_' . time() . '_' . $_FILES['marca_logo']['name'];
                    if (move_uploaded_file($_FILES['marca_logo']['tmp_name'], __DIR__ . '/../' . $logo_path)) {
                        $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, slug = ?, logo = ? WHERE id = ?");
                        $stmt->execute([$nombre, $slug, $logo_path, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, slug = ? WHERE id = ?");
                        $stmt->execute([$nombre, $slug, $id]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, slug = ? WHERE id = ?");
                    $stmt->execute([$nombre, $slug, $id]);
                }
                $toast_msg = "Marca '{$nombre}' actualizada.";
            }
        }

        if ($action === 'delete_marca') {
            $id = (int)($_POST['marca_id'] ?? 0);
            $check = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE marca_id = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                $toast_msg = "No se puede eliminar: la marca tiene autos asociados.";
                $toast_type = 'error';
            } else {
                $stmt = $pdo->prepare("DELETE FROM marcas WHERE id = ?");
                $stmt->execute([$id]);
                $toast_msg = "Marca eliminada.";
            }
        }
        
        if ($action === 'delete_carousel') {
            $id = $_POST['carousel_id'];
            $stmt = $pdo->prepare("DELETE FROM carousel_images WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Imagen eliminada.";
        }

        if ($action === 'toggle_carousel') {
            $id = $_POST['carousel_id'];
            $stmt = $pdo->prepare("UPDATE carousel_images SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $toast_msg = "Estado de imagen actualizado.";
        }

        if ($action === 'edit_carousel') {
            $id = $_POST['carousel_id'];
            if (!empty($_FILES['carousel_image']['name'])) {
                $img_path = 'uploads/hero_' . time() . '_' . $_FILES['carousel_image']['name'];
                if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], __DIR__ . '/../' . $img_path)) {
                    $stmt = $pdo->prepare("UPDATE carousel_images SET image_path = ? WHERE id = ?");
                    $stmt->execute([$img_path, $id]);
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

$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_db = $stmt->fetchAll();
$settings = [];
foreach ($settings_db as $s) { $settings[$s['setting_key']] = $s['setting_value']; }

$stmt = $pdo->query("SELECT * FROM spec_fields ORDER BY sort_order ASC");
$spec_fields = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM car_components WHERE car_id IS NULL ORDER BY sort_order ASC");
$default_components = $stmt->fetchAll();

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

$stmt = $pdo->query("SELECT m.*, (SELECT COUNT(*) FROM cars c WHERE c.marca_id = m.id) as car_count FROM marcas m ORDER BY m.nombre ASC");
$marcas = $stmt->fetchAll();
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
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: 260px; background-color: var(--primary); color: white; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1000; transition: transform 0.3s ease; box-shadow: 4px 0 15px rgba(0,0,0,0.1); }
        .sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-header i { font-size: 24px; }
        .sidebar-header h2 { font-size: 1.1rem; font-weight: 700; margin: 0; }
        .sidebar ul { list-style: none; padding: 15px 0; }
        .sidebar ul li a { display: flex; align-items: center; gap: 12px; padding: 15px 25px; color: #cbd5e1; text-decoration: none; transition: 0.3s; font-weight: 500; font-size: 0.95rem; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background-color: rgba(255,255,255,0.05); color: white; border-left: 4px solid #3b82f6; }
        .sidebar ul li a i { width: 20px; text-align: center; }
        .close-sidebar { display: none; position: absolute; top: 25px; right: 20px; background: none; border: none; color: white; font-size: 20px; cursor: pointer; }
        .main-wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; width: calc(100% - 260px); transition: margin-left 0.3s ease, width 0.3s ease; }
        .topbar { background: white; height: 70px; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.02); position: sticky; top: 0; z-index: 99; }
        .menu-toggle { display: none; background: none; border: none; font-size: 24px; color: var(--text-dark); cursor: pointer; }
        .topbar-actions a { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-dark); font-weight: 500; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .topbar-actions a:hover { background: var(--bg-color); }
        .topbar-actions a.logout { color: var(--danger); }
        .topbar-actions a.logout:hover { background: #fef2f2; }
        .content { padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 30px; border: 1px solid var(--border); }
        .card h3 { margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px; font-size: 1.1rem; color: var(--primary); }
        .btn { display: inline-flex; align-items: center; gap: 8px; background-color: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: 0.3s; text-decoration: none; }
        .btn:hover { background-color: var(--primary-hover); }
        .btn-danger { background-color: var(--danger); }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-success { background-color: var(--success); }
        .btn-success:hover { background-color: #059669; }
        .btn-warning { background-color: var(--warning); }
        .btn-warning:hover { background-color: #d97706; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #4b5563; font-size: 0.9rem; }
        input[type="text"], input[type="number"], input[type="file"], input[type="date"], input[type="time"], select, textarea { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 0.95rem; transition: border-color 0.3s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(11,25,44,0.1); }
        textarea { resize: vertical; min-height: 100px; }
        .img-preview { max-height: 50px; border-radius: 4px; border: 1px solid #ccc; padding: 2px; margin-bottom: 10px; display: block; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        table th { background: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        table th, table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        table tr:hover td { background-color: #f8fafc; }
        table img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; }
        .status-pendiente { background: #fef3c7; color: #d97706; }
        .status-confirmada { background: #d1fae5; color: #059669; }
        .status-cancelada { background: #fee2e2; color: #dc2626; }
        .status-active { background: #d1fae5; color: #059669; }
        .status-draft { background: #fef3c7; color: #d97706; }
        .status-sold { background: #fee2e2; color: #dc2626; }
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.15); animation: slideDown 0.3s ease; }
        .modal-content.modal-lg { max-width: 800px; max-height: 90vh; overflow-y: auto; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .modal-header h2 { margin: 0; font-size: 1.2rem; color: var(--primary); }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
        .close-btn:hover { color: #333; }
        #toast-container { position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .confirm-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1500; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .confirm-overlay.open { opacity: 1; pointer-events: auto; }
        .confirm-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%) scale(0.9); background: #fff; border-radius: 16px; padding: 30px 40px; z-index: 1501; min-width: 320px; max-width: 90vw; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3); opacity: 0; pointer-events: none; transition: all 0.3s ease; }
        .confirm-modal.open { opacity: 1; pointer-events: auto; transform: translate(-50%,-50%) scale(1); }
        .confirm-modal .confirm-icon { width: 60px; height: 60px; border-radius: 50%; background: #fef2f2; color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 20px auto; }
        .confirm-modal #confirm-message { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 25px; }
        .confirm-modal .confirm-actions { display: flex; gap: 12px; justify-content: center; }
        .confirm-modal .confirm-actions .btn { min-width: 100px; }
        .toast { background: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px; min-width: 300px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55); border-left: 5px solid; }
        .toast.show { transform: translateX(0); }
        .toast.success { border-color: var(--success); }
        .toast.error { border-color: var(--danger); }
        .toast i { font-size: 20px; }
        .toast.success i { color: var(--success); }
        .toast.error i { color: var(--danger); }
        .toast-body { flex: 1; font-size: 0.95rem; font-weight: 500; }
        .spec-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .spec-row select, .spec-row input { flex: 1; }
        .spec-row .btn-remove { background: var(--danger); color: white; border: none; width: 36px; height: 36px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .image-upload-area { border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; }
        .image-upload-area:hover { border-color: var(--primary); background: #f8fafc; }
        .uploaded-images { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .uploaded-img-item { position: relative; width: 100px; height: 80px; border-radius: 6px; overflow: hidden; border: 2px solid #e2e8f0; }
        .uploaded-img-item img { width: 100%; height: 100%; object-fit: cover; }
        .uploaded-img-item .img-actions { position: absolute; top: 2px; right: 2px; display: flex; gap: 2px; }
        .uploaded-img-item .img-actions button { background: rgba(0,0,0,0.6); color: white; border: none; width: 22px; height: 22px; border-radius: 4px; cursor: pointer; font-size: 10px; display: flex; align-items: center; justify-content: center; }
        .uploaded-img-item.is-primary { border-color: var(--warning); }
        .uploaded-img-item.is-primary::after { content: '★'; position: absolute; bottom: 2px; left: 2px; background: var(--warning); color: white; font-size: 10px; padding: 1px 4px; border-radius: 3px; }
        .component-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 8px; cursor: grab; }
        .component-item:active { cursor: grabbing; }
        .component-item .drag-handle { color: #94a3b8; cursor: grab; }
        .component-item .comp-info { flex: 1; }
        .component-item .comp-info strong { font-size: 0.9rem; }
        .component-item .comp-info small { color: #64748b; }
        .component-item.dragging { opacity: 0.5; background: #e2e8f0; }
        .form-row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-row-3col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-primary { background: #dbeafe; color: #1d4ed8; }
        .badge-success { background: #d1fae5; color: #059669; }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; width: 100%; }
            .menu-toggle { display: block; }
            .close-sidebar { display: block; }
            .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; }
            .overlay.show { display: block; }
            .form-row-2col, .form-row-3col { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="overlay" id="mobileOverlay"></div>

    <div class="sidebar" id="sidebar">
        <button class="close-sidebar" id="closeSidebar"><i class="fas fa-times"></i></button>
        <div class="sidebar-header">
            <i class="fas fa-car"></i>
            <h2>Tu Auto Con</h2>
        </div>
        <ul>
            <li><a href="#" class="tab-link active" data-tab="tab-cars"><i class="fas fa-car-side"></i> Inventario</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-marcas"><i class="fas fa-flag"></i> Marcas</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-spec-fields"><i class="fas fa-tags"></i> Campos de Especificación</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-components"><i class="fas fa-puzzle-piece"></i> Plantilla de Página</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-carousel"><i class="fas fa-images"></i> Carrusel</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-appointments"><i class="fas fa-calendar-check"></i> Citas</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-availability"><i class="fas fa-clock"></i> Disponibilidad</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-services"><i class="fas fa-handshake"></i> Servicios</a></li>
            <li><a href="#" class="tab-link" data-tab="tab-settings"><i class="fas fa-cog"></i> Ajustes</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="topbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div style="flex: 1;"></div>
            <div class="topbar-actions">
                <a href="<?= $base_url ?>" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Sitio</a>
                <a href="<?= $base_url ?>admin/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>

        <div class="content">
            <!-- Tab: Cars -->
            <div id="tab-cars" class="tab-content active">
                <div class="page-header" style="margin-bottom: 15px;">
                    <h1>Inventario de Autos</h1>
                    <button class="btn" onclick="window.location.href='<?= $base_url ?>admin/auto/crear'"><i class="fas fa-plus"></i> Nuevo Auto</button>
                </div>
                <div class="card" style="margin-bottom: 20px; padding: 15px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; background: #fff; justify-content: space-between;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" id="searchCars" placeholder="Buscar por título o descripción..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="min-width: 160px;">
                        <select id="filterShorthandCars" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer;">
                            <option value="all">Todos los Autos</option>
                            <option value="recent">Recientes</option>
                            <option value="luxury">Gama Alta</option>
                            <option value="budget">Económicos</option>
                        </select>
                    </div>
                    <div style="min-width: 160px;">
                        <select id="filterPriceCars" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer;">
                            <option value="">Cualquier Precio</option>
                            <option value="50k">&lt; $50,000</option>
                            <option value="100k">&lt; $100,000</option>
                            <option value="150k">&lt; $150,000</option>
                            <option value="150k_plus">&ge; $150,000</option>
                        </select>
                    </div>
                    <div id="bulkActionsCars" style="display: none; align-items: center; gap: 10px;">
                        <span id="bulkSelectCountCars" style="font-weight: 600; color: var(--text-dark);">0 autos seleccionados</span>
                        <button class="btn btn-danger" onclick="submitBulkDeleteCars()"><i class="fas fa-trash-alt"></i> Eliminar</button>
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
                                    <th>Slug</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="carsTbody">
                                <tr><td colspan="7" style="text-align:center;">Cargando autos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <span id="carsPaginationInfo" style="color: var(--text-light); font-size: 0.9rem;">Mostrando 0 de 0</span>
                        <div style="display: flex; gap: 10px;">
                            <button id="btnPrevCars" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);"><i class="fas fa-chevron-left"></i> Anterior</button>
                            <button id="btnNextCars" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);">Siguiente <i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <form id="formBulkDeleteCars" method="POST" style="display:none;">
                <input type="hidden" name="action" value="bulk_delete_cars">
                <input type="hidden" name="car_ids" id="bulkDeleteCarIds">
            </form>

            <!-- Tab: Spec Fields -->
            <div id="tab-spec-fields" class="tab-content">
                <div class="page-header" style="margin-bottom: 15px;">
                    <h1>Campos de Especificación</h1>
                    <button class="btn" onclick="openModal('modalAddSpecField')"><i class="fas fa-plus"></i> Nuevo Campo</button>
                </div>
                <p style="color: #64748b; margin-bottom: 20px;">Define los campos que aparecerán al crear/editar autos. Estos campos también se usarán como filtros en el catálogo.</p>
                <div class="card" style="margin-bottom: 20px; padding: 15px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; background: #fff;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" id="searchSpecFields" placeholder="Buscar por nombre o slug..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="min-width: 160px;">
                        <select id="filterTypeSpecFields" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer;">
                            <option value="">Todos los tipos</option>
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="select">Selección</option>
                            <option value="color">Color</option>
                        </select>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Nombre</th><th>Slug</th><th>Tipo</th><th>Opciones</th><th>Obligatorio</th><th>Acciones</th></tr></thead>
                            <tbody id="specFieldsTbody"><tr><td colspan="6" style="text-align:center;">Cargando...</td></tr></tbody>
                        </table>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <span id="specFieldsPaginationInfo" style="color: var(--text-light); font-size: 0.9rem;">Mostrando 0 de 0</span>
                        <div style="display: flex; gap: 10px;">
                            <button id="btnPrevSpecFields" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);"><i class="fas fa-chevron-left"></i> Anterior</button>
                            <button id="btnNextSpecFields" class="btn" style="background: var(--bg-color); color: var(--text-dark); border: 1px solid var(--border);">Siguiente <i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Components Template -->
            <div id="tab-components" class="tab-content">
                <div class="page-header">
                    <h1>Plantilla de Página por Defecto</h1>
                </div>
                <div class="card">
                    <p style="color: #64748b; margin-bottom: 20px;">Define qué componentes tendrán los autos nuevos por defecto. Arrastra para reordenar.</p>
                    <div id="default-components-list"></div>
                    <div style="margin-top: 20px;">
                        <button class="btn" onclick="openModal('modalAddComponent')"><i class="fas fa-plus"></i> Agregar Componente</button>
                    </div>
                </div>
            </div>

            <!-- Tab: Marcas -->
            <div id="tab-marcas" class="tab-content">
                <div class="page-header">
                    <h1>Gestión de Marcas</h1>
                    <button class="btn" onclick="openModal('modalAddMarca')"><i class="fas fa-plus"></i> Nueva Marca</button>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Logo</th><th>Nombre</th><th>Slug</th><th>Autos</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php if(empty($marcas)): ?><tr><td colspan="5" style="text-align:center;">No hay marcas creadas.</td></tr><?php endif; ?>
                                <?php foreach($marcas as $m): ?>
                                <tr>
                                    <td><?php if($m['logo']): ?><img src="<?= htmlspecialchars(get_asset_url($m['logo'])) ?>" alt="<?= htmlspecialchars($m['nombre']) ?>" style="width:60px;height:40px;object-fit:contain;"><?php else: ?><span style="color:#999;">—</span><?php endif; ?></td>
                                    <td><strong><?= htmlspecialchars($m['nombre']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($m['slug']) ?></code></td>
                                    <td><span class="badge badge-primary"><?= $m['car_count'] ?> autos</span></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editMarca(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['nombre'])) ?>', '<?= htmlspecialchars(addslashes($m['slug'])) ?>')"><i class="fas fa-edit"></i></button>
                                        <?php if($m['car_count'] == 0): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta marca?')">
                                            <input type="hidden" name="action" value="delete_marca">
                                            <input type="hidden" name="marca_id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <?php else: ?>
                                        <span title="No se puede eliminar: tiene autos asociados" style="color:#999;cursor:not-allowed;"><i class="fas fa-trash"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab: Carousel -->
            <div id="tab-carousel" class="tab-content">
                <div class="page-header">
                    <h1>Carrusel Principal</h1>
                    <button class="btn" onclick="openModal('modalAddCarousel')"><i class="fas fa-plus"></i> Subir Imagen</button>
                </div>
                <div class="card">
                    <p style="color: #64748b; margin-bottom: 20px;">Estas imágenes se muestran como fondo del encabezado de la página de inicio. Sube imágenes de al menos 1920x800px para mejor calidad.</p>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Vista Previa</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php if(empty($carousels)): ?><tr><td colspan="3" style="text-align:center;">No hay imágenes en el carrusel.</td></tr><?php endif; ?>
                                <?php foreach($carousels as $c): ?>
                                <tr>
                                    <td><img src="<?= htmlspecialchars(get_asset_url($c['image_path'])) ?>" alt="hero" style="width: 200px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;"></td>
                                    <td>
                                        <?php if($c['is_active']): ?>
                                            <span class="status-badge status-active">Activo</span>
                                        <?php else: ?>
                                            <span class="status-badge status-draft">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_carousel">
                                            <input type="hidden" name="carousel_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background: <?= $c['is_active'] ? 'var(--warning)' : 'var(--success)' ?>;">
                                                <i class="fas <?= $c['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i> <?= $c['is_active'] ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                            <input type="hidden" name="action" value="delete_carousel">
                                            <input type="hidden" name="carousel_id" value="<?= $c['id'] ?>">
                                            <button type="button" class="btn btn-warning btn-sm" onclick="editCarousel(<?= $c['id'] ?>)"><i class="fas fa-edit"></i></button>
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
                            <thead><tr><th>Icono</th><th>Título</th><th>Descripción</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php if(empty($services)): ?><tr><td colspan="4" style="text-align:center;">No hay servicios configurados.</td></tr><?php endif; ?>
                                <?php foreach($services as $svc): ?>
                                <tr>
                                    <td><i class="<?= htmlspecialchars($svc['icon']) ?> fa-2x"></i><br><small><?= htmlspecialchars($svc['icon']) ?></small></td>
                                    <td><strong><?= htmlspecialchars($svc['title']) ?></strong></td>
                                    <td><?= htmlspecialchars(substr($svc['description'], 0, 50)) ?>...</td>
                                    <td>
                                        <form method="POST" action="<?= $base_url ?>api/services.php" style="display:inline;" onsubmit="return confirm('¿Eliminar este servicio?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                            <button type="button" class="btn btn-warning btn-sm" onclick="editService(<?= $svc['id'] ?>, '<?= htmlspecialchars(addslashes($svc['icon']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($svc['title']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($svc['description']), ENT_QUOTES) ?>')"><i class="fas fa-edit"></i></button>
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
                <div class="card" style="margin-bottom: 20px; padding: 15px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; background: #fff;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" id="searchAppointments" placeholder="Buscar por nombre, apellido o teléfono..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div><input type="date" id="filterDateAppointments" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px;"></div>
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
                            <thead><tr><th>Fecha y Hora</th><th>Cliente</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody id="appointmentsTbody"><tr><td colspan="5" style="text-align:center;">Cargando citas...</td></tr></tbody>
                        </table>
                    </div>
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
                <div class="page-header"><h1>Disponibilidad y Horarios</h1></div>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                        <h3 style="border:none; margin:0;">Horario Regular Semanal</h3>
                        <button class="btn" onclick="openModal('modalAddWeekly')"><i class="fas fa-plus"></i> Agregar Turno</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Día</th><th>Horario</th><th>Duración</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php $days_map = [1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes', 6=>'Sábado', 7=>'Domingo']; ?>
                                <?php if(empty($weekly_schedules)): ?><tr><td colspan="4" style="text-align:center;">No hay horarios configurados.</td></tr><?php endif; ?>
                                <?php foreach($weekly_schedules as $ws): ?>
                                <tr>
                                    <td><strong><?= $days_map[$ws['day_of_week']] ?? 'Desconocido' ?></strong></td>
                                    <td><?= date('h:i A', strtotime($ws['start_time'])) ?> - <?= date('h:i A', strtotime($ws['end_time'])) ?></td>
                                    <td><?= htmlspecialchars($ws['slot_duration']) ?> min</td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este turno?')">
                                            <input type="hidden" name="action" value="delete_weekly_schedule">
                                            <input type="hidden" name="id" value="<?= $ws['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                        <h3 style="border:none; margin:0;">Excepciones y Festivos</h3>
                        <button class="btn" onclick="openModal('modalAddException')"><i class="fas fa-calendar-times"></i> Agregar</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Fecha</th><th>Estado</th><th>Duración</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php if(empty($schedule_exceptions)): ?><tr><td colspan="4" style="text-align:center;">No hay excepciones.</td></tr><?php endif; ?>
                                <?php foreach($schedule_exceptions as $ex): ?>
                                <tr>
                                    <td><strong><?= date('d M Y', strtotime($ex['exception_date'])) ?></strong></td>
                                    <td><?php if($ex['is_closed'] == 1): ?><span class="status-badge status-cancelada"><i class="fas fa-door-closed"></i> Cerrado</span><?php else: ?><?= date('h:i A', strtotime($ex['start_time'])) ?> - <?= date('h:i A', strtotime($ex['end_time'])) ?><?php endif; ?></td>
                                    <td><?= $ex['is_closed'] ? '-' : htmlspecialchars($ex['slot_duration']) . ' min' ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta excepción?')">
                                            <input type="hidden" name="action" value="delete_schedule_exception">
                                            <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                                            <button type="button" class="btn btn-warning btn-sm" onclick="editException(<?= $ex['id'] ?>, '<?= htmlspecialchars($ex['exception_date']) ?>', '<?= htmlspecialchars($ex['start_time']) ?>', '<?= htmlspecialchars($ex['end_time']) ?>', <?= $ex['slot_duration'] ?>, <?= $ex['is_closed'] ?>)"><i class="fas fa-edit"></i></button>
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card" style="margin-top: 20px;">
                    <h3 style="border:none; margin-bottom: 20px;"><i class="fas fa-calendar-alt" style="color: var(--primary);"></i> Límite de Reserva</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_settings">
                        <div class="form-group" style="max-width: 500px;">
                            <label>Días para Reservar en el Futuro</label>
                            <input type="number" name="appointment_window_days" value="<?= htmlspecialchars($settings['appointment_window_days'] ?? '30') ?>" required>
                        </div>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar</button>
                    </form>
                </div>
            </div>

            <!-- Tab: Settings -->
            <div id="tab-settings" class="tab-content">
                <div class="page-header"><h1>Ajustes Generales</h1></div>
                <div class="card">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="save_settings">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h3 style="font-size: 1rem; border: none;">Imágenes de Marca</h3>
                                <div class="form-group">
                                    <label>Logo Actual</label>
                                    <img src="<?= htmlspecialchars(get_asset_url($settings['logo'] ?? '')) ?>" class="img-preview" style="background:#0B192C;">
                                    <input type="file" name="logo" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <label>Favicon Actual</label>
                                    <img src="<?= htmlspecialchars(get_asset_url($settings['favicon'] ?? '')) ?>" class="img-preview" style="max-width: 32px;">
                                    <input type="file" name="favicon" accept="image/*">
                                </div>
                            </div>
                            <div>
                                <h3 style="font-size: 1rem; border: none;">Contacto y Redes</h3>
                                <div class="form-group"><label><i class="fab fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label><input type="text" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>"></div>
                                <div class="form-group"><label><i class="fab fa-facebook" style="color:#1877f2;"></i> Facebook</label><input type="text" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>"></div>
                                <div class="form-group"><label><i class="fab fa-instagram" style="color:#e1306c;"></i> Instagram</label><input type="text" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>"></div>
                                <div class="form-group"><label><i class="fab fa-twitter" style="color:#1da1f2;"></i> Twitter/X</label><input type="text" name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>"></div>
                                <div class="form-group"><label><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube</label><input type="text" name="social_youtube" value="<?= htmlspecialchars($settings['social_youtube'] ?? '') ?>"></div>
                            </div>
                        </div>
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 20px;"><i class="fas fa-calculator" style="color: var(--primary);"></i> Calculadora</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                            <div class="form-group"><label>Precio Mínimo</label><input type="number" name="calc_min_price" value="<?= htmlspecialchars($settings['calc_min_price'] ?? '5000') ?>"></div>
                            <div class="form-group"><label>Precio Máximo</label><input type="number" name="calc_max_price" value="<?= htmlspecialchars($settings['calc_max_price'] ?? '100000') ?>"></div>
                            <div class="form-group"><label>APR %</label><input type="number" name="calc_default_apr" step="0.1" value="<?= htmlspecialchars($settings['calc_default_apr'] ?? '5') ?>"></div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                            <div class="form-group"><label>Enganche Mín</label><input type="number" name="calc_min_downpayment" value="<?= htmlspecialchars($settings['calc_min_downpayment'] ?? '0') ?>"></div>
                            <div class="form-group"><label>Enganche Máx</label><input type="number" name="calc_max_downpayment" value="<?= htmlspecialchars($settings['calc_max_downpayment'] ?? '50000') ?>"></div>
                            <div class="form-group"><label>Plazos (coma)</label><input type="text" name="calc_terms" value="<?= htmlspecialchars($settings['calc_terms'] ?? '12,24,36,48,60,72,84') ?>"></div>
                        </div>
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Ajustes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>

    <div class="confirm-overlay" id="confirm-overlay"></div>
    <div class="confirm-modal" id="confirm-modal">
        <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <p id="confirm-message">¿Estás seguro?</p>
        <div class="confirm-actions">
            <button class="btn btn-secondary" id="confirm-no"><i class="fas fa-times"></i> No</button>
            <button class="btn btn-danger" id="confirm-yes"><i class="fas fa-check"></i> Sí</button>
        </div>
    </div>

    <!-- Modal Add Car -->
    <div id="modalAddCar" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2><i class="fas fa-car"></i> Agregar Nuevo Auto</h2>
                <button class="close-btn" onclick="closeModal('modalAddCar')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="formAddCar">
                <input type="hidden" name="action" value="add_car">
                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Marca</label>
                        <select name="marca_id" id="add_car_marca" required>
                            <option value="">Seleccionar marca</option>
                            <?php foreach($marcas as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" id="add_car_modelo" required placeholder="Ej: RAV4, Silverado">
                    </div>
                </div>
                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Título / Modelo</label>
                        <input type="text" name="title" id="add_car_title" required placeholder="Ej: Toyota RAV4 LE Hybrid 2025" oninput="autoGenerateSlug('add_car_title', 'add_car_slug')">
                    </div>
                    <div class="form-group">
                        <label>Slug (URL)</label>
                        <input type="text" name="slug" id="add_car_slug" required placeholder="toyota-rav4-le-hybrid-2025">
                    </div>
                </div>
                <div class="form-row-3col">
                    <div class="form-group">
                        <label>Precio ($)</label>
                        <input type="number" name="price" step="0.01" required placeholder="45000">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status">
                            <option value="active">Activo</option>
                            <option value="draft">Borrador</option>
                            <option value="sold">Vendido</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; margin-top: 28px;">
                            <input type="checkbox" name="featured" style="width: 18px; height: 18px;"> Destacado
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" rows="3" placeholder="Describe el vehículo..."></textarea>
                </div>
                <div class="form-group">
                    <label>Imagen Principal</label>
                    <input type="file" name="car_image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Especificaciones</label>
                    <div id="add-car-specs"></div>
                    <button type="button" class="btn btn-sm" onclick="addCustomSpecRow('add-car-specs')" style="margin-top: 8px;"><i class="fas fa-plus"></i> Añadir Extra</button>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-check"></i> Guardar Auto</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Car -->
    <div id="modalEditCar" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Auto</h2>
                <button class="close-btn" onclick="closeModal('modalEditCar')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_car">
                <input type="hidden" name="car_id" id="edit_car_id">
                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Marca</label>
                        <select name="marca_id" id="edit_car_marca" required>
                            <option value="">Seleccionar marca</option>
                            <?php foreach($marcas as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" id="edit_car_modelo" required placeholder="Ej: RAV4, Silverado">
                    </div>
                </div>
                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="title" id="edit_car_title" required oninput="autoGenerateSlug('edit_car_title', 'edit_car_slug')">
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="edit_car_slug" required>
                    </div>
                </div>
                <div class="form-row-3col">
                    <div class="form-group">
                        <label>Precio</label>
                        <input type="number" name="price" id="edit_car_price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" id="edit_car_status">
                            <option value="active">Activo</option>
                            <option value="draft">Borrador</option>
                            <option value="sold">Vendido</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; margin-top: 28px;">
                            <input type="checkbox" name="featured" id="edit_car_featured"> Destacado
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" id="edit_car_description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Nueva Imagen (Opcional)</label>
                    <input type="file" name="car_image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Especificaciones</label>
                    <div id="edit-car-specs"></div>
                    <button type="button" class="btn btn-sm" onclick="addCustomSpecRow('edit-car-specs')" style="margin-top: 8px;"><i class="fas fa-plus"></i> Añadir Extra</button>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal Add/Edit Spec Field -->
    <div id="modalAddSpecField" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-tag"></i> <span id="sf_modal_title">Nuevo Campo</span></h2>
                <button class="close-btn" onclick="closeModal('modalAddSpecField')">&times;</button>
            </div>
            <form id="formAddSpecField" onsubmit="submitSpecField(event)">
                <input type="hidden" name="action" id="sf_action" value="create">
                <input type="hidden" name="id" id="sf_id" value="">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="sf_nombre" required placeholder="Ej: Transmisión" oninput="autoGenerateSlug('sf_nombre', 'sf_slug')">
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" id="sf_slug" required>
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo" id="sf_tipo" onchange="toggleSpecOptions()">
                        <option value="text">Texto</option>
                        <option value="number">Número</option>
                        <option value="select">Selección</option>
                        <option value="color">Color</option>
                    </select>
                </div>
                <div class="form-group" id="sf_opciones_group" style="display:none;">
                    <label>Opciones (separadas por coma)</label>
                    <input type="text" name="opciones" id="sf_opciones" placeholder="Automática, Manual, CVT">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="obligatorio" id="sf_obligatorio" style="width: 18px; height: 18px;"> Obligatorio
                    </label>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> <span id="sf_submit_text">Guardar</span></button>
            </form>
        </div>
    </div>

    <!-- Modal Add Component -->
    <div id="modalAddComponent" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-puzzle-piece"></i> Agregar Componente</h2>
                <button class="close-btn" onclick="closeModal('modalAddComponent')">&times;</button>
            </div>
            <form id="formAddComponent" onsubmit="submitComponent(event)">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="car_id" value="">
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="component_type" required>
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
                    </select>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-plus"></i> Agregar</button>
            </form>
        </div>
    </div>

    <!-- Modal Config Component -->
    <div id="modalConfigComponent" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2><i class="fas fa-cog"></i> Configurar Componente</h2>
                <button class="close-btn" onclick="closeModal('modalConfigComponent')">&times;</button>
            </div>
            <div id="component-config-form"></div>
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
                    <label>Imagen</label>
                    <input type="file" name="carousel_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-upload"></i> Subir</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Marca -->
    <div id="modalAddMarca" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-flag"></i> Nueva Marca</h2>
                <button class="close-btn" onclick="closeModal('modalAddMarca')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_marca">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" placeholder="Ej: Toyota" required>
                </div>
                <div class="form-group">
                    <label>Slug (opcional, se genera automáticamente)</label>
                    <input type="text" name="slug" placeholder="Ej: toyota">
                </div>
                <div class="form-group">
                    <label>Logo (opcional)</label>
                    <input type="file" name="marca_logo" accept="image/*">
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-plus"></i> Crear Marca</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Marca -->
    <div id="modalEditMarca" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Marca</h2>
                <button class="close-btn" onclick="closeModal('modalEditMarca')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_marca">
                <input type="hidden" name="marca_id" id="edit_marca_id">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="edit_marca_nombre" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" id="edit_marca_slug">
                </div>
                <div class="form-group">
                    <label>Nuevo Logo (opcional)</label>
                    <input type="file" name="marca_logo" accept="image/*">
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
                    <label>Nueva Imagen</label>
                    <input type="file" name="carousel_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Weekly -->
    <div id="modalAddWeekly" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-clock"></i> Agregar Turno</h2>
                <button class="close-btn" onclick="closeModal('modalAddWeekly')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_weekly_schedule">
                <div class="form-group">
                    <label>Día</label>
                    <select name="day_of_week" required>
                        <option value="1">Lunes</option><option value="2">Martes</option><option value="3">Miércoles</option>
                        <option value="4">Jueves</option><option value="5">Viernes</option><option value="6">Sábado</option><option value="7">Domingo</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label>Inicio</label><input type="time" name="start_time" value="09:00" required></div>
                    <div class="form-group"><label>Fin</label><input type="time" name="end_time" value="13:00" required></div>
                </div>
                <div class="form-group"><label>Duración (min)</label><input type="number" name="slot_duration" value="60" min="15" required></div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>

    <!-- Modal Add Exception -->
    <div id="modalAddException" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-times"></i> Agregar Excepción</h2>
                <button class="close-btn" onclick="closeModal('modalAddException')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_schedule_exception">
                <div class="form-group"><label>Fecha</label><input type="date" name="exception_date" required min="<?= date('Y-m-d') ?>"></div>
                <div class="form-group" style="background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_closed" id="chkIsClosed" value="1" style="width:20px; height:20px;" onchange="toggleExceptionTimes()">
                        <span style="font-weight:600; color:#dc2626;">Día Cerrado</span>
                    </label>
                </div>
                <div id="exceptionTimes">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group"><label>Inicio</label><input type="time" name="start_time" id="ex_start" value="09:00"></div>
                        <div class="form-group"><label>Fin</label><input type="time" name="end_time" id="ex_end" value="13:00"></div>
                    </div>
                    <div class="form-group"><label>Duración (min)</label><input type="number" name="slot_duration" id="ex_dur" value="60" min="15"></div>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Exception -->
    <div id="modalEditException" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Editar Excepción</h2>
                <button class="close-btn" onclick="closeModal('modalEditException')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_schedule_exception">
                <input type="hidden" name="id" id="edit_exception_id">
                <div class="form-group"><label>Fecha</label><input type="date" name="exception_date" id="edit_exception_date" required></div>
                <div class="form-group" style="background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_closed" id="edit_chkIsClosed" value="1" style="width:20px; height:20px;" onchange="toggleEditExceptionTimes()">
                        <span style="font-weight:600; color:#dc2626;">Día Cerrado</span>
                    </label>
                </div>
                <div id="edit_exceptionTimes">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group"><label>Inicio</label><input type="time" name="start_time" id="edit_ex_start"></div>
                        <div class="form-group"><label>Fin</label><input type="time" name="end_time" id="edit_ex_end"></div>
                    </div>
                    <div class="form-group"><label>Duración</label><input type="number" name="slot_duration" id="edit_ex_dur" min="15"></div>
                </div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
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
                <div class="form-group"><label>Icono</label><input type="text" name="icon" required placeholder="fas fa-handshake"></div>
                <div class="form-group"><label>Título</label><input type="text" name="title" required></div>
                <div class="form-group"><label>Descripción</label><textarea name="description" rows="3" required></textarea></div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
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
                <div class="form-group"><label>Icono</label><input type="text" name="icon" id="edit_service_icon" required></div>
                <div class="form-group"><label>Título</label><input type="text" name="title" id="edit_service_title" required></div>
                <div class="form-group"><label>Descripción</label><textarea name="description" id="edit_service_description" rows="3" required></textarea></div>
                <button type="submit" class="btn" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>

    <script>
        window.baseAppUrl = '<?= $base_url ?>';
        window.specFieldsData = <?= json_encode($spec_fields, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
        window.defaultComponentsData = <?= json_encode($default_components, JSON_HEX_TAG | JSON_HEX_APOS) ?>;

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
                localStorage.setItem('activeDashboardTab', targetTab);
                if(window.innerWidth <= 1024) toggleSidebar();
            });
        });

        const savedTab = localStorage.getItem('activeDashboardTab');
        if (savedTab) {
            const activeTab = document.querySelector(`.tab-link[data-tab="${savedTab}"]`);
            if (activeTab) {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                activeTab.classList.add('active');
                document.getElementById(savedTab).classList.add('active');
            }
        }

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        function toggleSidebar() { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); }
        document.getElementById('menuToggle').addEventListener('click', toggleSidebar);
        document.getElementById('closeSidebar').addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        window.addEventListener('click', (e) => { if(e.target.classList.contains('modal')) e.target.style.display = 'none'; });

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            toast.innerHTML = `<i class="fas ${icon}"></i><div class="toast-body">${message}</div>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
        }

        function showConfirm(msg, onConfirm, onCancel) {
            const overlay = document.getElementById('confirm-overlay');
            const modal = document.getElementById('confirm-modal');
            const message = document.getElementById('confirm-message');
            if (!overlay || !modal || !message) return;
            message.textContent = msg;
            overlay.classList.add('open');
            modal.classList.add('open');
            const yesBtn = document.getElementById('confirm-yes');
            const noBtn = document.getElementById('confirm-no');
            function cleanup() {
                overlay.classList.remove('open');
                modal.classList.remove('open');
                if (yesBtn) yesBtn.onclick = null;
                if (noBtn) noBtn.onclick = null;
            }
            if (yesBtn) { yesBtn.onclick = function() { cleanup(); if (onConfirm) onConfirm(); }; }
            if (noBtn) { noBtn.onclick = function() { cleanup(); if (onCancel) onCancel(); }; }
        }

        function toggleExceptionTimes() {
            const isClosed = document.getElementById('chkIsClosed').checked;
            const timesDiv = document.getElementById('exceptionTimes');
            timesDiv.style.opacity = isClosed ? '0.5' : '1';
            timesDiv.querySelectorAll('input').forEach(i => i.disabled = isClosed);
        }
        function toggleEditExceptionTimes() {
            const isClosed = document.getElementById('edit_chkIsClosed').checked;
            const timesDiv = document.getElementById('edit_exceptionTimes');
            timesDiv.style.opacity = isClosed ? '0.5' : '1';
            timesDiv.querySelectorAll('input').forEach(i => i.disabled = isClosed);
        }

        function autoGenerateSlug(titleId, slugId) {
            const title = document.getElementById(titleId).value;
            const slug = title.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById(slugId).value = slug;
        }

        function toggleSpecOptions() {
            const tipo = document.getElementById('sf_tipo').value;
            document.getElementById('sf_opciones_group').style.display = tipo === 'select' ? 'block' : 'none';
        }

        let specFieldsPage = 1;
        const specFieldsLimit = 10;
        let specFieldsSearchTimer = null;

        function submitSpecField(e) {
            e.preventDefault();
            const form = document.getElementById('formAddSpecField');
            const formData = new FormData(form);
            const isEdit = document.getElementById('sf_id').value !== '';
            const action = isEdit ? 'edit' : 'create';
            fetch(window.baseAppUrl + 'api/spec_fields.php?action=' + action, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(isEdit ? 'Campo actualizado exitosamente' : 'Campo creado exitosamente');
                        closeModal('modalAddSpecField');
                        form.reset();
                        document.getElementById('sf_action').value = 'create';
                        document.getElementById('sf_modal_title').textContent = 'Nuevo Campo';
                        document.getElementById('sf_submit_text').textContent = 'Guardar';
                        specFieldsPage = 1;
                        fetchSpecFields();
                    } else {
                        showToast(data.error || 'Error', 'error');
                    }
                });
        }

        function editSpecField(id) {
            const fields = window._specFieldsList || [];
            const f = fields.find(function(field) { return field.id == id; });
            if (!f) return;

            document.getElementById('sf_id').value = f.id;
            document.getElementById('sf_action').value = 'edit';
            document.getElementById('sf_nombre').value = f.nombre;
            document.getElementById('sf_slug').value = f.slug;
            document.getElementById('sf_tipo').value = f.tipo;
            document.getElementById('sf_obligatorio').checked = f.obligatorio == 1 || f.obligatorio === true;

            const opciones = document.getElementById('sf_opciones');
            if (f.opciones) {
                try {
                    const parsed = typeof f.opciones === 'string' ? JSON.parse(f.opciones) : f.opciones;
                    opciones.value = Array.isArray(parsed) ? parsed.join(', ') : parsed;
                } catch(e) {
                    opciones.value = f.opciones;
                }
            } else {
                opciones.value = '';
            }

            toggleSpecOptions();
            document.getElementById('sf_modal_title').textContent = 'Editar Campo';
            document.getElementById('sf_submit_text').textContent = 'Actualizar';
            openModal('modalAddSpecField');
        }

        function fetchSpecFields() {
            const search = document.getElementById('searchSpecFields').value;
            const filterType = document.getElementById('filterTypeSpecFields').value;
            const tbody = document.getElementById('specFieldsTbody');
            const offset = (specFieldsPage - 1) * specFieldsLimit;

            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';

            const url = window.baseAppUrl + 'api/get_spec_fields.php?limit=' + specFieldsLimit + '&offset=' + offset + '&search=' + encodeURIComponent(search) + '&filter_type=' + encodeURIComponent(filterType);

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (!data.fields || data.fields.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay campos de especificación.</td></tr>';
                        document.getElementById('specFieldsPaginationInfo').textContent = 'Mostrando 0 de 0';
                        document.getElementById('btnPrevSpecFields').disabled = true;
                        document.getElementById('btnNextSpecFields').disabled = true;
                        window._specFieldsList = [];
                        return;
                    }
                    window._specFieldsList = data.fields;
                    data.fields.forEach(function(f) {
                        const tr = document.createElement('tr');
                        const tipoLabel = { text: 'Texto', number: 'Número', select: 'Selección', color: 'Color' }[f.tipo] || f.tipo;
                        let opcionesStr = '<span style="color:#999;">—</span>';
                        if (f.opciones) {
                            try {
                                const parsed = typeof f.opciones === 'string' ? JSON.parse(f.opciones) : f.opciones;
                                opcionesStr = Array.isArray(parsed) ? parsed.join(', ') : parsed;
                            } catch(e) {
                                opcionesStr = f.opciones;
                            }
                        }
                        const obligatorioStr = f.obligatorio == 1 || f.obligatorio === true ? '<span class="badge badge-success">Sí</span>' : '<span style="color:#999;">No</span>';
                        tr.innerHTML = '<td><strong>' + f.nombre + '</strong></td>' +
                            '<td><code>' + f.slug + '</code></td>' +
                            '<td><span class="badge badge-primary">' + tipoLabel + '</span></td>' +
                            '<td><small>' + opcionesStr + '</small></td>' +
                            '<td>' + obligatorioStr + '</td>' +
                            '<td><div style="display:flex;gap:4px;">' +
                            '<button class="btn btn-warning btn-sm" onclick="editSpecField(' + f.id + ')"><i class="fas fa-edit"></i></button> ' +
                            '<button class="btn btn-danger btn-sm" onclick="deleteSpecField(' + f.id + ')"><i class="fas fa-trash"></i></button>' +
                            '</div></td>';
                        tbody.appendChild(tr);
                    });
                    const startCount = offset + 1;
                    const endCount = Math.min(offset + data.fields.length, data.total);
                    document.getElementById('specFieldsPaginationInfo').textContent = 'Mostrando ' + startCount + '-' + endCount + ' de ' + data.total;
                    document.getElementById('btnPrevSpecFields').disabled = (specFieldsPage === 1);
                    document.getElementById('btnNextSpecFields').disabled = (offset + specFieldsLimit >= data.total);
                })
                .catch(function() {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red;">Error al cargar campos.</td></tr>';
                });
        }

        function deleteSpecField(id) {
            if (!confirm('¿Eliminar este campo?')) return;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            fetch(window.baseAppUrl + 'api/spec_fields.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { showToast('Campo eliminado'); specFieldsPage = 1; fetchSpecFields(); }
                    else showToast(data.error || 'Error', 'error');
                });
        }

        document.getElementById('searchSpecFields').addEventListener('input', function() {
            clearTimeout(specFieldsSearchTimer);
            specFieldsSearchTimer = setTimeout(function() { specFieldsPage = 1; fetchSpecFields(); }, 300);
        });
        document.getElementById('filterTypeSpecFields').addEventListener('change', function() { specFieldsPage = 1; fetchSpecFields(); });
        document.getElementById('btnPrevSpecFields').addEventListener('click', function() { if (specFieldsPage > 1) { specFieldsPage--; fetchSpecFields(); } });
        document.getElementById('btnNextSpecFields').addEventListener('click', function() { specFieldsPage++; fetchSpecFields(); });

        function loadDefaultComponents() {
            fetch(window.baseAppUrl + 'api/components.php?action=list')
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('default-components-list');
                    if (!data.components || data.components.length === 0) {
                        container.innerHTML = '<p style="text-align:center; color:#64748b;">No hay componentes por defecto.</p>';
                        return;
                    }
                    container.innerHTML = data.components.map(c => {
                        const names = {hero_slider:'Hero Slider',specs_destacadas:'Specs Destacadas',descripcion:'Descripción',exterior_interior:'Exterior / Interior',image_gallery:'Galería',specs_tabla:'Tabla Specs',video:'Video',cta_whatsapp:'CTA WhatsApp',calculadora:'Calculadora',autos_relacionados:'Autos Relacionados'};
                        const hasConfig = ['hero_slider','specs_destacadas','descripcion','exterior_interior','image_gallery','autos_relacionados'].includes(c.component_type);
                        return `
                        <div class="component-item" data-id="${c.id}">
                            <i class="fas fa-grip-vertical drag-handle"></i>
                            <div class="comp-info"><strong>${names[c.component_type] || c.component_type}</strong></div>
                            <label style="display:flex;align-items:center;gap:5px;margin:0;">
                                <input type="checkbox" ${c.is_active ? 'checked' : ''} onchange="toggleComponent(${c.id}, this.checked)">
                                <small>Activo</small>
                            </label>
                            ${hasConfig ? `<button class="btn btn-warning btn-sm" onclick="openComponentConfig(${c.id}, '${c.component_type}', '${(c.config || '{}').replace(/'/g, "\\'").replace(/"/g, '&quot;')}')" title="Configurar"><i class="fas fa-cog"></i></button>` : ''}
                            <button class="btn btn-danger btn-sm" onclick="deleteComponent(${c.id})"><i class="fas fa-trash"></i></button>
                        </div>`;
                    }).join('');
                    initComponentDrag(container);
                });
        }

        function toggleComponent(id, active) {
            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('id', id);
            formData.append('component_type', 'placeholder');
            formData.append('is_active', active ? '1' : '');
            fetch(window.baseAppUrl + 'api/components.php', { method: 'POST', body: formData });
        }

        function deleteComponent(id) {
            if (!confirm('¿Eliminar este componente?')) return;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            fetch(window.baseAppUrl + 'api/components.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { if (data.success) { showToast('Componente eliminado'); loadDefaultComponents(); } });
        }

        function submitComponent(e) {
            e.preventDefault();
            const form = document.getElementById('formAddComponent');
            const formData = new FormData(form);
            fetch(window.baseAppUrl + 'api/components.php?action=create', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { showToast('Componente agregado'); closeModal('modalAddComponent'); loadDefaultComponents(); }
                });
        }

        function initComponentDrag(container) {
            let dragItem = null;
            container.querySelectorAll('.component-item').forEach(item => {
                item.setAttribute('draggable', 'true');
                item.addEventListener('dragstart', function() { dragItem = this; this.classList.add('dragging'); });
                item.addEventListener('dragend', function() { this.classList.remove('dragging'); saveComponentOrder(); });
                item.addEventListener('dragover', function(e) { e.preventDefault(); });
                item.addEventListener('dragenter', function(e) { e.preventDefault(); if (dragItem && dragItem !== this) { const all = [...container.querySelectorAll('.component-item')]; const dragIdx = all.indexOf(dragItem); const thisIdx = all.indexOf(this); if (dragIdx < thisIdx) this.after(dragItem); else this.before(dragItem); } });
            });
        }

        function saveComponentOrder() {
            const container = document.getElementById('default-components-list');
            const order = [...container.querySelectorAll('.component-item')].map(el => el.dataset.id);
            const formData = new FormData();
            formData.append('action', 'reorder');
            order.forEach((id, i) => formData.append(`order[${i}]`, id));
            fetch(window.baseAppUrl + 'api/components.php', { method: 'POST', body: formData });
        }

        let currentConfigCompId = null;
        let currentConfigCompType = '';

        function openComponentConfig(id, type, configStr) {
            currentConfigCompId = id;
            currentConfigCompType = type;
            let config = {};
            try { config = JSON.parse(configStr); } catch(e) {}
            const form = document.getElementById('component-config-form');
            let html = '<form id="formConfigComponent" onsubmit="saveComponentConfig(event)">';

            if (type === 'hero_slider') {
                html += '<div class="form-group"><label><input type="checkbox" name="show_title" ' + (config.show_title !== false ? 'checked' : '') + '> Mostrar Título</label></div>';
                html += '<div class="form-group"><label><input type="checkbox" name="show_price" ' + (config.show_price !== false ? 'checked' : '') + '> Mostrar Precio</label></div>';
            } else if (type === 'specs_destacadas') {
                html += '<div class="form-group"><label>Máx. items</label><input type="number" name="max_items" value="' + (config.max_items || 6) + '" min="1" max="12"></div>';
            } else if (type === 'descripcion') {
                html += '<div class="form-group"><label>Imagen (opcional)</label><div id="desc-img-preview">' + (config.image ? '<img src="'+window.baseAppUrl+config.image+'" style="max-width:200px;border-radius:8px;margin-bottom:8px;"><br><button type="button" class="btn btn-sm btn-danger" onclick="removeDescImage()">Quitar</button>' : '<p style="color:#94a3b8;">Sin imagen</p>') + '</div><input type="file" id="desc-image-upload" accept="image/*" onchange="uploadDescImage(this)"><input type="hidden" name="image" id="desc_image_path" value="' + (config.image || '') + '"></div>';
                html += '<div class="form-group"><label>Posición de imagen</label><select name="image_position"><option value="left" ' + (config.image_position === 'right' ? '' : 'selected') + '>Izquierda</option><option value="right" ' + (config.image_position === 'right' ? 'selected' : '') + '>Derecha</option></select></div>';
            } else if (type === 'exterior_interior') {
                html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';
                html += '<div><h4 style="margin:0 0 15px;color:var(--primary-color);">Exterior</h4>';
                html += '<div class="form-group"><label>Título</label><input type="text" name="exterior_title" value="' + escHtml(config.exterior_title || 'Exterior') + '"></div>';
                html += '<div class="form-group"><label>Descripción</label><textarea name="exterior_description" rows="4">' + escHtml(config.exterior_description || '') + '</textarea></div>';
                html += '<div class="form-group"><label>Imagen</label><div id="ext-img-preview">' + (config.exterior_image ? '<img src="'+window.baseAppUrl+config.exterior_image+'" style="max-width:200px;border-radius:8px;margin-bottom:8px;"><br><button type="button" class="btn btn-sm btn-danger" onclick="removeEIImage(\'exterior\')">Quitar</button>' : '<p style="color:#94a3b8;">Sin imagen</p>') + '</div><input type="file" id="ext-image-upload" accept="image/*" onchange="uploadEIImage(this,\'exterior\')"><input type="hidden" name="exterior_image" id="exterior_image_path" value="' + (config.exterior_image || '') + '"></div></div>';
                html += '<div><h4 style="margin:0 0 15px;color:var(--primary-color);">Interior</h4>';
                html += '<div class="form-group"><label>Título</label><input type="text" name="interior_title" value="' + escHtml(config.interior_title || 'Interior') + '"></div>';
                html += '<div class="form-group"><label>Descripción</label><textarea name="interior_description" rows="4">' + escHtml(config.interior_description || '') + '</textarea></div>';
                html += '<div class="form-group"><label>Imagen</label><div id="int-img-preview">' + (config.interior_image ? '<img src="'+window.baseAppUrl+config.interior_image+'" style="max-width:200px;border-radius:8px;margin-bottom:8px;"><br><button type="button" class="btn btn-sm btn-danger" onclick="removeEIImage(\'interior\')">Quitar</button>' : '<p style="color:#94a3b8;">Sin imagen</p>') + '</div><input type="file" id="int-image-upload" accept="image/*" onchange="uploadEIImage(this,\'interior\')"><input type="hidden" name="interior_image" id="interior_image_path" value="' + (config.interior_image || '') + '"></div></div>';
                html += '</div>';
            } else if (type === 'autos_relacionados') {
                html += '<div class="form-group"><label>Máx. autos</label><input type="number" name="max_items" value="' + (config.max_items || 4) + '" min="1" max="8"></div>';
            } else if (type === 'image_gallery') {
                html += '<div class="form-group"><label>Layout</label><select name="layout"><option value="grid" ' + (config.layout !== 'masonry' ? 'selected' : '') + '>Grid</option><option value="masonry" ' + (config.layout === 'masonry' ? 'selected' : '') + '>Masonry</option></select></div>';
            }

            html += '<button type="submit" class="btn" style="width:100%;margin-top:15px;"><i class="fas fa-save"></i> Guardar Configuración</button></form>';
            form.innerHTML = html;
            openModal('modalConfigComponent');
        }

        function escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function saveComponentConfig(e) {
            e.preventDefault();
            const form = document.getElementById('formConfigComponent');
            const config = {};
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(function(inp) {
                if (inp.type === 'checkbox') config[inp.name] = inp.checked;
                else if (inp.type === 'hidden') { if (inp.value) config[inp.name] = inp.value; }
                else if (inp.name) config[inp.name] = inp.value;
            });
            const data = new FormData();
            data.append('action', 'edit');
            data.append('id', currentConfigCompId);
            data.append('component_type', currentConfigCompType);
            data.append('config', JSON.stringify(config));
            data.append('is_active', '1');
            fetch(window.baseAppUrl + 'api/components.php', { method: 'POST', body: data })
                .then(res => res.json())
                .then(function(res) {
                    if (res.success) { showToast('Configuración guardada'); closeModal('modalConfigComponent'); loadDefaultComponents(); }
                });
        }

        function uploadDescImage(input) {
            if (!input.files || !input.files[0]) return;
            var fd = new FormData();
            fd.append('action', 'upload_component_image');
            fd.append('car_id', '0');
            fd.append('component_type', 'descripcion');
            fd.append('field', 'image');
            fd.append('image', input.files[0]);
            fetch(window.baseAppUrl + 'api/car_media.php', { method: 'POST', body: fd })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        document.getElementById('desc_image_path').value = data.path;
                        document.getElementById('desc-img-preview').innerHTML = '<img src="'+window.baseAppUrl+data.path+'" style="max-width:200px;border-radius:8px;margin-bottom:8px;"><br><button type="button" class="btn btn-sm btn-danger" onclick="removeDescImage()">Quitar</button>';
                    }
                });
        }

        function removeDescImage() {
            document.getElementById('desc_image_path').value = '';
            document.getElementById('desc-img-preview').innerHTML = '<p style="color:#94a3b8;">Sin imagen</p>';
        }

        function uploadEIImage(input, side) {
            if (!input.files || !input.files[0]) return;
            var fd = new FormData();
            fd.append('action', 'upload_component_image');
            fd.append('car_id', '0');
            fd.append('component_type', 'exterior_interior');
            fd.append('field', side + '_image');
            fd.append('image', input.files[0]);
            fetch(window.baseAppUrl + 'api/car_media.php', { method: 'POST', body: fd })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        var prefix = side === 'exterior' ? 'ext' : 'int';
                        document.getElementById(side + '_image_path').value = data.path;
                        document.getElementById(prefix + '-img-preview').innerHTML = '<img src="'+window.baseAppUrl+data.path+'" style="max-width:200px;border-radius:8px;margin-bottom:8px;"><br><button type="button" class="btn btn-sm btn-danger" onclick="removeEIImage(\''+side+'\')">Quitar</button>';
                    }
                });
        }

        function removeEIImage(side) {
            document.getElementById(side + '_image_path').value = '';
            var prefix = side === 'exterior' ? 'ext' : 'int';
            document.getElementById(prefix + '-img-preview').innerHTML = '<p style="color:#94a3b8;">Sin imagen</p>';
        }

        function addSpecRow(containerId, field) {
            const container = document.getElementById(containerId);
            const row = document.createElement('div');
            row.className = 'spec-row';
            row.innerHTML = `
                <select name="spec_field_id[]">
                    <option value="">-- Campo --</option>
                    ${window.specFieldsData.map(f => `<option value="${f.id}" ${field && field.spec_field_id == f.id ? 'selected' : ''}>${f.nombre}</option>`).join('')}
                </select>
                <input type="text" name="spec_value[]" placeholder="Valor" value="${field ? field.valor : ''}">
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(row);
        }

        function addCustomSpecRow(containerId) {
            const container = document.getElementById(containerId);
            const row = document.createElement('div');
            row.className = 'spec-row';
            row.innerHTML = `
                <input type="text" name="spec_custom_label[]" placeholder="Etiqueta (ej: Dueños anteriores)">
                <input type="text" name="spec_custom_value[]" placeholder="Valor">
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(row);
        }

        function editCar(id, title, slug, price, description, status, featured, marca_id, modelo) {
            document.getElementById('edit_car_id').value = id;
            document.getElementById('edit_car_title').value = title;
            document.getElementById('edit_car_slug').value = slug;
            document.getElementById('edit_car_price').value = price;
            document.getElementById('edit_car_description').value = description || '';
            document.getElementById('edit_car_status').value = status || 'active';
            document.getElementById('edit_car_featured').checked = featured == 1;
            if (marca_id) document.getElementById('edit_car_marca').value = marca_id;
            if (modelo) document.getElementById('edit_car_modelo').value = modelo;
            document.getElementById('edit-car-specs').innerHTML = '';
            openModal('modalEditCar');
        }

        function editCarousel(id) {
            document.getElementById('edit_carousel_id').value = id;
            openModal('modalEditCarousel');
        }

        function editMarca(id, nombre, slug) {
            document.getElementById('edit_marca_id').value = id;
            document.getElementById('edit_marca_nombre').value = nombre;
            document.getElementById('edit_marca_slug').value = slug;
            openModal('modalEditMarca');
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

        let carsPage = parseInt(new URLSearchParams(window.location.search).get('page')) || 1;
        const carsLimit = 10;
        let carsSearchTimer = null;

        function fetchCars() {
            const search = document.getElementById('searchCars').value;
            const shorthand = document.getElementById('filterShorthandCars').value;
            const priceFilter = document.getElementById('filterPriceCars').value;
            const tbody = document.getElementById('carsTbody');
            const offset = (carsPage - 1) * carsLimit;
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
            const url = `${window.baseAppUrl}api/get_cars.php?limit=${carsLimit}&offset=${offset}&search=${encodeURIComponent(search)}&shorthand=${shorthand}&price_filter=${priceFilter}`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';
                    document.getElementById('selectAllCars').checked = false;
                    updateBulkActionsCarsVisibility();
                    if (!data.cars || data.cars.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay autos.</td></tr>';
                        document.getElementById('carsPaginationInfo').textContent = 'Mostrando 0 de 0';
                        document.getElementById('btnPrevCars').disabled = true;
                        document.getElementById('btnNextCars').disabled = true;
                        return;
                    }
                    data.cars.forEach(car => {
                        const tr = document.createElement('tr');
                        const formattedPrice = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(car.price);
                        const statusClass = car.status === 'active' ? 'status-active' : car.status === 'draft' ? 'status-draft' : 'status-sold';
                        const statusLabel = car.status === 'active' ? 'Activo' : car.status === 'draft' ? 'Borrador' : 'Vendido';
                        tr.innerHTML = `
                            <td style="text-align: center;"><input type="checkbox" class="car-checkbox" value="${car.id}" style="width: 18px; height: 18px; cursor: pointer;"></td>
                            <td><img src="${car.image_path || ''}" alt="car" style="width: 80px; border-radius: 4px;"></td>
                            <td><strong>${car.title}</strong></td>
                            <td><small>${car.slug}</small></td>
                            <td>${formattedPrice}</td>
                            <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                            <td>
                                <a href="<?= $base_url ?>admin/auto/editar/${car.id}?id=${car.id}&page=${carsPage}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteCar(${car.id})"><i class="fas fa-trash"></i></button>
                                <form method="POST" style="display:none;" id="formDeleteCar${car.id}">
                                    <input type="hidden" name="action" value="delete_car">
                                    <input type="hidden" name="car_id" value="${car.id}">
                                </form>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                    document.querySelectorAll('.car-checkbox').forEach(chk => chk.addEventListener('change', updateBulkActionsCarsVisibility));
                    const startCount = offset + 1;
                    const endCount = Math.min(offset + data.cars.length, data.total);
                    document.getElementById('carsPaginationInfo').textContent = `Mostrando ${startCount}-${endCount} de ${data.total}`;
                    document.getElementById('btnPrevCars').disabled = (carsPage === 1);
                    document.getElementById('btnNextCars').disabled = (offset + carsLimit >= data.total);
                })
                .catch(err => { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Error</td></tr>'; });
        }

        document.getElementById('selectAllCars').addEventListener('change', (e) => {
            const checked = e.target.checked;
            document.querySelectorAll('.car-checkbox').forEach(chk => chk.checked = checked);
            updateBulkActionsCarsVisibility();
        });

        function updateBulkActionsCarsVisibility() {
            const totalChecked = document.querySelectorAll('.car-checkbox:checked').length;
            const bulkBar = document.getElementById('bulkActionsCars');
            if (totalChecked > 0) {
                bulkBar.style.display = 'flex';
                document.getElementById('bulkSelectCountCars').textContent = `${totalChecked} auto(s)`;
            } else { bulkBar.style.display = 'none'; }
        }

        function confirmDeleteCar(id) {
            showConfirm('¿Eliminar este auto?', function() {
                document.getElementById('formDeleteCar' + id).submit();
            });
        }

        function confirmDeleteAppointment(id) {
            showConfirm('¿Eliminar esta cita?', function() {
                document.getElementById('formDeleteAppt' + id).submit();
            });
        }

        function submitBulkDeleteCars() {
            const ids = Array.from(document.querySelectorAll('.car-checkbox:checked')).map(chk => chk.value);
            if (ids.length === 0) return;
            showConfirm('¿Eliminar ' + ids.length + ' autos?', function() {
                document.getElementById('bulkDeleteCarIds').value = ids.join(',');
                document.getElementById('formBulkDeleteCars').submit();
            });
        }

        document.getElementById('searchCars').addEventListener('input', () => {
            clearTimeout(carsSearchTimer);
            carsSearchTimer = setTimeout(() => { carsPage = 1; fetchCars(); }, 300);
        });
        document.getElementById('filterShorthandCars').addEventListener('change', () => { carsPage = 1; fetchCars(); });
        document.getElementById('filterPriceCars').addEventListener('change', () => { carsPage = 1; fetchCars(); });
        document.getElementById('btnPrevCars').addEventListener('click', () => { if (carsPage > 1) { carsPage--; fetchCars(); } });
        document.getElementById('btnNextCars').addEventListener('click', () => { carsPage++; fetchCars(); });

        let appointmentsPage = 1;
        const appointmentsLimit = 10;
        let appointmentsSearchTimer = null;

        function fetchAppointments() {
            const search = document.getElementById('searchAppointments').value;
            const dateFilter = document.getElementById('filterDateAppointments').value;
            const shorthand = document.getElementById('filterShorthandAppointments').value;
            const tbody = document.getElementById('appointmentsTbody');
            const offset = (appointmentsPage - 1) * appointmentsLimit;
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
            const url = `${window.baseAppUrl}api/get_appointments.php?limit=${appointmentsLimit}&offset=${offset}&search=${encodeURIComponent(search)}&date_filter=${dateFilter}&shorthand=${shorthand}`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (!data.appointments || data.appointments.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay citas.</td></tr>';
                        document.getElementById('appointmentsPaginationInfo').textContent = 'Mostrando 0 de 0';
                        document.getElementById('btnPrevAppointments').disabled = true;
                        document.getElementById('btnNextAppointments').disabled = true;
                        return;
                    }
                    data.appointments.forEach(appt => {
                        const dateFormatted = new Date(appt.appointment_date + 'T00:00:00').toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                        const timeParts = appt.appointment_time.split(':');
                        const hours = parseInt(timeParts[0]);
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        const timeFormatted = `${hours % 12 || 12}:${timeParts[1]} ${ampm}`;
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
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAppointment(${appt.id})"><i class="fas fa-trash"></i></button>
                                <form method="POST" style="display:none;" id="formDeleteAppt${appt.id}">
                                    <input type="hidden" name="action" value="delete_appointment">
                                    <input type="hidden" name="id" value="${appt.id}">
                                </form>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                    const startCount = offset + 1;
                    const endCount = Math.min(offset + data.appointments.length, data.total);
                    document.getElementById('appointmentsPaginationInfo').textContent = `Mostrando ${startCount}-${endCount} de ${data.total}`;
                    document.getElementById('btnPrevAppointments').disabled = (appointmentsPage === 1);
                    document.getElementById('btnNextAppointments').disabled = (offset + appointmentsLimit >= data.total);
                })
                .catch(err => { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error</td></tr>'; });
        }

        document.getElementById('searchAppointments').addEventListener('input', () => {
            clearTimeout(appointmentsSearchTimer);
            appointmentsSearchTimer = setTimeout(() => { appointmentsPage = 1; fetchAppointments(); }, 300);
        });
        document.getElementById('filterDateAppointments').addEventListener('change', () => { appointmentsPage = 1; fetchAppointments(); });
        document.getElementById('filterShorthandAppointments').addEventListener('change', () => { appointmentsPage = 1; fetchAppointments(); });
        document.getElementById('btnPrevAppointments').addEventListener('click', () => { if (appointmentsPage > 1) { appointmentsPage--; fetchAppointments(); } });
        document.getElementById('btnNextAppointments').addEventListener('click', () => { appointmentsPage++; fetchAppointments(); });

        <?php if($toast_msg): ?>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("<?= htmlspecialchars($toast_msg) ?>", "<?= $toast_type ?>");
            });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', () => {
            fetchCars();
            fetchAppointments();
            fetchSpecFields();
            loadDefaultComponents();
            // Populate spec fields in add car form
            const addSpecs = document.getElementById('add-car-specs');
            window.specFieldsData.forEach(f => {
                if (f.obligatorio) {
                    addSpecRow('add-car-specs', { spec_field_id: f.id, valor: '' });
                }
            });
        });
    </script>
</body>
</html>
