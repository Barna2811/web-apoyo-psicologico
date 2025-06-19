<?php
session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'paciente') {
    header("Location: ingresar.php");
    exit();
}

$paciente_id = $_SESSION['usuario_id'];
$mensaje = '';

// Obtener diagnósticos para el selector
$stmt_diag = $pdo->query("SELECT id, nombre FROM diagnosticos");
$diagnosticos = $stmt_diag->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_cita = $_POST['fecha_cita'];
    $diagnostico_id = $_POST['diagnostico_id'];
    $notas_paciente = $_POST['notas_paciente'];

    if (empty($fecha_cita) || empty($diagnostico_id)) {
        $mensaje = "<p style='color: red;'>Por favor, complete todos los campos obligatorios.</p>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO citas (paciente_id, fecha_cita, diagnostico_id, notas_paciente) VALUES (?, ?, ?, ?)");
            $stmt->execute([$paciente_id, $fecha_cita, $diagnostico_id, $notas_paciente]);
            $mensaje = "<p style='color: green;'>¡Cita solicitada con éxito! Un especialista la revisará pronto.</p>";
        } catch (PDOException $e) {
            $mensaje = "<p style='color: red;'>Error al agendar la cita: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<section class="content-section">
    <h2>Agendar Nueva Cita</h2>
    <?php echo $mensaje; ?>
    <form action="agendar_cita.php" method="POST" style="max-width: 500px; margin: 0 auto; text-align: left;">
        <label for="fecha_cita">Fecha y Hora Preferida:</label>
        <input type="datetime-local" id="fecha_cita" name="fecha_cita" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">

        <label for="diagnostico_id">Tipo de Diagnóstico (Si aplica):</label>
        <select id="diagnostico_id" name="diagnostico_id" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">
            <?php foreach ($diagnosticos as $diag): ?>
                <option value="<?php echo htmlspecialchars($diag['id']); ?>"><?php echo htmlspecialchars($diag['nombre']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="notas_paciente">Notas adicionales (opcional):</label>
        <textarea id="notas_paciente" name="notas_paciente" rows="4" style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;"></textarea>

        <button type="submit" class="btn" style="background-color: #6495ed; color: white; border: none; cursor: pointer;">Solicitar Cita</button>
    </form>
    <p style="margin-top: 20px;"><a href="dashboard_paciente.php" class="btn" style="background-color: #ccc;">Volver al Dashboard</a></p>
</section>

<?php include 'includes/footer.php'; ?>