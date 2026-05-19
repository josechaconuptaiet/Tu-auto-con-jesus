<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 - No Encontrado</title>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .error-container h1 { font-size: 5rem; color: var(--primary-color); }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>La página que buscas no existe.</p>
        <a href="<?= $base_url ?>" class="btn btn-primary" style="margin-top: 20px;">VOLVER AL INICIO</a>
    </div>
</body>
</html>
