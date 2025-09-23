<?php
include_once 'includes/conexsql.php';

// Obtener el año y mes actual
$anoActual = date('Y');
$mesActual = date('n');

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['meta_mes']) && isset($_POST['meta_ano'])) {
    $metaMes = floatval($_POST['meta_mes']);
    $metaAno = floatval($_POST['meta_ano']);
    $mesSeleccionado = intval($_POST['mes']);
    $anoSeleccionado = intval($_POST['ano']);
    
    // Verificar si ya existe una meta para este mes y año
    $sqlCheck = "SELECT Id FROM dbo.Metas_Notas WHERE Ano = ? AND Mes = ?";
    $paramsCheck = array($anoSeleccionado, $mesSeleccionado);
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, $paramsCheck);
    
    if ($stmtCheck !== false && sqlsrv_has_rows($stmtCheck)) {
        // Actualizar meta existente
        $sqlUpdate = "UPDATE dbo.Metas_Notas SET Meta_Mes_Notas = ?, Meta_Ano_Notas = ?, Fecha_Actualizacion = GETDATE(), Usuario_Actualizacion = ? WHERE Ano = ? AND Mes = ?";
        $paramsUpdate = array($metaMes, $metaAno, $_SESSION['usuario'] ?? 'Sistema', $anoSeleccionado, $mesSeleccionado);
        $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);
        
        if ($stmtUpdate) {
            $mensaje = "Meta actualizada correctamente";
        } else {
            $error = "Error actualizando meta: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        // Insertar nueva meta
        $sqlInsert = "INSERT INTO dbo.Metas_Notas (Ano, Mes, Meta_Mes_Notas, Meta_Ano_Notas, Usuario_Registro) VALUES (?, ?, ?, ?, ?)";
        $paramsInsert = array($anoSeleccionado, $mesSeleccionado, $metaMes, $metaAno, $_SESSION['usuario'] ?? 'Sistema');
        $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);
        
        if ($stmtInsert) {
            $mensaje = "Meta guardada correctamente";
        } else {
            $error = "Error guardando meta: " . print_r(sqlsrv_errors(), true);
        }
    }
}

// Obtener meta actual para el mes y año seleccionados (o el actual por defecto)
$mesFiltro = isset($_GET['mes']) ? intval($_GET['mes']) : $mesActual;
$anoFiltro = isset($_GET['ano']) ? intval($_GET['ano']) : $anoActual;

$sqlMeta = "SELECT Meta_Mes_Notas, Meta_Ano_Notas FROM dbo.Metas_Notas WHERE Ano = ? AND Mes = ?";
$paramsMeta = array($anoFiltro, $mesFiltro);
$stmtMeta = sqlsrv_query($conn, $sqlMeta, $paramsMeta);

$metaMesActual = 0;
$metaAnoActual = 0;
if ($stmtMeta !== false && sqlsrv_has_rows($stmtMeta)) {
    $row = sqlsrv_fetch_array($stmtMeta, SQLSRV_FETCH_ASSOC);
    $metaMesActual = $row['Meta_Mes_Notas'];
    $metaAnoActual = $row['Meta_Ano_Notas'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Metas de Notas de Crédito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background: linear-gradient(135deg, #2c3e50, #3a506b);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: #3498db;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .current-meta-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-left: 8px;
        }
        
        .meta-inputs {
            display: flex;
            gap: 15px;
        }
        
        .meta-input {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .meta-inputs {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Administrar Metas de Notas de Crédito</h1>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Agregar/Editar Meta</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ano" class="form-label">Año</label>
                                <select class="form-select" id="ano" name="ano" required>
                                    <?php
                                    for ($i = 2023; $i <= 2025; $i++) {
                                        $selected = $i == $anoFiltro ? 'selected' : '';
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="mes" class="form-label">Mes</label>
                                <select class="form-select" id="mes" name="mes" required>
                                    <?php
                                    $meses = [
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                    
                                    foreach ($meses as $num => $nombre) {
                                        $selected = $num == $mesFiltro ? 'selected' : '';
                                        echo "<option value='$num' $selected>$nombre</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Metas de Notas (%)</label>
                                <div class="meta-inputs">
                                    <div class="meta-input">
                                        <input type="number" class="form-control" id="meta_mes" name="meta_mes" 
                                               value="<?php echo $metaMesActual; ?>" min="0" step="0.01" 
                                               placeholder="Meta Mensual" required>
                                        <small class="form-text text-muted">Meta Mensual</small>
                                    </div>
                                    <div class="meta-input">
                                        <input type="number" class="form-control" id="meta_ano" name="meta_ano" 
                                               value="<?php echo $metaAnoActual; ?>" min="0" step="0.01" 
                                               placeholder="Meta Anual" required>
                                        <small class="form-text text-muted">Meta Anual</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Guardar Meta</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Metas Existentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Mes</th>
                                <th>Meta Mensual</th>
                                <th>Meta Anual</th>
                                <th>Fecha de Registro</th>
                                <th>Última Actualización</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sqlHistorial = "SELECT Id, Ano, Mes, Meta_Mes_Notas, Meta_Ano_Notas, Fecha_Registro, Fecha_Actualizacion, Usuario_Registro 
                                            FROM dbo.Metas_Notas ORDER BY Ano DESC, Mes DESC";
                            $stmtHistorial = sqlsrv_query($conn, $sqlHistorial);
                            
                            if ($stmtHistorial !== false) {
                                while ($row = sqlsrv_fetch_array($stmtHistorial, SQLSRV_FETCH_ASSOC)) {
                                    $esActual = ($row['Ano'] == $anoFiltro && $row['Mes'] == $mesFiltro);
                                    echo "<tr" . ($esActual ? " class='table-info'" : "") . ">";
                                    echo "<td>" . $row['Ano'] . "</td>";
                                    echo "<td>" . $meses[$row['Mes']] . "</td>";
                                    echo "<td>" . $row['Meta_Mes_Notas'] . " %" . ($esActual ? " <span class='current-meta-badge'>Actual</span>" : "") . "</td>";
                                    echo "<td>" . $row['Meta_Ano_Notas'] . " %" . ($esActual ? " <span class='current-meta-badge'>Actual</span>" : "") . "</td>";
                                    echo "<td>" . $row['Fecha_Registro']->format('Y-m-d H:i') . "</td>";
                                    echo "<td>" . ($row['Fecha_Actualizacion'] ? $row['Fecha_Actualizacion']->format('Y-m-d H:i') : 'N/A') . "</td>";
                                    echo "<td>" . $row['Usuario_Registro'] . "</td>";
                                    echo "<td>";
                                    echo "<a href='?ano=" . $row['Ano'] . "&mes=" . $row['Mes'] . "' class='btn btn-sm btn-warning'>Editar</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No hay metas configuradas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Volver al Dashboard</a>
        </div>
    </div>

    <script>
        // Cuando cambia el año o mes, actualizar el formulario
        document.getElementById('ano').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('mes').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>