<?php
// ver_notificaciones.php

session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado (solo pacientes pueden ver sus notificaciones aquí)
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'paciente') {
    header("Location: ingresar.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje_pagina = '';
$notificaciones = [];

try {
    // 1. Obtener todas las notificaciones del usuario
    $stmt_notificaciones = $pdo->prepare("SELECT id, tipo, mensaje, enlace, leida, fecha_creacion FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
    $stmt_notificaciones->execute([$usuario_id]);
    $notificaciones = $stmt_notificaciones->fetchAll(PDO::FETCH_ASSOC);

    // 2. Marcar las notificaciones como leídas (una vez que el paciente las ve en esta página)
    // Esto es opcional, puedes hacer que se marquen al hacer clic, o que se marquen todas al entrar.
    // Para simplificar, las marcaremos todas como leídas al visitar la página.
    $stmt_marcar_leidas = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ? AND leida = FALSE");
    $stmt_marcar_leidas->execute([$usuario_id]);

} catch (PDOException $e) {
    $mensaje_pagina = "<p style='color: red;'>Error al cargar las notificaciones: " . $e->getMessage() . "</p>";
}
?>

<style>
    .notification-item {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .notification-item.unread {
        background-color: #e0f7fa; /* Un color distinto para notificaciones no leídas */
        border-color: #00bcd4;
        font-weight: bold;
    }
    .notification-content {
        flex-grow: 1;
    }
    .notification-date {
        font-size: 0.9em;
        color: #666;
        margin-left: 20px;
        white-space: nowrap; /* Evita que la fecha se rompa en varias líneas */
    }
    .notification-link {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
        margin-top: 5px;
        display: inline-block;
    }
    .notification-link:hover {
        text-decoration: underline;
    }
</style>

<section class="content-section">
    <h2>Mis Notificaciones</h2>
    <?php echo $mensaje_pagina; ?>

    <?php if (empty($notificaciones)): ?>
        <p>No tienes notificaciones en este momento.</p>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notificaciones as $notif): ?>
                <div class="notification-item <?php echo $notif['leida'] ? '' : 'unread'; ?>">
                    <div class="notification-content">
                        <p><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                        <?php if ($notif['enlace']): ?>
                            <a href="<?php echo htmlspecialchars($notif['enlace']); ?>" class="notification-link">Ver más detalles</a>
                        <?php endif; ?>
                    </div>
                    <span class="notification-date">
                        <?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p style="margin-top: 30px;"><a href="dashboard_paciente.php" class="btn" style="background-color: #6c757d;">Volver al Dashboard</a></p>
</section>

<?php include 'includes/footer.php'; ?>