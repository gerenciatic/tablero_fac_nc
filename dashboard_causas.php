<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Notas - Conexión Real</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="text-center"><i class="fas fa-chart-line me-2"></i>Dashboard de Notas Devoluciones por Causa</h1>
            <p class="text-center">Análisis detallado de notas de crédito y débito</p>
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
                <label for="tipoNota" class="form-label">Tipo de Nota</label>
                <select class="form-select" id="tipoNota">
                    <option value="NC" selected>Notas Crédito</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="agrupacion" class="form-label">Agrupación</label>
                <select class="form-select" id="agrupacion">
                    <option value="diaria" selected>Diaria</option>
                    <option value="mensual">Mensual</option>
                    <option value="anual">Anual</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="cargarDatos()">
                    <i class="fas fa-sync-alt me-2"></i>Cargar Datos
                </button>
            </div>

            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Volver al Dashboard</a>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab">
                    <i class="fas fa-chart-pie me-2"></i>Resumen
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="causas-tab" data-bs-toggle="tab" data-bs-target="#causas" type="button" role="tab">
                    <i class="fas fa-exclamation-circle me-2"></i>Notas por Causa
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="vendedores-tab" data-bs-toggle="tab" data-bs-target="#vendedores" type="button" role="tab">
                    <i class="fas fa-user-tie me-2"></i>Notas por Vendedor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="departamentos-tab" data-bs-toggle="tab" data-bs-target="#departamentos" type="button" role="tab">
                    <i class="fas fa-building me-2"></i>Notas por Departamento
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="causas-vendedor-tab" data-bs-toggle="tab" data-bs-target="#causas-vendedor" type="button" role="tab">
                    <i class="fas fa-search me-2"></i>Causas por Vendedor
                </button>
            </li>


                        <!-- En el ul de nav-tabs, después de la pestaña de Causas por Vendedor -->
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos" type="button" role="tab">
                    <i class="fas fa-boxes me-2"></i>Análisis de Productos
                </button>
            </li>


        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content" id="dashboardTabContent">
            <!-- Pestaña de Resumen -->
            <div class="tab-pane fade show active" id="resumen" role="tabpanel">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-file-invoice me-2"></i>Total Notas</h5>
                                <div class="metric-value" id="totalNotas">0</div>
                                <div class="text-muted">Documentos únicos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-list-alt me-2"></i>Líneas Detalle</h5>
                                <div class="metric-value" id="totalLineasDetalle">0</div>
                                <div class="text-muted">Total de líneas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-balance-scale me-2"></i>Notas por Día</h5>
                                <div class="metric-value" id="notasPorDia">0</div>
                                <div class="text-muted">Promedio diario</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-star me-2"></i>Causa Principal</h5>
                                <div class="metric-value" id="causaPrincipal">-</div>
                                <div class="text-muted">Más frecuente</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-layer-group me-2"></i>Líneas por Nota</h5>
                                <div class="metric-value" id="lineasPorNota">0.0</div>
                                <div class="text-muted">Promedio por documento</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-calendar me-2"></i>Días Hábiles</h5>
                                <div class="metric-value" id="diasHabiles">0</div>
                                <div class="text-muted">Días laborables</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-chart-line me-2"></i>Eficiencia NC/NCDT</h5>
                                <div class="metric-value" id="eficiencia">0%</div>
                                <div class="text-muted">Rendimiento general</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <h5 class="metric-title"><i class="fas fa-building me-2"></i>Departamentos</h5>
                                <div class="metric-value" id="totalDepartamentos">0</div>
                                <div class="text-muted">Total departamentos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-2"></i>Evolución de Notas por Mes
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="evolucionMensualChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-2"></i>Distribución por Tipo
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="distribucionTipoChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Notas por Causa -->
            <div class="tab-pane fade" id="causas" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-2"></i>Notas por Causa
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="causasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-2"></i>Top 5 Causas
                            </div>
                            <div class="card-body">
                                <div class="chart-container small-chart">
                                    <canvas id="topCausasChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-2"></i>Resumen por Causa
                            </div>
                            <div class="card-body">
                                <div id="resumenCausas">
                                    <p class="text-center">Cargando datos...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-table me-2"></i>Detalle de Notas por Causa</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Causa</th>
                                                <th>Descripcion</th>
                                                <th>Cantidad</th>
                                                <th>% del Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaCausas">
                                            <tr>
                                                <td colspan="5" class="text-center">Cargando datos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Notas por Vendedor -->
            <div class="tab-pane fade" id="vendedores" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-2"></i>Notas por Vendedor
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="vendedoresChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-2"></i>Top 5 Vendedores
                            </div>
                            <div class="card-body">
                                <div class="chart-container small-chart">
                                    <canvas id="topVendedoresChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-trophy me-2"></i>Top Vendedores
                            </div>
                            <div class="card-body">
                                <div id="rankingVendedores">
                                    <p class="text-center">Cargando datos...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-table me-2"></i>Detalle de Notas por Vendedor la primera causa</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Vendedor</th>
                                                <th>Cantidad</th>
                                                <th>% del Total</th>
                                                <th>Causa Principal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaVendedores">
                                            <tr>
                                                <td colspan="4" class="text-center">Cargando datos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NUEVA PESTAÑA: Notas por Departamento -->
            <div class="tab-pane fade" id="departamentos" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-2"></i>Notas por Departamento
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="departamentosChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-2"></i>Top 5 Departamentos
                            </div>
                            <div class="card-body">
                                <div class="chart-container small-chart">
                                    <canvas id="topDepartamentosChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-2"></i>Resumen por Departamento
                            </div>
                            <div class="card-body">
                                <div id="resumenDepartamentos">
                                    <p class="text-center">Cargando datos...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-table me-2"></i>Detalle de Notas por Departamento</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Departamento</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>% del Total</th>
                                                <th>Causa Principal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaDepartamentos">
                                            <tr>
                                                <td colspan="6" class="text-center">Cargando datos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Causas por Vendedor -->
            <div class="tab-pane fade" id="causas-vendedor" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-search me-2"></i>Análisis de Causas por Vendedor
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="vendedor-selector">
                                            <label for="selectVendedor" class="form-label">Seleccionar Vendedor:</label>
                                            <select class="form-select" id="selectVendedor">
                                                <option value="">Cargando vendedores...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div id="infoVendedor" class="alert alert-info">
                                            <strong>Seleccione un vendedor</strong> para ver el análisis detallado de causas.
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4" id="contenidoVendedor" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <i class="fas fa-chart-pie me-2"></i>Distribución de Causas
                                            </div>
                                            <div class="card-body">
                                                <div class="chart-container">
                                                    <canvas id="causasVendedorChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <i class="fas fa-info-circle me-2"></i>Resumen del Vendedor
                                            </div>
                                            <div class="card-body">
                                                <div id="resumenVendedor">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <strong>Total Notas:</strong>
                                                            <span id="vendedorTotalNotas">0</span>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Eficiencia Item/NC:</strong>
                                                            <span id="vendedorEficiencia">0%</span>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <strong>Causa Principal:</strong>
                                                            <span id="vendedorCausaPrincipal">-</span>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Item/Nota:</strong>
                                                            <span id="vendedorLineasNota">0.0</span>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h6>Top 5 Causas:</h6>
                                                    <div id="topCausasVendedor"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4" id="tablaCausasVendedor" style="display: none;">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <i class="fas fa-table me-2"></i>Detalle de Causas por Vendedor
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive causas-vendedor-container">
                                                    <table class="table table-striped table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Causa</th>
                                                                <th>Cantidad</th>
                                                                <th>% del Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tablaDetalleCausasVendedor">
                                                            <tr>
                                                                <td colspan="3" class="text-center">Seleccione un vendedor para ver el detalle</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



<!-- Pestaña de Análisis de Productos -->
<div class="tab-pane fade" id="productos" role="tabpanel">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-boxes me-2"></i>Análisis de Productos por Causas
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="selectProducto" class="form-label">Seleccionar Producto:</label>
                            <select id="selectProducto" class="form-select">
                                <option value="">Todos los productos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="selectCausaProducto" class="form-label">Filtrar por Causa:</label>
                            <select id="selectCausaProducto" class="form-select">
                                <option value="">Todas las causas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="selectVendedorProducto" class="form-label">Filtrar por Vendedor:</label>
                            <select id="selectVendedorProducto" class="form-select">
                                <option value="">Todos los vendedores</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="selectDepartamentoProducto" class="form-label">Filtrar por Departamento:</label>
                            <select id="selectDepartamentoProducto" class="form-select">
                                <option value="">Todos los departamentos</option>
                            </select>
                        </div>
                    </div>

                     <!-- BOTÓN PARA LIMPIAR FILTROS - NUEVO -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-end">
                            <button class="btn btn-warning btn-sm" onclick="limpiarFiltrosProductos()">
                                <i class="fas fa-eraser me-1"></i>Limpiar Filtros
                            </button>
                        </div>
                    </div>



                    <!-- Métricas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card metric-card">
                                <div class="card-body">
                                    <h5 class="metric-title"><i class="fas fa-box me-2"></i>Total Productos</h5>
                                    <div class="metric-value" id="totalProductos">0</div>
                                    <div class="text-muted">Productos únicos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card metric-card">
                                <div class="card-body">
                                    <h5 class="metric-title"><i class="fas fa-exchange-alt me-2"></i>Notas con Productos</h5>
                                    <div class="metric-value" id="notasConProductos">0</div>
                                    <div class="text-muted">Documentos afectados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card metric-card">
                                <div class="card-body">
                                    <h5 class="metric-title"><i class="fas fa-exclamation-triangle me-2"></i>Producto Más Problemático</h5>
                                    <div class="metric-value" id="productoProblematico">-</div>
                                    <div class="text-muted">Más notas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card metric-card">
                                <div class="card-body">
                                    <h5 class="metric-title"><i class="fas fa-chart-pie me-2"></i>Causa Principal</h5>
                                    <div class="metric-value" id="causaPrincipalProductos">-</div>
                                    <div class="text-muted">En productos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-2"></i>Top 10 Productos con Más Notas
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="topProductosChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-2"></i>Distribución por Departamento
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="departamentoProductosChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="card-title mb-0">Detalle de Productos por Causas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Código Producto</th>
                                                    <th>Descripción</th>
                                                    <th>Causa</th>
                                                    <th>Vendedor</th>
                                                    <th>Departamento</th>
                                                    <th>Cantidad</th>
                                                 
                                                </tr>
                                            </thead>
                                            <tbody id="tablaProductos">
                                                <tr>
                                                    <td colspan="8" class="text-center">Cargando datos de productos...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




        <!-- Estado de carga -->
        <div class="row mt-4" id="estadoCarga">
            <div class="col-12">
                <div class="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <span class="ms-2">Cargando datos...</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/tablero_detallado.js"></script>

 


</body>
</html>