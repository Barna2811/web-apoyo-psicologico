<?php
// ver_alertas_especialista.php

session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado y es especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$especialista_id = $_SESSION['usuario_id']; // ID del especialista logueado
$mensaje = '';
$alertas = [];

try {
    // Obtener alertas pendientes para este especialista (o todas si no se asignan)
    // Se unen con la tabla usuarios para mostrar el nombre del paciente
    $stmt_alertas = $pdo->prepare("
        SELECT
            a.id,
            u.nombre_usuario AS paciente_nombre,
            a.fecha_alerta,
            a.nivel_urgencia,
            a.descripcion,
            a.atendida
        FROM
            alertas a
        JOIN
            usuarios u ON a.paciente_id = u.id
        WHERE
            a.atendida = FALSE -- Solo mostrar alertas no atendidas
            -- AND (a.especialista_id IS NULL OR a.especialista_id = ?) -- Opcional: filtrar por especialista asignado
        ORDER BY
            CASE a.nivel_urgencia
                WHEN 'critica' THEN 1
                WHEN 'alta' THEN 2
                WHEN 'media' THEN 3
                WHEN 'baja' THEN 4
                ELSE 5
            END,
            a.fecha_alerta DESC
    ");
    // Si usas el filtro por especialista asignado, descomenta la línea de arriba y la de abajo
    // $stmt_alertas->execute([$especialista_id]);
    $stmt_alertas->execute(); // Ejecutar sin filtro de especialista por ahora
    $alertas = $stmt_alertas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensaje = "<p style='color: red;'>Error al cargar las alertas: " . $e->getMessage() . "</p>";
}
?>

<style>
    /* Estilos adicionales para la tabla de alertas */
    .alert-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border-radius: 8px;
        overflow: hidden; /* Para que los bordes redondeados se apliquen a la tabla */
    }
    .alert-table th, .alert-table td {
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
        text-align: left;
    }
    .alert-table th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }
    .alert-table tbody tr:nth-child(even) {
        background-color: #f8f8f8;
    }
    .alert-table tbody tr:hover {
        background-color: #eef;
    }
    .nivel-critica { background-color: #ffebee; color: #c62828; font-weight: bold; } /* Rojo claro */
    .nivel-alta { background-color: #fff3e0; color: #ef6c00; font-weight: bold; } /* Naranja claro */
    .nivel-media { background-color: #fffde7; color: #f9a825; } /* Amarillo claro */
    .nivel-baja { background-color: #e8f5e9; color: #388e3c; } /* Verde claro */

    .btn-atender {
        background-color: #28a745; /* Verde para atender */
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    .btn-atender:hover {
        background-color: #218838;
    }
</style>

<section class="content-section">
    <h2>Alertas de Deterioro Emocional</h2>
    <?php echo $mensaje; ?>

    <?php if (empty($alertas)): ?>
        <p>No hay alertas pendientes en este momento. ¡Todo parece estar bien!</p>
    <?php else: ?>
        <table class="alert-table">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Fecha Alerta</th>
                    <th>Nivel Urgencia</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alertas as $alerta): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($alerta['paciente_nombre']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($alerta['fecha_alerta'])); ?></td>
                        <td class="nivel-<?php echo htmlspecialchars($alerta['nivel_urgencia']); ?>">
                            <?php echo htmlspecialchars(ucfirst($alerta['nivel_urgencia'])); ?>
                        </td>
                        <td><?php echo htmlspecialchars($alerta['descripcion']); ?></td>
                        <td>
                            <a href="marcar_alerta_atendida.php?id=<?php echo $alerta['id']; ?>" class="btn-atender">Marcar como Atendida</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top: 30px;"><a href="dashboard_especialista.php" class="btn" style="background-color: #6c757d;">Volver al Dashboard</a></p>
</section>

<?php include 'includes/footer.php'; ?>