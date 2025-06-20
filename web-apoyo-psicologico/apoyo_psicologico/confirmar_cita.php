<?php
// confirmar_cita.php (Ejemplo)

session_start();
include 'includes/db_config.php';
include 'includes/funciones.php'; // Incluir nuestro nuevo archivo de funciones

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$cita_id = $_GET['id'] ?? null;

if ($cita_id) {
    try {
        // 1. Cambiar el estado de la cita a 'confirmada'
        $stmt_update = $pdo->prepare("UPDATE citas SET estado = 'confirmada' WHERE id = ?");
        $stmt_update->execute([$cita_id]);

        // 2. Obtener los detalles de la cita para la notificación
        $stmt_cita = $pdo->prepare("SELECT paciente_id, fecha_cita FROM citas WHERE id = ?");
        $stmt_cita->execute([$cita_id]);
        $cita = $stmt_cita->fetch(PDO::FETCH_ASSOC);

        if ($cita) {
            $paciente_id = $cita['paciente_id'];
            $fecha_cita = date('d/m/Y H:i', strtotime($cita['fecha_cita']));
            $mensaje = "Tu cita para el " . $fecha_cita . " ha sido confirmada. ¡Te esperamos!";
            $enlace = "dashboard_paciente.php?ver_citas=true"; // Enlace a la sección de citas del paciente

            // 3. Crear la notificación para el paciente
            crearNotificacion($pdo, $paciente_id, 'cita_confirmada', $mensaje, $enlace);
        }

        header("Location: ver_citas_especialista.php?mensaje=Cita%20confirmada%20y%20notificada%20al%20paciente.");
        exit();

    } catch (PDOException $e) {
        header("Location: ver_citas_especialista.php?error=Error%20al%20confirmar%20cita:%20" . $e->getMessage());
        exit();
    }
} else {
    header("Location: ver_citas_especialista.php?error=ID%20de%20cita%20no%20proporcionado.");
    exit();
}
?>