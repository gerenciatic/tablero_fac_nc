<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Análisis de Ventas - Versión Mejorada</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --gray-color: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            padding-bottom: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            color: var(--dark-color);
            font-size: 24px;
        }
        
        .filters {
            display: flex;
            gap: 15px;
        }
        
        select, input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .card-content {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .kpi-card {
            text-align: center;
        }
        
        .kpi-value {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .kpi-label {
            color: var(--gray-color);
            font-size: 14px;
        }
        
        .kpi-positive {
            color: var(--secondary-color);
        }
        
        .kpi-negative {
            color: var(--danger-color);
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            border-bottom: 3px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .data-highlight {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .data-section {
            margin-bottom: 20px;
        }
        
        .data-section h3 {
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .improvement-note {
            background-color: #fff8e1;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 14px;
            border-left: 4px solid var(--warning-color);
        }
        
        .code-comment {
            color: #666;
            font-style: italic;
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Dashboard de Análisis de Ventas - Versión Mejorada</h1>
            <div class="filters">
                <select id="periodo">
                    <option value="mensual">Mensual</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="anual">Anual</option>
                </select>
                <select id="vendedor">
                    <option value="todos">Todos los vendedores</option>
                    <option value="juan">Juan Pérez</option>
                    <option value="maria">María García</option>
                    <option value="carlos">Carlos López</option>
                </select>
                <input type="month" id="fecha" value="2023-11">
            </div>
        </header>
        
        <div class="improvement-note">
            <strong>MEJORA IMPLEMENTADA:</strong> Se ha corregido la estructura de datos para diferenciar correctamente entre Nota Cabecera y Nota Detalle.
        </div>
        
        <div class="dashboard-grid">
            <!-- KPI Cards -->
            <div class="card kpi-card">
                <div class="card-header">
                    <div class="card-title">Ventas Totales</div>
                </div>
                <div class="card-content">
                    <div>
                        <div class="kpi-value">$125,430</div>
                        <div class="kpi-label kpi-positive">+12.5% vs mes anterior</div>
                    </div>
                </div>
            </div>
            
            <div class="card kpi-card">
                <div class="card-header">
                    <div class="card-title">Notas de Crédito (Cabecera)</div>
                </div>
                <div class="card-content">
                    <div>
                        <div class="kpi-value">$8,750</div>
                        <div class="kpi-label">15 notas de crédito</div>
                    </div>
                </div>
            </div>
            
            <div class="card kpi-card">
                <div class="card-header">
                    <div class="card-title">Tasa de Devolución</div>
                </div>
                <div class="card-content">
                    <div>
                        <div class="kpi-value">6.5%</div>
                        <div class="kpi-label kpi-negative">+0.8% vs mes anterior</div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Ventas vs Notas de Crédito -->
            <div class="card full-width">
                <div class="card-header">
                    <div class="card-title">Ventas vs Notas de Crédito (Evolución Mensual)</div>
                </div>
                <div class="card-content">
                    <canvas id="ventasVsNcChart"></canvas>
                </div>
            </div>
            
            <!-- Gráfico de Causas de NC -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Causas de Notas de Crédito</div>
                </div>
                <div class="card-content">
                    <canvas id="causasChart"></canvas>
                </div>
            </div>
            
            <!-- Gráfico de Ventas por Vendedor -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Ventas por Vendedor</div>
                </div>
                <div class="card-content">
                    <canvas id="vendedoresChart"></canvas>
                </div>
            </div>
            
            <!-- Análisis Detallado -->
            <div class="card full-width">
                <div class="card-header">
                    <div class="card-title">Análisis Detallado - Notas de Crédito</div>
                </div>
                <div class="card-content">
                    <div class="tabs">
                        <div class="tab active" data-tab="cabecera">Por Cabecera</div>
                        <div class="tab" data-tab="detalle">Por Detalle</div>
                        <div class="tab" data-tab="comparativa">Comparativa</div>
                    </div>
                    
                    <div class="tab-content active" id="cabecera-tab">
                        <div class="data-highlight">
                            <strong>Análisis por Nota Cabecera:</strong> Cada fila representa una nota de crédito completa con su monto total.
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Nota</th>
                                    <th>Fecha</th>
                                    <th>Vendedor</th>
                                    <th>Cliente</th>
                                    <th>Monto Total</th>
                                    <th>Causa Principal</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-cabecera">
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-content" id="detalle-tab">
                        <div class="data-highlight">
                            <strong>Análisis por Nota Detalle:</strong> Cada fila representa un producto dentro de una nota de crédito.
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Nota</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Causa Específica</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-detalle">
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-content" id="comparativa-tab">
                        <div class="data-highlight">
                            <strong>Comparativa Cabecera vs Detalle:</strong> Resumen de diferencias clave entre ambos enfoques.
                        </div>
                        <div class="data-section">
                            <h3>Resumen por Cabecera</h3>
                            <div id="resumen-cabecera">
                                <!-- Los datos se cargarán dinámicamente -->
                            </div>
                        </div>
                        <div class="data-section">
                            <h3>Resumen por Detalle</h3>
                            <div id="resumen-detalle">
                                <!-- Los datos se cargarán dinámicamente -->
                            </div>
                        </div>
                        <div class="data-section">
                            <h3>Diferencias Clave</h3>
                            <div id="diferencias">
                                <!-- Las diferencias se calcularán dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="improvement-note">
            <strong>MEJORAS IMPLEMENTADAS:</strong>
            <ul>
                <li>Estructura de datos diferenciada para Nota Cabecera y Nota Detalle</li>
                <li>Gráfico comparativo de Ventas vs Notas de Crédito</li>
                <li>Análisis detallado con pestañas para diferentes perspectivas</li>
                <li>Cálculos correctos basados en la estructura de datos apropiada</li>
            </ul>
        </div>
    </div>

    <script>
        // =============================================
        // ESTRUCTURA DE DATOS MEJORADA
        // =============================================
        
        // Datos de ejemplo con estructura corregida
        const datosMejorados = {
            // Datos de ventas
            ventas: {
                total: 125430,
                porVendedor: [
                    { vendedor: "Juan Pérez", monto: 45200, cantidad: 45 },
                    { vendedor: "María García", monto: 38750, cantidad: 38 },
                    { vendedor: "Carlos López", monto: 41480, cantidad: 42 }
                ],
                porDepartamento: [
                    { departamento: "Electrónica", monto: 52300, porcentaje: 41.7 },
                    { departamento: "Hogar", monto: 38100, porcentaje: 30.4 },
                    { departamento: "Ropa", monto: 35030, porcentaje: 27.9 }
                ]
            },
            
            // NOTAS DE CRÉDITO - ESTRUCTURA CORREGIDA
            notasCredito: {
                // Datos por CABECERA (nota completa)
                porCabecera: [
                    {
                        id: "NC-001",
                        fecha: "2023-11-05",
                        vendedor: "Juan Pérez",
                        cliente: "Cliente A",
                        montoTotal: 1200,
                        causaPrincipal: "Producto defectuoso",
                        detalles: [
                            { producto: "Smartphone X", cantidad: 1, precio: 1000, subtotal: 1000, causa: "Pantalla rota" },
                            { producto: "Funda protectora", cantidad: 1, precio: 200, subtotal: 200, causa: "No compatible" }
                        ]
                    },
                    {
                        id: "NC-002",
                        fecha: "2023-11-12",
                        vendedor: "María García",
                        cliente: "Cliente B",
                        montoTotal: 850,
                        causaPrincipal: "Error en pedido",
                        detalles: [
                            { producto: "Lámpara LED", cantidad: 2, precio: 300, subtotal: 600, causa: "Color incorrecto" },
                            { producto: "Cable USB", cantidad: 1, precio: 250, subtotal: 250, causa: "Longitud incorrecta" }
                        ]
                    },
                    {
                        id: "NC-003",
                        fecha: "2023-11-18",
                        vendedor: "Carlos López",
                        cliente: "Cliente C",
                        montoTotal: 1500,
                        causaPrincipal: "Insatisfacción del cliente",
                        detalles: [
                            { producto: "Tablet Z", cantidad: 1, precio: 1500, subtotal: 1500, causa: "Rendimiento insuficiente" }
                        ]
                    }
                ],
                
                // Datos por DETALLE (productos individuales)
                porDetalle: [
                    // Estos datos se calcularán a partir de porCabecera
                ],
                
                // Resumen por causas
                porCausa: [
                    { causa: "Producto defectuoso", monto: 3200, cantidad: 3, porcentaje: 36.6 },
                    { causa: "Error en pedido", monto: 2100, cantidad: 2, porcentaje: 24.0 },
                    { causa: "Insatisfacción del cliente", monto: 1850, cantidad: 2, porcentaje: 21.1 },
                    { causa: "Entrega tardía", monto: 1600, cantidad: 1, porcentaje: 18.3 }
                ]
            },
            
            // Evolución mensual
            evolucionMensual: [
                { mes: "Ene", ventas: 110000, notasCredito: 7200 },
                { mes: "Feb", ventas: 105000, notasCredito: 6800 },
                { mes: "Mar", ventas: 115000, notasCredito: 7500 },
                { mes: "Abr", ventas: 120000, notasCredito: 8200 },
                { mes: "May", ventas: 118000, notasCredito: 7900 },
                { mes: "Jun", ventas: 122000, notasCredito: 8100 },
                { mes: "Jul", ventas: 119000, notasCredito: 8300 },
                { mes: "Ago", ventas: 124000, notasCredito: 8400 },
                { mes: "Sep", ventas: 121000, notasCredito: 8600 },
                { mes: "Oct", ventas: 123000, notasCredito: 8700 },
                { mes: "Nov", ventas: 125430, notasCredito: 8750 }
            ]
        };

        // =============================================
        // FUNCIONES DE CÁLCULO MEJORADAS
        // =============================================
        
        // Calcular datos por detalle a partir de los datos por cabecera
        function calcularDatosPorDetalle() {
            const detalles = [];
            datosMejorados.notasCredito.porCabecera.forEach(nota => {
                nota.detalles.forEach(detalle => {
                    detalles.push({
                        idNota: nota.id,
                        producto: detalle.producto,
                        cantidad: detalle.cantidad,
                        precioUnitario: detalle.precio,
                        subtotal: detalle.subtotal,
                        causa: detalle.causa
                    });
                });
            });
            datosMejorados.notasCredito.porDetalle = detalles;
            return detalles;
        }
        
        // Calcular total de notas de crédito por cabecera
        function calcularTotalNotasCabecera() {
            return datosMejorados.notasCredito.porCabecera.reduce((total, nota) => total + nota.montoTotal, 0);
        }
        
        // Calcular total de notas de crédito por detalle
        function calcularTotalNotasDetalle() {
            const detalles = calcularDatosPorDetalle();
            return detalles.reduce((total, detalle) => total + detalle.subtotal, 0);
        }
        
        // Calcular tasa de devolución
        function calcularTasaDevolucion() {
            const ventasTotales = datosMejorados.ventas.total;
            const notasCreditoTotales = calcularTotalNotasCabecera();
            return (notasCreditoTotales / ventasTotales * 100).toFixed(2);
        }
        
        // =============================================
        // INICIALIZACIÓN DEL DASHBOARD
        // =============================================
        
        document.addEventListener('DOMContentLoaded', function() {
            // Calcular datos iniciales
            calcularDatosPorDetalle();
            const tasaDevolucion = calcularTasaDevolucion();
            
            // Actualizar KPIs
            document.querySelectorAll('.kpi-card .kpi-value')[0].textContent = '$' + datosMejorados.ventas.total.toLocaleString();
            document.querySelectorAll('.kpi-card .kpi-value')[1].textContent = '$' + calcularTotalNotasCabecera().toLocaleString();
            document.querySelectorAll('.kpi-card .kpi-value')[2].textContent = tasaDevolucion + '%';
            
            // Gráfico de Ventas vs Notas de Crédito
            const ventasVsNcCtx = document.getElementById('ventasVsNcChart').getContext('2d');
            const ventasVsNcChart = new Chart(ventasVsNcCtx, {
                type: 'line',
                data: {
                    labels: datosMejorados.evolucionMensual.map(item => item.mes),
                    datasets: [
                        {
                            label: 'Ventas',
                            data: datosMejorados.evolucionMensual.map(item => item.ventas),
                            borderColor: 'rgba(52, 152, 219, 1)',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Notas de Crédito',
                            data: datosMejorados.evolucionMensual.map(item => item.notasCredito),
                            borderColor: 'rgba(231, 76, 60, 1)',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de causas de notas de crédito
            const causasCtx = document.getElementById('causasChart').getContext('2d');
            const causasChart = new Chart(causasCtx, {
                type: 'doughnut',
                data: {
                    labels: datosMejorados.notasCredito.porCausa.map(item => item.causa),
                    datasets: [{
                        data: datosMejorados.notasCredito.porCausa.map(item => item.porcentaje),
                        backgroundColor: [
                            'rgba(231, 76, 60, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(243, 156, 18, 0.7)',
                            'rgba(46, 204, 113, 0.7)'
                        ],
                        borderWidth: 1
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
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de ventas por vendedor
            const vendedoresCtx = document.getElementById('vendedoresChart').getContext('2d');
            const vendedoresChart = new Chart(vendedoresCtx, {
                type: 'pie',
                data: {
                    labels: datosMejorados.ventas.porVendedor.map(item => item.vendedor),
                    datasets: [{
                        data: datosMejorados.ventas.porVendedor.map(item => item.monto),
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(243, 156, 18, 0.7)'
                        ],
                        borderWidth: 1
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
                                    return context.label + ': $' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Llenar tabla de notas por cabecera
            const tablaCabecera = document.getElementById('tabla-cabecera');
            datosMejorados.notasCredito.porCabecera.forEach(nota => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${nota.id}</td>
                    <td>${nota.fecha}</td>
                    <td>${nota.vendedor}</td>
                    <td>${nota.cliente}</td>
                    <td>$${nota.montoTotal.toLocaleString()}</td>
                    <td>${nota.causaPrincipal}</td>
                `;
                tablaCabecera.appendChild(fila);
            });
            
            // Llenar tabla de notas por detalle
            const tablaDetalle = document.getElementById('tabla-detalle');
            calcularDatosPorDetalle().forEach(detalle => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${detalle.idNota}</td>
                    <td>${detalle.producto}</td>
                    <td>${detalle.cantidad}</td>
                    <td>$${detalle.precioUnitario.toLocaleString()}</td>
                    <td>$${detalle.subtotal.toLocaleString()}</td>
                    <td>${detalle.causa}</td>
                `;
                tablaDetalle.appendChild(fila);
            });
            
            // Llenar resumen comparativo
            document.getElementById('resumen-cabecera').innerHTML = `
                <p><strong>Total de notas:</strong> ${datosMejorados.notasCredito.porCabecera.length}</p>
                <p><strong>Monto total:</strong> $${calcularTotalNotasCabecera().toLocaleString()}</p>
                <p><strong>Promedio por nota:</strong> $${(calcularTotalNotasCabecera() / datosMejorados.notasCredito.porCabecera.length).toLocaleString()}</p>
            `;
            
            document.getElementById('resumen-detalle').innerHTML = `
                <p><strong>Total de productos devueltos:</strong> ${calcularDatosPorDetalle().length}</p>
                <p><strong>Monto total:</strong> $${calcularTotalNotasDetalle().toLocaleString()}</p>
                <p><strong>Promedio por producto:</strong> $${(calcularTotalNotasDetalle() / calcularDatosPorDetalle().length).toLocaleString()}</p>
            `;
            
            // Calcular diferencias
            const diferenciaMontos = calcularTotalNotasCabecera() - calcularTotalNotasDetalle();
            document.getElementById('diferencias').innerHTML = `
                <p><strong>Diferencia en montos:</strong> $${diferenciaMontos.toLocaleString()}</p>
                <p><strong>Coincidencia de datos:</strong> ${diferenciaMontos === 0 ? 'Perfecta' : 'Hay discrepancias'}</p>
                <p class="code-comment">Nota: En una implementación correcta, ambos totales deberían coincidir exactamente.</p>
            `;
            
            // Funcionalidad de pestañas
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
            
            // Funcionalidad de filtros
            const filters = document.querySelectorAll('.filters select, .filters input');
            filters.forEach(filter => {
                filter.addEventListener('change', function() {
                    console.log('Filtro cambiado:', this.id, this.value);
                    // En una implementación real, aquí se recargarían los datos
                });
            });
        });
    </script>
</body>
</html>