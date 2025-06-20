<?php
session_start();
// Usa include_once para header.php y db_config.php para evitar problemas si ya se incluyeron
include_once 'includes/header.php';
include_once 'includes/db_config.php'; // Tu archivo de conexión a la base de datos
include_once 'includes/funciones.php'; // Asegúrate de que funciones.php esté incluido aquí para crearNotificacion

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$especialista_id = $_SESSION['usuario_id'];

// Obtener citas donde este especialista está involucrado
$stmt_citas = $pdo->prepare("
    SELECT
        c.id,
        c.paciente_id,
        c.fecha_cita,
        c.diagnostico_id,
        c.estado,
        c.notas_paciente,
        c.notas_especialista,
        c.enlace_videollamada, -- ¡Asegúrate de tener esta línea!
        u.nombre_usuario AS paciente_nombre,
        d.nombre AS diagnostico_nombre
    FROM citas c
    JOIN usuarios u ON c.paciente_id = u.id
    LEFT JOIN diagnosticos d ON c.diagnostico_id = d.id
    WHERE
        (c.estado = 'pendiente' AND c.especialista_id IS NULL)
        OR (c.especialista_id = ? AND (c.estado = 'pendiente' OR c.estado = 'confirmada' OR c.estado = 'completada' OR c.estado = 'cancelada'))
    ORDER BY c.fecha_cita ASC
");
$stmt_citas->execute([$especialista_id]);
$citas = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);

// Mensajes de éxito o error (si vienen de aceptar_cita.php, cancelar_cita.php, etc.)
$mensaje = '';
if (isset($_GET['mensaje'])) {
    $mensaje = '<p style="color: green; font-weight: bold;">' . htmlspecialchars($_GET['mensaje']) . '</p>';
} elseif (isset($_GET['error'])) {
    $mensaje = '<p style="color: red; font-weight: bold;">' . htmlspecialchars($_GET['error']) . '</p>';
}

?>

<style>
    /* Estilos base para la sección de contenido */
    .content-section {
        background-color: #f9f9f9;
        padding: 30px;
        margin-top: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .content-section h2 {
        color: #333;
        margin-bottom: 25px;
        text-align: center;
    }
    .content-section p {
        color: #555;
        font-size: 1.1em;
        line-height: 1.6;
        text-align: center;
    }

    /* Estilos para la tabla */
    .table-container {
        overflow-x: auto; /* Para tablas que se desbordan en pantallas pequeñas */
        margin-top: 30px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px; /* Asegura que la tabla no se vea demasiado pequeña */
    }
    th, td {
        padding: 12px;
        text-align: left;
        border: 1px solid #e0e0e0;
        vertical-align: top; /* Alinea el contenido de las celdas en la parte superior */
    }
    th {
        background-color: #eaf6ff; /* Un azul muy claro */
        color: #333;
        font-weight: bold;
    }
    tbody tr:nth-child(odd) {
        background-color: #fcfdff; /* Rayado para mejor legibilidad */
    }
    tbody tr:hover {
        background-color: #e9f5fe;
    }

    /* Estilos para los botones */
    .btn {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
        font-weight: bold;
        margin-bottom: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
        text-align: center;
        box-sizing: border-box;
    }
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .btn-green { background-color: #28a745; } /* Aceptar */
    .btn-red { background-color: #dc3545; }   /* Cancelar */
    .btn-blue { background-color: #007bff; }  /* Ver Reporte */
    .btn-purple { background-color: #6f42c1; } /* Completar */
    .btn-teal { background-color: #17a2b8; }   /* Guardar Enlace */
    .btn-meet { background-color: #6610f2; }   /* Iniciar Videollamada */
    .btn-gray { background-color: #6c757d; }   /* Volver */

    /* Estilos para los campos de formulario dentro de la tabla */
    input[type="url"],
    textarea {
        width: 100%; /* Ocupa todo el ancho disponible de la celda */
        padding: 8px;
        margin-bottom: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box; /* Para que el padding no aumente el ancho */
        font-family: inherit; /* Hereda la fuente del cuerpo */
        font-size: 0.95em;
    }
    form {
        margin-bottom: 10px; /* Espacio entre formularios de acciones */
    }
    form:last-child {
        margin-bottom: 0;
    }
</style>

<section class="content-section">
    <h2>Gestionar Citas de Pacientes</h2>

    <?php echo $mensaje; // Mostrar mensajes de éxito/error ?>

    <?php if (count($citas) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Fecha y Hora</th>
                        <th>Diagnóstico</th>
                        <th>Notas Paciente</th>
                        <th>Notas Especialista</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['paciente_nombre']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($cita['fecha_cita'])); ?></td>
                            <td><?php echo htmlspecialchars($cita['diagnostico_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cita['notas_paciente'] ?: 'N/A'); ?></td>
                            <td>
                                <textarea name="notas_especialista_<?php echo $cita['id']; ?>" rows="3" placeholder="Añadir notas de la sesión"><?php echo htmlspecialchars($cita['notas_especialista'] ?: ''); ?></textarea>
                            </td>
                            <td><?php echo htmlspecialchars(ucfirst($cita['estado'])); ?></td>
                            <td>
                                <a href="reporte_caso_paciente.php?paciente_id=<?php echo $cita['paciente_id']; ?>" class="btn btn-blue">Ver Reporte</a>

                                <?php if ($cita['estado'] == 'pendiente'): ?>
                                    <a href="aceptar_cita.php?id=<?php echo $cita['id']; ?>&especialista_id=<?php echo $especialista_id; ?>" class="btn btn-green">Aceptar</a>
                                    <a href="cancelar_cita.php?id=<?php echo $cita['id']; ?>" class="btn btn-red">Cancelar</a>
                                <?php elseif ($cita['estado'] == 'confirmada'): ?>
                                    <form action="guardar_cita_data.php" method="POST">
                                        <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                                        <input type="hidden" name="paciente_id" value="<?php echo $cita['paciente_id']; ?>">
                                        <input type="hidden" name="notas_especialista_hidden" class="notas-especialista-hidden-<?php echo $cita['id']; ?>">
                                        
                                        <input type="url" name="enlace_videollamada" placeholder="Enlace Google Meet/Zoom" value="<?php echo htmlspecialchars($cita['enlace_videollamada'] ?? ''); ?>">
                                        
                                        <button type="submit" name="accion" value="guardar_enlace_y_notas" class="btn btn-teal">Guardar Enlace y Notas</button>
                                    </form>

                                    <?php if (!empty($cita['enlace_videollamada'])): ?>
                                        <a href="<?php echo htmlspecialchars($cita['enlace_videollamada']); ?>" target="_blank" class="btn btn-meet">Iniciar Videollamada</a>
                                        <span style="color: green; font-size: 0.9em; display: block; text-align: center; margin-top: 5px;">Enlace listo.</span>
                                    <?php else: ?>
                                        <span style="color: gray; font-size: 0.9em; display: block; text-align: center; margin-top: 5px;">Enlace pendiente.</span>
                                    <?php endif; ?>

                                    <a href="completar_cita_simple.php?id=<?php echo $cita['id']; ?>&paciente_id=<?php echo $cita['paciente_id']; ?>" class="btn btn-purple">Marcar como Completada</a>
                                    <a href="cancelar_cita.php?id=<?php echo $cita['id']; ?>" class="btn btn-red">Cancelar Cita</a>
                                <?php else: // Citas completadas o canceladas ?>
                                    <span style="color: gray;">Acciones no disponibles</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No hay citas pendientes o asignadas en este momento.</p>
    <?php endif; ?>
    <p style="margin-top: 20px; text-align: center;"><a href="dashboard_especialista.php" class="btn btn-gray">Volver al Dashboard</a></p>
</section>

<script>
// Script para copiar el valor del textarea de notas al input hidden antes de enviar el formulario
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        // Solo aplica a los formularios que guardan datos (en este caso, 'guardar_cita_data.php')
        if (this.action.includes('guardar_cita_data.php')) {
            const citaId = this.querySelector('input[name="cita_id"]').value;
            const textarea = document.querySelector(`textarea[name="notas_especialista_${citaId}"]`);
            const hiddenNotesInput = this.querySelector(`.notas-especialista-hidden-${citaId}`); // Usar la clase específica
            
            if (textarea && hiddenNotesInput) {
                hiddenNotesInput.value = textarea.value; // Copia el contenido del textarea al campo oculto
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>