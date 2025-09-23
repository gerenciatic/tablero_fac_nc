<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Facturación - Conexión Real</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="text-center">Dashboard de Facturación</h1>
            <p class="text-center">Monitoreo en tiempo real de facturas y notas de crédito</p>
        </div>
    </div>

    <div class="container">
        <!-- Filtros -->
        <div class="row filter-section">
            <div class="col-md-3">
                <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" id="fechaInicio" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-md-3">
                <label for="fechaFin" class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" id="fechaFin" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label for="agrupacion" class="form-label">Agrupación</label>
                <select class="form-select" id="agrupacion">
                    <option value="diaria" selected>Diaria</option>
                    <option value="semanal">Semanal</option>
                    <option value="mensual">Mensual</option>
                </select>
            </div>

            <div class="col-md-12 mt-3">
                <button class="btn btn-primary" onclick="cargarDatos()">Cargar Datos</button>
                <button class="btn btn-secondary" onclick="exportarDatos()">Exportar</button>
                 <a href="admin_metas.php" class="btn btn-info">Administrar Meta</a>
                 <a href="dashboard_causas.php" class="btn btn-info">Notas Por Causas</a>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="autoActualizar">
                    <label class="form-check-label" for="autoActualizar">Auto-actualizar cada 5 minutos</label>
                </div>
            </div>



        </div>

        <!-- Estado de carga -->
        <div class="row" id="estadoCarga">
            <div class="col-12">
                <div class="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <span class="ms-2">Cargando datos...</span>
                </div>
            </div>
        </div>

        <!-- Métricas principales -->
        <div class="row" id="metricasPrincipales" style="display: none;">
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">Total Facturas</h5>
                        <div class="metric-value" id="totalFacturas">0</div>
                        <div id="variacionFacturas" class="positive-change">Cargando...</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">Total Notas</h5>
                        <div class="metric-value" id="totalNotas">0</div>
                        <div id="variacionNotas" class="negative-change">Cargando...</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">% Notas/Facturas</h5>
                        <div class="metric-value" id="porcentajeNotas">0%</div>
                        <div id="tendenciaPorcentaje" class="negative-change">Cargando...</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">Promedio Histórico</h5>
                        <div class="metric-value" id="promedioHistorico">0%</div>
                        <div class="metric-desc">Promedio anual</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">Meta Automática</h5>
                        <div class="metric-value" id="metaAutomatica">0%</div>
                        <div class="metric-desc">Base histórica</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">Progreso a Meta</h5>
                        <div class="metric-value" id="progresoMeta">0%</div>
                        <div id="textoProgreso" class="positive-change">Cargando...</div>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-body">
                        <h5 class="metric-title">Días Hábiles</h5>
                        <div class="metric-value" id="diasHabiles">0/0</div>
                        <div id="porcentajeDias" class="positive-change">Cargando...</div>
                    </div>
                </div>
            </div>
            
        </div>

       <!-- Barra de progreso -->
       <!-- Barra de progreso -->
<div class="row mt-3" id="barraProgresoContainer" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                Progreso hacia la Meta
                <span class="float-end" id="textoMeta">Meta: 0%</span>
            </div>
            <div class="card-body">
                <!-- Barra de progreso dividida -->
                <div class="progress-split-container">
                    <div class="progress-split-base"></div>
                    <div class="progress-split-within" id="barraDentroMeta"></div>
                    <div class="progress-split-exceeded" id="barraExcedente"></div>
                    <div class="meta-indicator-line" id="indicadorMeta"></div>
                    <div class="meta-tooltip" id="tooltipMeta">Meta: 0%</div>
                </div>
                
                <div class="progress-labels">
                    <span>0%</span>
                    <span id="valorActualProgreso">0%</span>
                    <span>100%</span>
                </div>
                
                <div class="progress-current-value" id="textoValorActual">
                    Cargando...
                </div>
                
                <div class="meta-status" id="estadoMeta">
                    Estado de la meta
                </div>
                
                <div id="textoExcedente" class="progress-info"></div>
            </div>
        </div>
    </div>
</div>

        <!-- Gráficos -->
        <div class="row" id="seccionGraficos" style="display: none;">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Evolución - Facturas vs Notas
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="evolucionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Distribución por Tipo
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="distribucionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de vista para tablas -->
        <div class="row mt-4" id="selectorTabla" style="display: none;">
            <div class="col-12">
                <div class="view-selector">
                    <div class="view-btn active" onclick="cambiarVista('diaria')">Vista Diaria</div>
                    <div class="view-btn" onclick="cambiarVista('mensual')">Vista Mensual</div>
                </div>
            </div>
        </div>

        <!-- Tabla de datos diaria -->
        <div class="row vista-tabla" id="seccionTablaDiaria" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Detalle por Fecha (Vista Diaria)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Año</th>
                                        <th>Mes</th>
                                        <th>Día</th>
                                        <th>Facturas</th>
                                        <th>Notas Crédito</th>
                                        <th>% Notas/Facturas</th>
                                        <th>Total Documentos</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDatosDiaria">
                                    <!-- Los datos se llenarán con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de datos mensual -->
        <div class="row vista-tabla" id="seccionTablaMensual" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Detalle por Mes (Vista Mensual)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Año</th>
                                        <th>Mes</th>
                                        <th>Facturas</th>
                                        <th>Notas Crédito</th>
                                        <th>% Notas/Facturas</th>
                                        <th>Total Documentos</th>
                                        <th>Promedio Diario Facturas</th>
                                        <th>Promedio Diario Notas</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDatosMensual">
                                    <!-- Los datos se llenarán con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje de error -->
        <div class="row" id="mensajeError" style="display: none;">
            <div class="col-12">
                <div class="error-message">
                    <h4>Error al cargar los datos</h4>
                    <p id="textoError">Por favor, verifique la conexión e intente nuevamente.</p>
                    <button class="btn btn-primary" onclick="cargarDatos()">Reintentar</button>
                </div>
            </div>
        </div>
    </div>

  <script src="js/script.js"></script>
</body>
</html>