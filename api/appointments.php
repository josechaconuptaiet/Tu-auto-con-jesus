<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($method === 'GET') {
    if ($action === 'available_dates') {
        $dates = [];
        $current_time = time();
        
        // Fetch weekly schedule
        $stmt = $pdo->query("SELECT DISTINCT day_of_week FROM weekly_schedule");
        $active_days = $stmt->fetchAll(PDO::FETCH_COLUMN); // array of e.g. [1, 2, 3, 4, 5]
        
        // Fetch exceptions (closed or modified)
        $stmt = $pdo->query("SELECT exception_date, is_closed FROM schedule_exceptions WHERE exception_date >= CURDATE()");
        $exceptions_db = $stmt->fetchAll();
        $exceptions = [];
        foreach ($exceptions_db as $ex) {
            $exceptions[$ex['exception_date']] = $ex['is_closed'] == 1;
        }

        // Fetch settings
        $settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings_db = $settings_stmt->fetchAll();
        $settings = [];
        foreach ($settings_db as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }
        $window_days = isset($settings['appointment_window_days']) ? (int)$settings['appointment_window_days'] : 30;

        // Loop next days
        for ($i = 0; $i < $window_days; $i++) {
            $timestamp = strtotime("+$i days", $current_time);
            $date_str = date('Y-m-d', $timestamp);
            $day_of_week = date('N', $timestamp); // 1 (for Monday) through 7 (for Sunday)
            
            $is_available = false;

            if (isset($exceptions[$date_str])) {
                // If it's in exceptions, it's available only if it's NOT closed
                if (!$exceptions[$date_str]) {
                    $is_available = true;
                }
            } else {
                // Not an exception, check weekly schedule
                if (in_array($day_of_week, $active_days)) {
                    $is_available = true;
                }
            }

            if ($is_available) {
                $dates[] = $date_str;
            }
        }
        
        echo json_encode(['dates' => $dates]);
        exit;
    }

    if ($action === 'available_slots') {
        $date = $_GET['date'] ?? '';
        if (!$date) {
            echo json_encode(['error' => 'Fecha es requerida']);
            exit;
        }

        $all_slots = [];
        
        // Check if there is an exception for this date
        $stmt = $pdo->prepare("SELECT start_time, end_time, slot_duration, is_closed FROM schedule_exceptions WHERE exception_date = ?");
        $stmt->execute([$date]);
        $exception = $stmt->fetch();

        if ($exception) {
            if ($exception['is_closed'] == 1) {
                echo json_encode(['slots' => []]);
                exit;
            } else {
                // Generate slots based on exception
                $start = strtotime($date . ' ' . $exception['start_time']);
                $end = strtotime($date . ' ' . $exception['end_time']);
                $duration = $exception['slot_duration'] * 60;

                for ($time = $start; $time + $duration <= $end; $time += $duration) {
                    $all_slots[] = date('H:i:s', $time);
                }
            }
        } else {
            // Check weekly schedule
            $timestamp = strtotime($date);
            $day_of_week = date('N', $timestamp);
            
            $stmt = $pdo->prepare("SELECT start_time, end_time, slot_duration FROM weekly_schedule WHERE day_of_week = ? ORDER BY start_time ASC");
            $stmt->execute([$day_of_week]);
            $blocks = $stmt->fetchAll();

            if (empty($blocks)) {
                echo json_encode(['slots' => []]);
                exit;
            }

            foreach ($blocks as $block) {
                $start = strtotime($date . ' ' . $block['start_time']);
                $end = strtotime($date . ' ' . $block['end_time']);
                $duration = $block['slot_duration'] * 60;

                for ($time = $start; $time + $duration <= $end; $time += $duration) {
                    $slot_time = date('H:i:s', $time);
                    if (!in_array($slot_time, $all_slots)) {
                        $all_slots[] = $slot_time;
                    }
                }
            }
        }

        // Get booked slots for the date
        $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ?");
        $stmt->execute([$date]);
        $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Filter out booked slots
        $available_slots = array_values(array_diff($all_slots, $booked_slots));

        echo json_encode(['slots' => $available_slots]);
        exit;
    }
}

if ($method === 'POST') {
    if ($action === 'book') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $date = $_POST['appointment_date'] ?? '';
        $time = $_POST['appointment_time'] ?? '';

        if (!$first_name || !$last_name || !$phone || !$date || !$time) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (first_name, last_name, phone, appointment_date, appointment_time) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $phone, $date, $time]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (Duplicate entry)
                echo json_encode(['success' => false, 'error' => 'Ese horario ya fue reservado. Por favor, elige otro.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al reservar la cita.']);
            }
        }
        exit;
    }
}

echo json_encode(['error' => 'Acción inválida']);
