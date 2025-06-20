<?php
// calendario_especialista.php

session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado y es especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$especialista_id = $_SESSION['usuario_id'];
?>

<section class="content-section">
    <h2>Mi Calendario de Citas</h2>
    <p>Aquí puedes ver tus citas agendadas y organizar tu tiempo.</p>

    <div id='calendar' style="max-width: 1000px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"></div>

    <hr style="margin-top: 40px; margin-bottom: 40px;">

    <h3>Estadísticas de Citas</h3>
    <div class="stats-container" style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">
        <div class="stat-box" style="background-color: #e0f2f7; padding: 20px; border-radius: 8px; text-align: center; flex: 1; min-width: 250px;">
            <h4>Citas Confirmadas (Últimos 30 días)</h4>
            <p style="font-size: 2em; font-weight: bold; color: #0288d1;" id="numCitasConfirmadas">Cargando...</p>
        </div>
        <div class="stat-box" style="background-color: #e8f5e9; padding: 20px; border-radius: 8px; text-align: center; flex: 1; min-width: 250px;">
            <h4>Tiempo Promedio por Sesión</h4>
            <p style="font-size: 2em; font-weight: bold; color: #388e3c;" id="tiempoPromedioSesion">Cargando...</p>
        </div>
        </div>


    <p style="margin-top: 30px;"><a href="dashboard_especialista.php" class="btn" style="background-color: #6c757d;">Volver al Dashboard</a></p>
</section>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var especialistaId = <?php echo json_encode($especialista_id); ?>;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Vista inicial: mes
        locale: 'es', // Idioma español
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Vistas: mes, semana, día
        },
        editable: false, // El especialista no edita arrastrando aquí, sino gestionando citas
        selectable: true, // Permite seleccionar rangos de fechas (si quieres agendar)
        events: {
            url: 'get_citas_especialista.php', // Endpoint que PHP usará para obtener las citas
            method: 'GET',
            extraParams: {
                especialista_id: especialistaId // Pasar el ID del especialista
            },
            failure: function() {
                alert('Hubo un error al cargar las citas.');
            }
        },
        eventClick: function(info) {
            // Cuando se hace clic en un evento (cita)
            alert('Cita: ' + info.event.title + '\n' +
                  'Fecha: ' + info.event.start.toLocaleString() + '\n' +
                  'Estado: ' + info.event.extendedProps.estado + '\n' +
                  'Paciente: ' + info.event.extendedProps.paciente_nombre + '\n' +
                  'Notas: ' + (info.event.extendedProps.notas_paciente || 'N/A'));
            // Podrías redirigir a una página de detalle de la cita
            // window.location.href = 'detalle_cita.php?id=' + info.event.id;
        },
        loading: function(bool) {
            // Muestra/oculta un indicador de carga
            if (bool) {
                console.log('Cargando eventos...');
            } else {
                console.log('Eventos cargados.');
            }
        }
    });

    calendar.render();

    // --- Cargar las estadísticas por AJAX ---
    function cargarEstadisticas() {
        fetch('get_estadisticas_especialista.php?especialista_id=' + especialistaId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('numCitasConfirmadas').innerText = data.num_citas_confirmadas;
                    document.getElementById('tiempoPromedioSesion').innerText = data.tiempo_promedio_sesion + ' min';
                    // Aquí podrías añadir la tasa de disponibilidad vs demanda
                    // document.getElementById('tasaDisponibilidad').innerText = data.tasa_disponibilidad + '%';
                } else {
                    console.error('Error al cargar estadísticas:', data.message);
                    document.getElementById('numCitasConfirmadas').innerText = 'Error';
                    document.getElementById('tiempoPromedioSesion').innerText = 'Error';
                }
            })
            .catch(error => {
                console.error('Error de red al cargar estadísticas:', error);
                document.getElementById('numCitasConfirmadas').innerText = 'Error';
                document.getElementById('tiempoPromedioSesion').innerText = 'Error';
            });
    }

    cargarEstadisticas(); // Cargar estadísticas al iniciar la página
});
</script>

<?php include 'includes/footer.php'; ?>