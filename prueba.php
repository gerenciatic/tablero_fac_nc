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
                    <option value="TODAS" selected>Todas</option>
                    <option value="NC">Notas Crédito</option>
                    <option value="ND">Notas Débito</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="agrupacion" class="form-label">Agrupación</label>
                <select class="form-select" id="agrupacion">
                    <option value="diaria">Diaria</option>
                    <option value="mensual" selected>Mensual</option>
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
                <h5 class="metric-title"><i class="fas fa-list-alt me-2"></i>Item Detalle</h5>
                <div class="metric-value" id="totalLineasDetalle">0</div>
                <div class="text-muted">Total de Item</div>
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
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="groupByMonthCausas">
                                    <label class="form-check-label" for="groupByMonthCausas">Agrupar por Mes</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Causa</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                               
                                                <th>% del Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaCausas">
                                            <tr>
                                                <td colspan="7" class="text-center">Cargando datos...</td>
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
                                <span><i class="fas fa-table me-2"></i>Detalle de Notas por Vendedor</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="groupByMonthVendedores">
                                    <label class="form-check-label" for="groupByMonthVendedores">Agrupar por Mes</label>
                                </div>
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
    <script>
        // Variables globales para los gráficos
        let evolucionMensualChart, distribucionTipoChart, causasChart, topCausasChart, vendedoresChart, topVendedoresChart;
        
        // Función para cargar datos desde el servidor
        function cargarDatos() {
            // Mostrar estado de carga
            document.getElementById('estadoCarga').style.display = 'flex';
            
            // Obtener parámetros del formulario
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const tipoNota = document.getElementById('tipoNota').value;
            const agrupacion = document.getElementById('agrupacion').value;

            // Realizar solicitud al servidor
            fetch('obtener_datos_notas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    tipoNota: tipoNota,
                    agrupacion: agrupacion
                })
            })
            .then(response => {
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Respuesta del servidor:', text);
                        throw new Error('El servidor no devolvió JSON válido. Verifica la configuración del backend.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Actualizar métricas principales
                    document.getElementById('totalNotas').textContent = data.totalNotas.toLocaleString();
                     document.getElementById('totalLineasDetalle').textContent = data.totalLineasDetalle.toLocaleString();
                    //document.getElementById('montoTotal').textContent = `$${data.montoTotal.toLocaleString()}`;
                    document.getElementById('notasPorDia').textContent = data.notasPorDia.toFixed(1);
                    document.getElementById('causaPrincipal').textContent = data.causaPrincipal;
                    
                    // Actualizar gráficos
                    actualizarGraficos(data);
                    
                    // Actualizar tablas
                    actualizarTablaCausas(data.causasData);
                    actualizarTablaVendedores(data.vendedoresData);
                    
                    // Ocultar estado de carga
                    document.getElementById('estadoCarga').style.display = 'none';
                } else {
                    throw new Error(data.error || 'Error al cargar los datos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('estadoCarga').style.display = 'none';
                alert('Error al cargar los datos: ' + error.message);
            });
        }

        // Función para actualizar todos los gráficos
        function actualizarGraficos(data) {
            actualizarEvolucionMensual(data.evolucionMensual);
            actualizarDistribucionTipo(data.distribucionTipo);
            actualizarGraficoCausas(data.causasData);
            actualizarTopCausas(data.causasData);
            actualizarGraficoVendedores(data.vendedoresData);
            actualizarTopVendedores(data.vendedoresData);
            actualizarResumenCausas(data.causasData);
            actualizarRankingVendedores(data.vendedoresData);
        }
        
        // Función para actualizar el gráfico de evolución mensual
        function actualizarEvolucionMensual(datos) {
            const ctx = document.getElementById('evolucionMensualChart').getContext('2d');
            
            if (evolucionMensualChart) {
                evolucionMensualChart.destroy();
            }
            
            evolucionMensualChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: datos.labels,
                    datasets: [{
                        label: 'Notas de Crédito',
                        data: datos.ncData,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Notas de Débito',
                        data: datos.ndData,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Evolución Mensual de Notas'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Notas'
                            }
                        }
                    }
                }
            });
        }
        
        // Función para actualizar el gráfico de distribución por tipo
        function actualizarDistribucionTipo(datos) {
            const ctx = document.getElementById('distribucionTipoChart').getContext('2d');
            
            if (distribucionTipoChart) {
                distribucionTipoChart.destroy();
            }
            
            distribucionTipoChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Notas de Crédito', 'Notas de Débito'],
                    datasets: [{
                        data: [datos.ncCount, datos.ndCount],
                        backgroundColor: ['#3498db', '#e74c3c']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Función para actualizar el gráfico de causas
        function actualizarGraficoCausas(datos) {
            const ctx = document.getElementById('causasChart').getContext('2d');
            
            if (causasChart) {
                causasChart.destroy();
            }
            
            // Ordenar por cantidad descendente
            const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
            
            causasChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedData.map(item => item.causa),
                    datasets: [{
                        label: 'Cantidad de Notas',
                        data: sortedData.map(item => item.cantidad),
                        backgroundColor: sortedData.map((item, index) => {
                            return `hsl(${index * 36}, 70%, 60%)`;
                        }),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Notas por Causa'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Notas'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
        
        // Función para actualizar el gráfico de top causas
        function actualizarTopCausas(datos) {
            const ctx = document.getElementById('topCausasChart').getContext('2d');
            
            if (topCausasChart) {
                topCausasChart.destroy();
            }
            
            // Tomar las top 5 causas
            const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
            
            topCausasChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: top5.map(item => item.causa),
                    datasets: [{
                        data: top5.map(item => item.cantidad),
                        backgroundColor: [
                            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 5 Causas'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Función para actualizar el resumen de causas
        function actualizarResumenCausas(datos) {
            const resumenCausas = document.getElementById('resumenCausas');
            const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
            
            let html = '';
            top5.forEach((item, index) => {
                html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge causa-color-${index + 1}">${item.codigo}</span>
                    <span class="flex-grow-1 ms-2">${item.causa}</span>
                    <strong>${item.cantidad}</strong>
                </div>
                `;
            });
            
            resumenCausas.innerHTML = html;
        }
        
        // Función para actualizar la tabla de causas
        function actualizarTablaCausas(datos) {
            const tablaBody = document.getElementById('tablaCausas');
            
            // Ordenar por cantidad descendente
            const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
            
            // Calcular totales para porcentajes
            const totalNotas = sortedData.reduce((sum, item) => sum + item.cantidad, 0);
            const totalMonto = sortedData.reduce((sum, item) => sum + item.monto, 0);
            
            let html = '';
            sortedData.forEach((item, index) => {
                const porcentaje = totalNotas > 0 ? ((item.cantidad / totalNotas) * 100).toFixed(1) : 0;
                const montoPromedio = item.cantidad > 0 ? (item.monto / item.cantidad).toFixed(2) : 0;
                
                html += `
                <tr>
                    <td><span class="badge bg-primary">${item.codigo}</span></td>
                    <td>${item.causa}</td>
                    <td>${item.descripcion}</td>
                    <td>${item.cantidad}</td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: ${porcentaje}%;" 
                                aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                ${porcentaje}%
                            </div>
                        </div>
                    </td>
                </tr>
                `;
            });
            
            tablaBody.innerHTML = html;
        }

        // Funciones para vendedores (similares a las de causas)
        function actualizarGraficoVendedores(datos) {
            const ctx = document.getElementById('vendedoresChart').getContext('2d');
            
            if (vendedoresChart) {
                vendedoresChart.destroy();
            }
            
            // Ordenar por cantidad descendente
            const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
            
            vendedoresChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedData.map(item => item.nombre),
                    datasets: [{
                        label: 'Cantidad de Notas',
                        data: sortedData.map(item => item.cantidad),
                        backgroundColor: sortedData.map((item, index) => {
                            return `hsl(${index * 36}, 70%, 60%)`;
                        }),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Notas por Vendedor'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Notas'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
        
        function actualizarTopVendedores(datos) {
            const ctx = document.getElementById('topVendedoresChart').getContext('2d');
            
            if (topVendedoresChart) {
                topVendedoresChart.destroy();
            }
            
            // Tomar los top 5 vendedores
            const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
            
            topVendedoresChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: top5.map(item => item.nombre),
                    datasets: [{
                        data: top5.map(item => item.cantidad),
                        backgroundColor: [
                            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 5 Vendedores'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function actualizarRankingVendedores(datos) {
            const rankingVendedores = document.getElementById('rankingVendedores');
            const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
            
            let html = '';
            top5.forEach((item, index) => {
                html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge bg-primary me-2">${index + 1}</span>
                        <span>${item.nombre}</span>
                    </div>
                    <strong>${item.cantidad}</strong>
                </div>
                `;
            });
            
            rankingVendedores.innerHTML = html;
        }
        
        function actualizarTablaVendedores(datos) {
            const tablaBody = document.getElementById('tablaVendedores');
            
            // Ordenar por cantidad descendente
            const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
            
            // Calcular totales para porcentajes
            const totalNotas = sortedData.reduce((sum, item) => sum + item.cantidad, 0);
            const totalMonto = sortedData.reduce((sum, item) => sum + item.monto, 0);
            
            let html = '';
            sortedData.forEach(item => {
                const porcentaje = totalNotas > 0 ? ((item.cantidad / totalNotas) * 100).toFixed(1) : 0;
                const montoPromedio = item.cantidad > 0 ? (item.monto / item.cantidad).toFixed(2) : 0;
                
                html += `
                <tr>
                    <td>${item.nombre} <span class="badge bg-secondary">${item.codigo}</span></td>
                    <td>${item.cantidad}</td>
                    <td>${porcentaje}%</td>
                    <td><span class="badge bg-info">${item.causaPrincipal || 'N/A'}</span></td>
                </tr>
                `;
            });
            
            tablaBody.innerHTML = html;
        }
        



        
        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarDatos();
            
            // Configurar event listeners para los checkboxes de agrupación
            document.getElementById('groupByMonthCausas').addEventListener('change', function() {
                // Lógica para reagrupar datos de causas por mes
                alert('Función de agrupamiento por mes será implementada en la versión final');
            });
            
            document.getElementById('groupByMonthVendedores').addEventListener('change', function() {
                // Lógica para reagrupar datos de vendedores por mes
                alert('Función de agrupamiento por mes será implementada en la versión final');
            });
        });
    </script>
</body>
</html>