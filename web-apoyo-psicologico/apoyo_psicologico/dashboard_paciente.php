<?php
session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Incluimos el archivo de conexión a la base de datos

// Verificar si el usuario está logueado y es paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'paciente') {
    header("Location: ingresar.php"); // Redirigir si no está logueado o no es paciente
    exit();
}

$paciente_id = $_SESSION['usuario_id']; // Obtenemos el ID del paciente logueado

// Lógica para obtener el mensaje de la URL si existe (útil para notificaciones)
$mensaje = '';
if (isset($_GET['mensaje'])) {
    $mensaje = '<p style="color: green; font-weight: bold; text-align: center; margin-bottom: 20px;">' . htmlspecialchars($_GET['mensaje']) . '</p>';
}
if (isset($_GET['error'])) {
    $mensaje = '<p style="color: red; font-weight: bold; text-align: center; margin-bottom: 20px;">' . htmlspecialchars($_GET['error']) . '</p>';
}


// Consulta SQL para obtener las citas del paciente, incluyendo el enlace de videollamada
// Solo se muestran citas 'confirmada' o 'pendiente'
$stmt_citas = $pdo->prepare("
    SELECT
        c.id,
        c.fecha_cita,
        c.estado,
        c.enlace_videollamada,
        u.nombre_usuario AS especialista_nombre
    FROM citas c
    JOIN usuarios u ON c.especialista_id = u.id
    WHERE c.paciente_id = ? AND (c.estado = 'confirmada' OR c.estado = 'pendiente')
    ORDER BY c.fecha_cita ASC
");
$stmt_citas->execute([$paciente_id]);
$citas_paciente = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);

?>

<style>
    /* Estilos específicos para el dashboard del paciente */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f7f6; /* Fondo más suave */
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .content-section {
        background-color: #ffffff; /* Blanco puro para la sección principal */
        border: 1px solid #e0e0e0; /* Borde suave */
        padding: 40px;
        margin-top: 50px; /* Margen superior para separarlo del header */
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* Sombra más pronunciada pero suave */
        margin-left: auto;
        margin-right: auto;
        max-width: 950px; /* Ancho máximo aumentado para mejor visualización de tabla */
        box-sizing: border-box; /* Incluye padding y border en el ancho total */
    }

    .content-section h2 {
        color: #2c3e50; /* Un azul/gris oscuro para el título */
        margin-bottom: 25px;
        font-size: 2.5em;
        font-weight: 600;
    }

    .content-section p {
        color: #555;
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .content-section ul {
        list-style: none;
        padding: 0;
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }

    .content-section ul li {
        margin-bottom: 0;
    }

    .content-section ul li a {
        display: inline-block;
        background-color: #5cb8e4; /* Azul claro para los enlaces */
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }

    .content-section ul li a:hover {
        background-color: #48a6d0; /* Azul más oscuro al pasar el ratón */
        transform: translateY(-2px);
    }

    /* Estilo general para botones (usado por cerrar sesión y videollamada) */
    .btn {
        display: inline-block;
        color: white;
        padding: 10px 20px; /* Ajuste el padding para un mejor aspecto */
        border-radius: 8px;
        font-weight: bold;
        text-decoration: none;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none; /* Quitar borde por defecto de botones */
        cursor: pointer; /* Añadir cursor de puntero */
    }

    /* Estilo para el botón de cerrar sesión */
    .btn-logout {
        background-color: #e74c3c; /* Rojo intenso */
        margin-top: 30px; /* Separación de la tabla */
    }

    .btn-logout:hover {
        background-color: #c0392b; /* Rojo más oscuro al pasar el ratón */
        transform: translateY(-2px);
    }

    /* Estilos para la tabla de citas */
    .table-container {
        overflow-x: auto; /* Permite scroll horizontal en tablas pequeñas */
        margin-top: 35px; /* Más espacio */
        margin-bottom: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); /* Sombra suave para la tabla */
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 650px; /* Asegura que la tabla no sea demasiado pequeña */
        margin: 0 auto;
        background-color: #fff;
    }

    th, td {
        padding: 15px; /* Más padding para celdas */
        text-align: left;
        border-bottom: 1px solid #f0f0f0; /* Solo borde inferior */
        vertical-align: middle; /* Centrado vertical */
    }

    th {
        background-color: #f7f9fa; /* Fondo más claro para encabezados */
        color: #333;
        font-weight: bold;
        text-transform: uppercase; /* Mayúsculas para encabezados */
        font-size: 0.95em;
    }

    tbody tr:nth-child(odd) {
        background-color: #fdfefe;
    }

    tbody tr:hover {
        background-color: #eef7fc; /* Un azul muy claro al pasar el ratón */
    }

    /* Estilo específico para el botón de videollamada en la tabla */
    .btn-videollamada {
        background-color: #28a745; /* Verde para videollamada (similar a unirse) */
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
        font-weight: bold;
        transition: background-color 0.3s ease;
        display: inline-block; /* Para que no ocupe toda la línea */
        white-space: nowrap; /* Evitar que el texto se rompa */
    }

    .btn-videollamada:hover {
        background-color: #218838; /* Verde más oscuro */
        transform: translateY(-1px);
    }

    /* Estilos para mensajes de estado en la tabla */
    .status-message {
        font-size: 0.9em;
        display: block; /* Para que cada mensaje ocupe su propia línea */
        margin-top: 5px;
        color: #666; /* Color de texto predeterminado para estados */
    }

    .status-message.green {
        color: #28a745; /* Verde para listo */
    }

    .status-message.orange {
        color: #ff8c00; /* Naranja para pendiente */
    }

    .status-message.gray {
        color: #6c757d; /* Gris para otros estados */
    }
</style>

<section class="content-section">
    <h2>Bienvenido, Paciente <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
    <?php echo $mensaje; // Mostrar mensajes de éxito/error ?>

    <p>Este es tu panel de control como paciente. Aquí encontrarás recursos, historial de cuestionarios y enlaces a apoyo.</p>
    <ul>
        <li><a href="historial_resultados.php">Ver tus resultados anteriores del cuestionario</a></li>
        <li><a href="#">Acceder a recursos para pacientes</a></li>
        <li><a href="http://localhost/foro/" target="_blank">Participar en el Foro de la Comunidad</a></li>
        <li><a href="#">Buscar especialistas (funcionalidad futura)</a></li>
        <li><a href="agendar_cita.php">Agendar Nueva Cita</a></li>
        <li><a href="responder_encuesta.php">Responder Encuesta de Seguimiento Emocional</a></li>
    </ul>

    <h3>Tus Próximas Citas</h3>
    <?php if (count($citas_paciente) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Especialista</th>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas_paciente as $cita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['especialista_nombre']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($cita['fecha_cita'])); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($cita['estado'])); ?></td>
                            <td>
                                <?php if ($cita['estado'] == 'confirmada'): ?>
                                    <?php if (!empty($cita['enlace_videollamada'])): ?>
                                        <a href="<?php echo htmlspecialchars($cita['enlace_videollamada']); ?>" target="_blank" class="btn btn-videollamada">Unirme a la Videollamada</a>
                                        <span class="status-message green">Enlace listo.</span>
                                    <?php else: ?>
                                        <span class="status-message orange">Enlace de videollamada aún no disponible</span>
                                    <?php endif; ?>
                                <?php elseif ($cita['estado'] == 'pendiente'): ?>
                                    <span class="status-message gray">Esperando confirmación del especialista</span>
                                <?php else: ?>
                                    <span class="status-message gray">Estado: <?php echo htmlspecialchars(ucfirst($cita['estado'])); ?></span>
                                <?php endif; ?>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No tienes citas confirmadas o pendientes en este momento.</p>
    <?php endif; ?>

    <p><a href="logout.php" class="btn btn-logout">Cerrar Sesión</a></p>
</section>

<?php include 'includes/footer.php'; ?>