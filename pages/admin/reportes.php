<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: /qrcheckin/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Asistencia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- DateRangePicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Panel de Administración</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Inicio</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/qrcheckin/includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Reportes de Asistencia</h2>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filtrosForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="grupo" class="form-label">Grupo</label>
                                <select class="form-select" id="grupo" name="grupo">
                                    <option value="">Todos los grupos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fechas" class="form-label">Rango de Fechas</label>
                                <input type="text" class="form-control" id="fechas" name="fechas">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="profesor" class="form-label">Profesor</label>
                                <select class="form-select" id="profesor" name="profesor">
                                    <option value="">Todos los profesores</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Generar Reporte</button>
                </form>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reporteTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Documento</th>
                                <th>Grupo</th>
                                <th>Profesor</th>
                                <th>Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <!-- Moment.js y DateRangePicker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Inicializar DateRangePicker
        $('#fechas').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        });

        // Cargar grupos
        $.get('/qrcheckin/includes/reportes/get_grupos.php', function(data) {
            data.forEach(function(grupo) {
                $('#grupo').append(`<option value="${grupo.id}">${grupo.nombre}</option>`);
            });
        });

        // Cargar profesores
        $.get('/qrcheckin/includes/reportes/get_profesores.php', function(data) {
            data.forEach(function(profesor) {
                $('#profesor').append(`<option value="${profesor.id}">${profesor.nombre} ${profesor.apellidos}</option>`);
            });
        });

        // Inicializar DataTable
        var table = $('#reporteTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'print'
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            processing: true,
            serverSide: false,
            ajax: {
                url: '/qrcheckin/includes/reportes/get_asistencias.php',
                type: 'POST',
                data: function(d) {
                    d.grupo = $('#grupo').val();
                    d.profesor = $('#profesor').val();
                    d.fechas = $('#fechas').val();
                }
            },
            columns: [
                { data: 'estudiante' },
                { data: 'documento' },
                { data: 'grupo' },
                { data: 'profesor' },
                { data: 'fecha_hora' }
            ]
        });

        // Manejar envío del formulario
        $('#filtrosForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });
    </script>
</body>
</html> 