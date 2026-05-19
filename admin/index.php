<?php
require_once __DIR__ . '/../api/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . $base_url . "admin/dashboard");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Credenciales hardcodeadas por ahora, como se acordó en el plan
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: " . $base_url . "admin/dashboard");
        exit;
    } else {
        $error = 'Credenciales incorrectas. Intenta de nuevo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tu Auto Con</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-box {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .logo-container i {
            font-size: 40px;
            color: #0B192C;
            margin-bottom: 15px;
        }
        h2 { 
            margin-top: 0; 
            color: #0B192C; 
            text-align: center; 
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            color: #555; 
            font-weight: 500; 
            font-size: 0.9rem;
        }
        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        input[type="text"], input[type="password"] {
            width: 100%; 
            padding: 12px 12px 12px 40px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #0B192C;
            box-shadow: 0 0 0 3px rgba(11, 25, 44, 0.1);
        }
        button {
            width: 100%; 
            padding: 14px; 
            background-color: #0B192C; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.1s;
        }
        button:hover { 
            background-color: #1a365d; 
        }
        button:active {
            transform: scale(0.98);
        }
        .error { 
            background-color: #fef2f2;
            color: #b91c1c; 
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #0B192C;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo-container">
                <i class="fas fa-car-side"></i>
                <h2>Panel de Administración</h2>
            </div>
            
            <?php if($error): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Usuario</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Ingresa tu usuario" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit">INGRESAR AL PANEL</button>
            </form>
        </div>
        <a href="<?= $base_url ?>" class="back-link"><i class="fas fa-arrow-left"></i> Volver al sitio web</a>
    </div>
</body>
</html>
