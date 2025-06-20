<?php
// includes/header.php

// Asegúrate de que la sesión ya esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Asegúrate de que db_config.php esté incluido para tener la conexión $pdo disponible.
// Es una buena práctica que todas las páginas que incluyan header.php también incluyan db_config.php
// antes de header.php, o puedes incluirlo directamente aquí si header.php siempre necesita la conexión.
// Por ejemplo:
// include_once 'db_config.php'; // Usa include_once para evitar errores si ya está incluido

$num_notificaciones_no_leidas = 0;
// Solo intenta obtener notificaciones si el usuario está logueado y $pdo está disponible
if (isset($_SESSION['usuario_id']) && isset($pdo)) {
    try {
        $stmt_notif = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = FALSE");
        $stmt_notif->execute([$_SESSION['usuario_id']]);
        $num_notificaciones_no_leidas = $stmt_notif->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al contar notificaciones en header.php: " . $e->getMessage());
        // En un entorno de producción, es mejor no mostrar el error al usuario final.
        // La cuenta se mantendrá en 0.
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apoyo Psicológico</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notification-badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8em;
            position: absolute;
            top: -5px; /* Ajusta según tu diseño */
            right: -10px; /* Ajusta según tu diseño */
            line-height: 1; /* Para centrar verticalmente el número */
            min-width: 16px; /* Asegura un tamaño mínimo para números de un dígito */
            text-align: center;
        }
        nav ul li a {
            position: relative; /* Necesario para posicionar el badge */
            display: inline-block; /* Para que el padding y top/right funcionen bien */
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php">APOYO PSICOLOGICO</a>
            </div>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="quienes_somos.php">Quiénes Somos</a></li>
                <li><a href="preguntas_frecuentes.php">Preguntas Frecuentes</a></li>

                <?php if (isset($_SESSION['usuario_id'])): // Si el usuario está logueado ?>
                    <?php if ($_SESSION['tipo_usuario'] === 'paciente'): ?>
                        <li><a href="dashboard_paciente.php">Dashboard</a></li>
                        <li>
                            <a href="ver_notificaciones.php">
                                Notificaciones
                                <?php if ($num_notificaciones_no_leidas > 0): ?>
                                    <span class="notification-badge">
                                        <?php echo $num_notificaciones_no_leidas; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php elseif ($_SESSION['tipo_usuario'] === 'especialista'): ?>
                        <li><a href="dashboard_especialista.php">Dashboard</a></li>
                        <?php endif; ?>
                    <li><a href="perfil.php">Mi Perfil</a></li>
                    <li><a href="salir.php">Cerrar Sesión</a></li>
                <?php else: // Si el usuario no está logueado ?>
                    <li><a href="ingresar.php">Ingresar</a></li>
                    <li><a href="registrarse.php">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>