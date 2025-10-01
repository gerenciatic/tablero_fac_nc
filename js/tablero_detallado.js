// Variables globales para los gráficos
let evolucionMensualChart, distribucionTipoChart, causasChart, topCausasChart;
let vendedoresChart, topVendedoresChart, causasVendedorChart;
let departamentosChart, topDepartamentosChart;

// Variables globales para productos
let datosProductos = [];
let productosFiltrados = [];
let topProductosChart, departamentoProductosChart;

// Lista de feriados en Venezuela (2024-2025)
const feriadosVenezuela = [
    '2024-01-01', '2024-03-03', '2024-03-04', '2024-05-01', 
    '2024-12-25','2024-12-31'
];

// Función para calcular días hábiles incluyendo feriados
function calcularDiasHabilesConFeriados(fechaInicio, fechaFin) {
    let diasHabiles = 0;
    const fechaActual = new Date(fechaInicio);
    const fechaFinal = new Date(fechaFin);
    
    if (isNaN(fechaActual.getTime()) || isNaN(fechaFinal.getTime())) {
        return 0;
    }
    
    while (fechaActual <= fechaFinal) {
        const diaSemana = fechaActual.getDay();
        const fechaFormateada = fechaActual.toISOString().split('T')[0];
        
        if (diaSemana !== 0 && diaSemana !== 6 && !feriadosVenezuela.includes(fechaFormateada)) {
            diasHabiles++;
        }
        
        fechaActual.setDate(fechaActual.getDate() + 1);
    }
    
    return diasHabiles;
}

// Función principal cargarDatos - COMPLETAMENTE CORREGIDA
function cargarDatos() {
    document.getElementById('estadoCarga').style.display = 'flex';
    
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const tipoNota = document.getElementById('tipoNota').value;
    const agrupacion = document.getElementById('agrupacion').value;

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
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Respuesta del servidor:', text);
                throw new Error('El servidor no devolvió JSON válido');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // ✅ GUARDAR DATOS DE PRODUCTOS GLOBALMENTE
            datosProductos = data.productosData || [];
            productosFiltrados = [...datosProductos];
            
            console.log('✅ DATOS RECIBIDOS DEL PHP:');
            console.log('causasPorVendedorData:', data.causasPorVendedorData);
            console.log('productosData:', datosProductos);
            
            const diasHabiles = calcularDiasHabilesConFeriados(fechaInicio, fechaFin);
            
            // Mostrar datos básicos
            document.getElementById('totalNotas').textContent = data.totalNotasCabecero.toLocaleString();
            document.getElementById('totalLineasDetalle').textContent = data.totalLineasDetalle.toLocaleString();
            document.getElementById('diasHabiles').textContent = diasHabiles;
            document.getElementById('causaPrincipal').textContent = data.causaPrincipalDetalle;
            
            // Cálculos corregidos
            const notasPorDia = diasHabiles > 0 ? (data.totalNotasCabecero / diasHabiles) : 0;
            document.getElementById('notasPorDia').textContent = notasPorDia.toFixed(1);
            
            const lineasPorNota = data.totalNotasCabecero > 0 ? (data.totalLineasDetalle / data.totalNotasCabecero) : 0;
            const eficiencia = data.totalNotasCabecero > 0 ? Math.min(100, (100 - (data.totalNotasCabecero / data.totalLineasDetalle * 100)).toFixed(1)) : 0;
            
            document.getElementById('lineasPorNota').textContent = lineasPorNota.toFixed(1);
            document.getElementById('eficiencia').textContent = `${eficiencia}%`;
            document.getElementById('totalDepartamentos').textContent = data.departamentosData ? data.departamentosData.length : 0;
            
            actualizarGraficos(data);
            
            // ✅ CORRECCIÓN CRÍTICA: Usar causasPorVendedorData en lugar de vendedoresData
            actualizarTablaCausas(data.causasDetalleData, data.totalLineasDetalle);
            actualizarTablaVendedores(data.causasPorVendedorData, data.totalLineasDetalle);
            prepararAnalisisVendedor(data.causasPorVendedorData, data.totalNotasCabecero, data.totalLineasDetalle);
            
            if (data.departamentosData) {
                cargarDatosDepartamentos(data.departamentosData, data.totalLineasDetalle);
            }
            
            // Si estamos en la pestaña de productos, actualizar la vista
            if (document.getElementById('productos-tab').classList.contains('active')) {
                actualizarVistaProductos();
            }
            
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
    actualizarGraficoCausas(data.causasDetalleData);
    actualizarTopCausas(data.causasDetalleData, data.totalLineasDetalle);
    actualizarResumenCausas(data.causasDetalleData, data.totalLineasDetalle);
    actualizarGraficoVendedores(data.causasPorVendedorData);
    actualizarTopVendedores(data.causasPorVendedorData, data.totalLineasDetalle);
    actualizarRankingVendedores(data.causasPorVendedorData, data.totalLineasDetalle);
}

// ========== FUNCIONES PARA DEPARTAMENTOS ==========
function cargarDatosDepartamentos(datosDepartamentos, totalLineasDetalle) {
    if (!datosDepartamentos || datosDepartamentos.length === 0) {
        console.warn('No hay datos de departamentos disponibles');
        return;
    }
    
    actualizarGraficoDepartamentos(datosDepartamentos);
    actualizarTopDepartamentosChart(datosDepartamentos, totalLineasDetalle);
    actualizarResumenDepartamentos(datosDepartamentos, totalLineasDetalle);
    actualizarTablaDepartamentos(datosDepartamentos, totalLineasDetalle);
}

function actualizarGraficoDepartamentos(datosDepartamentos) {
    const ctx = document.getElementById('departamentosChart').getContext('2d');
    if (departamentosChart) departamentosChart.destroy();
    
    const sortedData = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 15);
    
    departamentosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(item => item.descripcion || item.codigo),
            datasets: [{
                label: 'Cantidad de Notas',
                data: sortedData.map(item => item.cantidad),
                backgroundColor: sortedData.map((item, index) => `hsl(${index * 25}, 70%, 60%)`),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Top 15 Departamentos' },
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cantidad de Notas' } },
                x: { ticks: { maxRotation: 45, minRotation: 45, font: { size: 10 } } }
            }
        }
    });
}

function actualizarTopDepartamentosChart(datosDepartamentos, totalLineasDetalle) {
    const ctx = document.getElementById('topDepartamentosChart').getContext('2d');
    if (topDepartamentosChart) topDepartamentosChart.destroy();
    
    const top5 = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
    topDepartamentosChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: top5.map(item => item.descripcion || item.codigo),
            datasets: [{
                data: top5.map(item => item.cantidad),
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Top 5 Departamentos' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = totalLineasDetalle;
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}



function actualizarResumenDepartamentos(datosDepartamentos, totalLineasDetalle) {
    const resumenDepartamentos = document.getElementById('resumenDepartamentos');
    if (!datosDepartamentos || datosDepartamentos.length === 0) {
        resumenDepartamentos.innerHTML = '<p class="text-center">No hay datos de departamentos</p>';
        return;
    }
    
    const top5 = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    const departamentoPrincipal = top5[0] || { descripcion: '-', cantidad: 0 };
    const porcentajePrincipal = totalLineasDetalle > 0 ? ((departamentoPrincipal.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
    
    // Calcular total del top 5
    const totalTop5 = top5.reduce((sum, item) => sum + item.cantidad, 0);
    const porcentajeTotal = totalLineasDetalle > 0 ? ((totalTop5 / totalLineasDetalle) * 100).toFixed(1) : 0;
    
    let html = `
        <div class="mb-2"><strong>Total Departamentos:</strong> ${datosDepartamentos.length}</div>
        <div class="mb-2"><strong>Departamento Principal:</strong> ${departamentoPrincipal.descripcion || departamentoPrincipal.codigo}</div>
        <div class="mb-2"><strong>Líneas del Depto. Principal:</strong> ${departamentoPrincipal.cantidad}</div>
        <div class="mb-2"><strong>% del Total:</strong> ${porcentajePrincipal}%</div>
        <hr>
        <h6>Top 5 Departamentos:</h6>
    `;
    
    top5.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
        html += `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-primary">${index + 1}</span>
            <span class="flex-grow-1 ms-2" style="font-size: 0.9em;">${item.descripcion || item.codigo}</span>
            <strong>${item.cantidad}</strong>
            <small class="text-muted ms-2">${porcentaje}%</small>
        </div>
        `;
    });
    
    // ✅ AGREGAR TOTAL DEL TOP 5
    html += `
    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
        <div><strong>TOTAL TOP 5:</strong></div>
        <div>
            <strong>${totalTop5}</strong>
            <small class="text-muted ms-2">${porcentajeTotal}%</small>
        </div>
    </div>`;
    
    resumenDepartamentos.innerHTML = html;
}







function actualizarTablaDepartamentos(datosDepartamentos, totalLineasDetalle) {
    const tablaBody = document.getElementById('tablaDepartamentos');
    if (!datosDepartamentos || datosDepartamentos.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="6" class="text-center">No hay datos de departamentos disponibles</td></tr>';
        return;
    }
    
    const sortedData = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad);
    let html = '';
    let totalReal = 0;
    
    sortedData.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(2) : 0;
        const causaPrincipal = item.causaPrincipal || '-';
        totalReal += item.cantidad;
        
        html += `
        <tr>
            <td><span class="badge bg-primary">${item.codigo}</span></td>
            <td>${item.descripcion || item.codigo}</td>
            <td>${item.descripcion || 'Sin descripción'}</td>
            <td>${item.cantidad}</td>
            <td>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" style="width: ${porcentaje}%;" 
                        aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                        ${porcentaje}%
                    </div>
                </div>
            </td>
            <td><span class="badge bg-info">${causaPrincipal}</span></td>
        </tr>`;
    });
    
    // ✅ AGREGAR FILA DE TOTAL AL FINAL
    const porcentajeTotal = totalLineasDetalle > 0 ? ((totalReal / totalLineasDetalle) * 100).toFixed(2) : 0;
    
    html += `
    <tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">
        <td colspan="3"><strong>TOTAL</strong></td>
        <td><strong>${totalReal}</strong></td>
        <td>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: ${porcentajeTotal}%;" 
                    aria-valuenow="${porcentajeTotal}" aria-valuemin="0" aria-valuemax="100">
                    ${porcentajeTotal}%
                </div>
            </div>
        </td>
        <td><span class="badge">-</span></td>
    </tr>`;
    
    tablaBody.innerHTML = html;
}




// ========== FUNCIONES EXISTENTES ACTUALIZADAS ==========
function actualizarEvolucionMensual(datos) {
    const ctx = document.getElementById('evolucionMensualChart').getContext('2d');
    if (evolucionMensualChart) evolucionMensualChart.destroy();
    
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
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { title: { display: true, text: 'Evolución Mensual de Notas' } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cantidad de Notas' } }
            }
        }
    });
}

function actualizarDistribucionTipo(datos) {
    const ctx = document.getElementById('distribucionTipoChart').getContext('2d');
    if (distribucionTipoChart) distribucionTipoChart.destroy();
    
    distribucionTipoChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Notas de Crédito'],
            datasets: [{ data: [datos.ncCount], backgroundColor: ['#3498db'] }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
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

function actualizarGraficoCausas(datos) {
    const ctx = document.getElementById('causasChart').getContext('2d');
    if (causasChart) causasChart.destroy();
    
    const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 15);
    
    causasChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(item => item.causa),
            datasets: [{
                label: 'Cantidad de Notas',
                data: sortedData.map(item => item.cantidad),
                backgroundColor: sortedData.map((item, index) => `hsl(${index * 25}, 70%, 60%)`),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Top 15 Causas (Detalle)' },
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cantidad de Notas' } },
                x: { ticks: { maxRotation: 45, minRotation: 45, font: { size: 10 } } }
            }
        }
    });
}

function actualizarTopCausas(datos, totalLineasDetalle) {
    const ctx = document.getElementById('topCausasChart').getContext('2d');
    if (topCausasChart) topCausasChart.destroy();
    
    const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
    topCausasChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: top5.map(item => item.causa),
            datasets: [{
                data: top5.map(item => item.cantidad),
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Top 5 Causas' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = totalLineasDetalle;
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}




function actualizarResumenCausas(datos, totalLineasDetalle) {
    const resumenCausas = document.getElementById('resumenCausas');
    const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
    // Calcular total del top 5
    const totalTop5 = top5.reduce((sum, item) => sum + item.cantidad, 0);
    const porcentajeTotal = totalLineasDetalle > 0 ? ((totalTop5 / totalLineasDetalle) * 100).toFixed(1) : 0;
    
    let html = '<h6>Top 5 Causas:</h6>';
    top5.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
        html += `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge causa-color-${index + 1}">${item.codigo}</span>
            <span class="flex-grow-1 ms-2" style="font-size: 0.9em;">${item.causa}</span>
            <strong>${item.cantidad}</strong>
            <small class="text-muted ms-2">${porcentaje}%</small>
        </div>
        `;
    });
    
    // ✅ AGREGAR TOTAL DEL TOP 5
    html += `
    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
        <div><strong>TOTAL TOP 5:</strong></div>
        <div>
            <strong>${totalTop5}</strong>
            <small class="text-muted ms-2">${porcentajeTotal}%</small>
        </div>
    </div>`;
    
    resumenCausas.innerHTML = html;
}







function actualizarTablaCausas(datos, totalLineasDetalle) {
    const tablaBody = document.getElementById('tablaCausas');
    
    // Verificar si hay datos
    if (!datos || datos.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay datos disponibles</td></tr>';
        return;
    }
    
    const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
    let html = '';
    let totalReal = 0;
    
    sortedData.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
        totalReal += item.cantidad;
        
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
    
    // ✅ AGREGAR FILA DE TOTAL AL FINAL
    const porcentajeTotal = totalLineasDetalle > 0 ? ((totalReal / totalLineasDetalle) * 100).toFixed(1) : 0;
    
    html += `
    <tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">
        <td colspan="3"><strong>TOTAL</strong></td>
        <td><strong>${totalReal}</strong></td>
        <td>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: ${porcentajeTotal}%;" 
                    aria-valuenow="${porcentajeTotal}" aria-valuemin="0" aria-valuemax="100">
                    ${porcentajeTotal}%
                </div>
            </div>
        </td>
    </tr>`;
    
    tablaBody.innerHTML = html;
}




function actualizarGraficoVendedores(datos) {
    const ctx = document.getElementById('vendedoresChart').getContext('2d');
    if (vendedoresChart) vendedoresChart.destroy();
    
    const sortedData = [...datos].sort((a, b) => (b.total_causas || b.cantidad) - (a.total_causas || a.cantidad)).slice(0, 15);
    
    vendedoresChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(item => item.nombre),
            datasets: [{
                label: 'Cantidad de Notas',
                data: sortedData.map(item => item.total_causas || item.cantidad),
                backgroundColor: sortedData.map((item, index) => `hsl(${index * 25}, 70%, 60%)`),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Top 15 Vendedores' },
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cantidad de Notas' } },
                x: { ticks: { maxRotation: 45, minRotation: 45, font: { size: 10 } } }
            }
        }
    });
}


function actualizarTopVendedores(datos, totalLineasDetalle) {
    const ctx = document.getElementById('topVendedoresChart').getContext('2d');
    if (topVendedoresChart) topVendedoresChart.destroy();
    
    const top5 = [...datos].sort((a, b) => (b.total_causas || b.cantidad) - (a.total_causas || a.cantidad)).slice(0, 5);
    
    topVendedoresChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: top5.map(item => item.nombre),
            datasets: [{
                data: top5.map(item => item.total_causas || item.cantidad),
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6']
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
                            const total = totalLineasDetalle;
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                // ✅ AGREGAR SUBTÍTULO CON EL TOTAL
                subtitle: {
                    display: true,
                    text: `Total: ${top5.reduce((sum, item) => sum + (item.total_causas || item.cantidad), 0)}`,
                    position: 'bottom',
                    font: {
                        size: 12,
                        weight: 'bold'
                    }
                }
            }
        }
    });
}




function actualizarRankingVendedores(datos, totalLineasDetalle) {
    const rankingVendedores = document.getElementById('rankingVendedores');
    const top5 = [...datos].sort((a, b) => (b.total_causas || b.cantidad) - (a.total_causas || a.cantidad)).slice(0, 5);
    
    // Calcular total del top 5
    const totalTop5 = top5.reduce((sum, item) => sum + (item.total_causas || item.cantidad || 0), 0);
    const porcentajeTotal = totalLineasDetalle > 0 ? ((totalTop5 / totalLineasDetalle) * 100).toFixed(1) : 0;
    
    let html = '<h6>Top 5 Vendedores:</h6>';
    top5.forEach((item, index) => {
        const totalVendedor = item.total_causas || item.cantidad || 0;
        const porcentaje = totalLineasDetalle > 0 ? ((totalVendedor / totalLineasDetalle) * 100).toFixed(1) : 0;
        html += `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="badge bg-primary me-2">${index + 1}</span>
                <span style="font-size: 0.9em;">${item.nombre}</span>
            </div>
            <div>
                <strong>${totalVendedor}</strong>
                <small class="text-muted ms-2">${porcentaje}%</small>
            </div>
        </div>
        `;
    });
    
    // ✅ AGREGAR TOTAL DEL TOP 5
    html += `
    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
        <div><strong>TOTAL TOP 5:</strong></div>
        <div>
            <strong>${totalTop5}</strong>
            <small class="text-muted ms-2">${porcentajeTotal}%</small>
        </div>
    </div>`;
    
    rankingVendedores.innerHTML = html;
}





function actualizarTablaVendedores(datos, totalLineasDetalle) {
    const tablaBody = document.getElementById('tablaVendedores');
    
    if (!datos || datos.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="4" class="text-center">No hay datos disponibles</td></tr>';
        return;
    }
    
    const sortedData = [...datos].sort((a, b) => {
        const totalA = a.total_causas || a.cantidad || 0;
        const totalB = b.total_causas || b.cantidad || 0;
        return totalB - totalA;
    });
    
    let html = '';
    let totalReal = 0;
    
    sortedData.forEach(item => {
        const totalVendedor = item.total_causas || item.cantidad || 0;
        const porcentaje = totalLineasDetalle > 0 ? ((totalVendedor / totalLineasDetalle) * 100).toFixed(1) : 0;
        totalReal += totalVendedor;
        
        let causaPrincipal = 'N/A';
        
        if (item.causas && item.causas.length > 0) {
            const causasOrdenadas = [...item.causas].sort((a, b) => {
                const cantA = a.cantidad || 0;
                const cantB = b.cantidad || 0;
                return cantB - cantA;
            });
            
            const causaPrincipalObj = causasOrdenadas[0];
            causaPrincipal = causaPrincipalObj.causa || 
                           causaPrincipalObj.descripcion || 
                           causaPrincipalObj.nombre || 
                           'N/A';
        }
        
        html += `
        <tr>
            <td>${item.nombre || 'N/A'} <span class="badge bg-secondary">${item.codigo || 'N/A'}</span></td>
            <td>${totalVendedor}</td>
            <td>${porcentaje}%</td>
            <td><span class="badge bg-info">${causaPrincipal}</span></td>
        </tr>
        `;
    });
    
    // ✅ AGREGAR FILA DE TOTAL AL FINAL
    const porcentajeTotal = totalLineasDetalle > 0 ? ((totalReal / totalLineasDetalle) * 100).toFixed(1) : 0;
    
    html += `
    <tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">
        <td><strong>TOTAL</strong></td>
        <td><strong>${totalReal}</strong></td>
        <td>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: ${porcentajeTotal}%;" 
                    aria-valuenow="${porcentajeTotal}" aria-valuemin="0" aria-valuemax="100">
                    ${porcentajeTotal}%
                </div>
            </div>
        </td>
        <td><span class="badge">-</span></td>
    </tr>`;
    
    tablaBody.innerHTML = html;
}



// ========== FUNCIONES PARA ANÁLISIS DE VENDEDOR - CORREGIDAS ==========

// Función para preparar el análisis por vendedor - CORREGIDA
function prepararAnalisisVendedor(causasPorVendedorData, totalNotas, totalLineasDetalle) {
    const selectVendedor = document.getElementById('selectVendedor');
    selectVendedor.innerHTML = '<option value="">Seleccione un vendedor</option>';
    
    if (!causasPorVendedorData || !Array.isArray(causasPorVendedorData)) {
        console.error('❌ No hay datos de vendedores:', causasPorVendedorData);
        return;
    }
    
    causasPorVendedorData.sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(vendedor => {
        const totalVendedor = vendedor.total_causas || vendedor.cantidad || 0;
        const option = document.createElement('option');
        option.value = vendedor.codigo;
        option.textContent = `${vendedor.nombre} (${vendedor.codigo}) - ${totalVendedor} notas`;
        selectVendedor.appendChild(option);
    });

    selectVendedor.addEventListener('change', function() {
        const codigoVendedor = this.value;
        if (codigoVendedor) {
            mostrarAnalisisVendedor(codigoVendedor, causasPorVendedorData, totalNotas, totalLineasDetalle);
        } else {
            ocultarAnalisisVendedor();
        }
    });
}

// Función para mostrar análisis del vendedor - COMPLETAMENTE CORREGIDA
function mostrarAnalisisVendedor(codigoVendedor, causasPorVendedorData, totalNotas, totalLineasDetalle) {
    if (!causasPorVendedorData || !Array.isArray(causasPorVendedorData)) {
        console.error('❌ No hay datos de vendedores');
        return;
    }
    
    const vendedor = causasPorVendedorData.find(v => v && v.codigo === codigoVendedor);
    if (!vendedor) {
        console.error('❌ Vendedor no encontrado:', codigoVendedor);
        return;
    }

    // ✅ USAR VALORES CORRECTOS
    const causasVendedor = vendedor.causas || [];
    const totalVendedor = vendedor.total_causas || vendedor.cantidad || 0;
    const nombreVendedor = vendedor.nombre || 'Vendedor sin nombre';
    
    document.getElementById('vendedorTotalNotas').textContent = totalVendedor;
    document.getElementById('vendedorCausaPrincipal').textContent = vendedor.causaPrincipal || 'N/A';
    
    // ✅ CÁLCULOS CORRECTOS
    const eficienciaVendedor = totalLineasDetalle > 0 ? 
        ((totalVendedor / totalLineasDetalle) * 100).toFixed(1) : 0;
    const lineasPorNotaVendedor = totalVendedor > 0 ? 
        (totalVendedor / totalVendedor).toFixed(1) : 0;
    
    document.getElementById('vendedorEficiencia').textContent = `${eficienciaVendedor}%`;
    document.getElementById('vendedorLineasNota').textContent = lineasPorNotaVendedor;

    // ✅ ACTUALIZAR CON DATOS REALES
    actualizarGraficoCausasVendedor(causasVendedor, nombreVendedor, totalVendedor);
    actualizarTopCausasVendedor(causasVendedor, totalVendedor);
    actualizarTablaCausasVendedor(causasVendedor, totalVendedor);

    document.getElementById('contenidoVendedor').style.display = 'flex';
    document.getElementById('tablaCausasVendedor').style.display = 'block';
    document.getElementById('infoVendedor').style.display = 'none';
}

// Función para ocultar el análisis del vendedor
function ocultarAnalisisVendedor() {
    document.getElementById('contenidoVendedor').style.display = 'none';
    document.getElementById('tablaCausasVendedor').style.display = 'none';
    document.getElementById('infoVendedor').style.display = 'block';
}

// Función para actualizar gráfico de causas por vendedor - CORREGIDA
function actualizarGraficoCausasVendedor(causasData, nombreVendedor, totalVendedor) {
    const ctx = document.getElementById('causasVendedorChart').getContext('2d');
    if (causasVendedorChart) causasVendedorChart.destroy();

    if (!causasData || causasData.length === 0) {
        return;
    }

    const topCausas = [...causasData].sort((a, b) => b.cantidad - a.cantidad).slice(0, 8);
    
    causasVendedorChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: topCausas.map(item => item.causa),
            datasets: [{
                data: topCausas.map(item => item.cantidad),
                backgroundColor: [
                    '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
                    '#9b59b6', '#1abc9c', '#34495e', '#e67e22'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: `Distribución de Causas - ${nombreVendedor}`
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = totalVendedor;
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Función para actualizar top causas por vendedor - CORREGIDA
function actualizarTopCausasVendedor(causasData, totalVendedor) {
    const topCausasContainer = document.getElementById('topCausasVendedor');
    
    if (!causasData || causasData.length === 0) {
        topCausasContainer.innerHTML = '<p>No hay causas para este vendedor</p>';
        return;
    }
    
    const top5 = [...causasData].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
    let html = '<h6>Top 5 Causas del Vendedor:</h6>';
    top5.forEach((causa, index) => {
        const porcentaje = totalVendedor > 0 ? ((causa.cantidad / totalVendedor) * 100).toFixed(1) : 0;
        html += `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge causa-color-${index + 1}">${causa.codigo}</span>
            <span class="flex-grow-1 ms-2" style="font-size: 0.9em;">${causa.causa}</span>
            <strong>${causa.cantidad}</strong>
            <small class="text-muted ms-2">${porcentaje}%</small>
        </div>
        `;
    });
    
    // ✅ AGREGAR TOTAL DEL TOP 5
    const totalTop5 = top5.reduce((sum, causa) => sum + (causa.cantidad || 0), 0);
    const porcentajeTotal = totalVendedor > 0 ? ((totalTop5 / totalVendedor) * 100).toFixed(1) : 0;
    
    html += `
    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
        <div><strong>TOTAL TOP 5:</strong></div>
        <div>
            <strong>${totalTop5}</strong>
            <small class="text-muted ms-2">${porcentajeTotal}%</small>
        </div>
    </div>`;
    
    topCausasContainer.innerHTML = html;
}


// Función para actualizar tabla de causas por vendedor - COMPLETAMENTE CORREGIDA
function actualizarTablaCausasVendedor(causasData, totalVendedor) {
    const tablaBody = document.getElementById('tablaDetalleCausasVendedor');
    
    if (!tablaBody) {
        console.error('❌ No se encontró tablaDetalleCausasVendedor');
        return;
    }
    
    if (!causasData || !Array.isArray(causasData) || causasData.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="3" class="text-center">No hay datos de causas para este vendedor</td></tr>';
        return;
    }
    
    const sortedData = [...causasData].sort((a, b) => b.cantidad - a.cantidad);
    
    // ✅ CALCULAR TOTAL REAL
    const totalReal = totalVendedor > 0 ? totalVendedor : sortedData.reduce((sum, item) => sum + (item.cantidad || 0), 0);
    
    let html = '';
    sortedData.forEach((item) => {
        const cantidad = item.cantidad || 0;
        const porcentaje = totalReal > 0 ? ((cantidad / totalReal) * 100).toFixed(1) : 0;
        const causaNombre = item.causa || item.descripcion || 'Causa no especificada';
        
        html += `
        <tr>
            <td>${causaNombre}</td>
            <td>${cantidad}</td>
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
    
    // ✅ AGREGAR TOTAL
    html += `
    <tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">
        <td><strong>TOTAL</strong></td>
        <td><strong>${totalReal}</strong></td>
        <td><strong>100%</strong></td>
    </tr>
    `;
    
    tablaBody.innerHTML = html;
    
    console.log('✅ Tabla actualizada. Total real:', totalReal, 'Causas:', sortedData.length);
}

// ========== FUNCIONES PARA PRODUCTOS ==========

// Función para cargar datos de productos desde el servidor
function cargarDatosProductos() {
    console.log('🔄 Cargando datos de productos...');
    
    // Si ya tenemos datos de productos, usarlos
    if (datosProductos.length > 0) {
        console.log('✅ Usando datos existentes de productos:', datosProductos.length);
        productosFiltrados = [...datosProductos];
        actualizarVistaProductos();
        return;
    }
    
    // Si no hay datos, recargar todos los datos
    console.log('📥 No hay datos de productos, recargando...');
    cargarDatos();
}

// Función para actualizar la vista completa de productos - CORREGIDA
function actualizarVistaProductos() {
    console.log('📊 Actualizando vista de productos. Datos:', productosFiltrados.length);
    
    if (productosFiltrados.length === 0) {
        console.warn('No hay datos de productos disponibles');
        document.getElementById('totalProductos').textContent = '0';
        document.getElementById('notasConProductos').textContent = '0';
        document.getElementById('productoProblematico').textContent = '-';
        document.getElementById('causaPrincipalProductos').textContent = '-';
        
        // Limpiar tablas y gráficos
        const tbody = document.getElementById('tablaProductos');
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay datos disponibles</td></tr>';
        return;
    }
    
    // Calcular porcentajes
    const totalGeneral = productosFiltrados.reduce((sum, p) => sum + p.cantidad, 0);
    productosFiltrados.forEach(producto => {
        producto.porcentaje = totalGeneral > 0 ? ((producto.cantidad / totalGeneral) * 100).toFixed(1) : 0;
    });
    
    actualizarMetricasProductos();
    actualizarFiltrosProductos();
    actualizarGraficosProductos();
    actualizarTablaProductos();
}

// Función para actualizar métricas de productos - CORREGIDA
function actualizarMetricasProductos() {
    const productosUnicos = [...new Set(productosFiltrados.map(p => p.codigo))].length;
    const totalNotas = productosFiltrados.reduce((sum, p) => sum + p.cantidad, 0);
    
    // Producto más problemático
    const productosAgrupados = productosFiltrados.reduce((acc, producto) => {
        if (!acc[producto.codigo]) {
            acc[producto.codigo] = { 
                codigo: producto.codigo,
                descripcion: producto.descripcion,
                total: 0
            };
        }
        acc[producto.codigo].total += producto.cantidad;
        return acc;
    }, {});

    const productoMasProblematico = Object.values(productosAgrupados)
        .sort((a, b) => b.total - a.total)[0];

    // Causa principal
    const causas = productosFiltrados.reduce((acc, producto) => {
        acc[producto.causa] = (acc[producto.causa] || 0) + producto.cantidad;
        return acc;
    }, {});

    const causaPrincipal = Object.entries(causas)
        .sort((a, b) => b[1] - a[1])[0];

    document.getElementById('totalProductos').textContent = productosUnicos.toLocaleString();
    document.getElementById('notasConProductos').textContent = totalNotas.toLocaleString();
    
    document.getElementById('productoProblematico').textContent = 
        productoMasProblematico ? 
        (productoMasProblematico.descripcion.length > 15 ? 
         productoMasProblematico.descripcion.substring(0, 15) + '...' : 
         productoMasProblematico.descripcion) : '-';
    
    document.getElementById('causaPrincipalProductos').textContent = 
        causaPrincipal ? 
        (causaPrincipal[0].length > 15 ? 
         causaPrincipal[0].substring(0, 15) + '...' : 
         causaPrincipal[0]) : '-';
}

// Función para actualizar filtros de productos - MEJORADA
function actualizarFiltrosProductos() {
    const selectProducto = document.getElementById('selectProducto');
    const selectCausa = document.getElementById('selectCausaProducto');
    const selectVendedor = document.getElementById('selectVendedorProducto');
    const selectDepartamento = document.getElementById('selectDepartamentoProducto');

    if (!selectProducto || !selectCausa || !selectVendedor || !selectDepartamento) {
        console.error('Elementos de filtro de productos no encontrados');
        return;
    }

    // Obtener opciones únicas
    const productos = [...new Set(datosProductos.map(p => p.codigo))];
    const causas = [...new Set(datosProductos.map(p => p.causa))];
    const vendedores = [...new Set(datosProductos.map(p => p.vendedor))];
    const departamentos = [...new Set(datosProductos.map(p => p.departamento))];

    // Actualizar select de productos - MEJORADO
    selectProducto.innerHTML = '<option value="">Todos los productos</option>';
    productos.forEach(producto => {
        const productoData = datosProductos.find(p => p.codigo === producto);
        const descripcion = productoData?.descripcion || `Producto ${producto}`;
        // Mostrar código y descripción completa en el select
        selectProducto.innerHTML += `<option value="${producto}" title="${descripcion}">${producto} - ${descripcion}</option>`;
    });

    // Actualizar otros selects
    actualizarSelect(selectCausa, causas, 'Todas las causas');
    actualizarSelect(selectVendedor, vendedores, 'Todos los vendedores');
    actualizarSelect(selectDepartamento, departamentos, 'Todos los departamentos');
}



// Función auxiliar para actualizar selects
function actualizarSelect(selectElement, opciones, textoDefault) {
    selectElement.innerHTML = `<option value="">${textoDefault}</option>`;
    opciones.forEach(opcion => {
        selectElement.innerHTML += `<option value="${opcion}">${opcion}</option>`;
    });
}

// Función para actualizar gráficos de productos
function actualizarGraficosProductos() {
    // Gráfico de top productos
    const productosAgrupados = productosFiltrados.reduce((acc, producto) => {
        if (!acc[producto.codigo]) {
            acc[producto.codigo] = {
                codigo: producto.codigo,
                descripcion: producto.descripcion,
                total: 0
            };
        }
        acc[producto.codigo].total += producto.cantidad;
        return acc;
    }, {});

    const topProductos = Object.values(productosAgrupados)
        .sort((a, b) => b.total - a.total)
        .slice(0, 10);

    crearGraficoTopProductos(topProductos);

    // Gráfico por departamento
    const departamentos = productosFiltrados.reduce((acc, producto) => {
        acc[producto.departamento] = (acc[producto.departamento] || 0) + producto.cantidad;
        return acc;
    }, {});

    crearGraficoDepartamentosProductos(departamentos);
}

// Función para crear gráfico de top productos - MEJORADA
function crearGraficoTopProductos(productos) {
    const ctx = document.getElementById('topProductosChart');
    if (!ctx) {
        console.error('Canvas topProductosChart no encontrado');
        return;
    }

    const ctx2d = ctx.getContext('2d');
    
    if (topProductosChart) {
        topProductosChart.destroy();
    }

    // Crear etiquetas más descriptivas
    const labels = productos.map(p => {
        const descripcion = p.descripcion || `Producto ${p.codigo}`;
        // Mostrar solo código en las etiquetas del gráfico (para evitar sobrecarga)
        return p.codigo;
    });

    topProductosChart = new Chart(ctx2d, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Notas',
                data: productos.map(p => p.total),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Top 10 Productos con Más Notas'
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            // Mostrar descripción completa en el tooltip
                            const index = tooltipItems[0].dataIndex;
                            const producto = productos[index];
                            return `${producto.codigo} - ${producto.descripcion}`;
                        },
                        label: function(context) {
                            return `Cantidad: ${context.raw}`;
                        }
                    }
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
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });
}



// Función para crear gráfico de departamentos de productos
function crearGraficoDepartamentosProductos(departamentos) {
    const ctx = document.getElementById('departamentoProductosChart');
    if (!ctx) {
        console.error('Canvas departamentoProductosChart no encontrado');
        return;
    }

    const ctx2d = ctx.getContext('2d');
    
    if (departamentoProductosChart) {
        departamentoProductosChart.destroy();
    }

    const colores = generarColores(Object.keys(departamentos).length);

    departamentoProductosChart = new Chart(ctx2d, {
        type: 'pie',
        data: {
            labels: Object.keys(departamentos),
            datasets: [{
                data: Object.values(departamentos),
                backgroundColor: colores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Función para actualizar tabla de productos - MEJORADA
function actualizarTablaProductos() {
    const tbody = document.getElementById('tablaProductos');
    if (!tbody) {
        console.error('Tabla de productos no encontrada');
        return;
    }
    
    if (productosFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay datos disponibles</td></tr>';
        return;
    }

    // Ordenar por cantidad descendente
    const productosOrdenados = [...productosFiltrados].sort((a, b) => b.cantidad - a.cantidad);

    let html = '';
    productosOrdenados.forEach((producto, index) => {
        const montoFormateado = new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'VES'
        }).format(producto.monto || 0);
        
        // Mostrar descripción completa en la tabla
        const descripcionCompleta = producto.descripcion || `Producto ${producto.codigo}`;
        
        html += `
        <tr>
            <td><span class="badge bg-primary">${producto.codigo}</span></td>
            <td title="${descripcionCompleta}">${descripcionCompleta}</td>
            <td>${producto.causa}</td>
            <td>${producto.vendedor}</td>
            <td>${producto.departamento}</td>
            <td>${producto.cantidad}</td>
         
        </tr>
        `;
    });

    tbody.innerHTML = html;
}



// Función para filtrar productos
function filtrarProductos() {
    const producto = document.getElementById('selectProducto')?.value || '';
    const causa = document.getElementById('selectCausaProducto')?.value || '';
    const vendedor = document.getElementById('selectVendedorProducto')?.value || '';
    const departamento = document.getElementById('selectDepartamentoProducto')?.value || '';

    productosFiltrados = datosProductos.filter(p => {
        return (!producto || p.codigo === producto) &&
               (!causa || p.causa === causa) &&
               (!vendedor || p.vendedor === vendedor) &&
               (!departamento || p.departamento === departamento);
    });

    actualizarVistaProductos();
}

// Función auxiliar para generar colores
function generarColores(cantidad) {
    const colores = [];
    for (let i = 0; i < cantidad; i++) {
        const hue = (i * 360 / cantidad) % 360;
        colores.push(`hsl(${hue}, 70%, 60%)`);
    }
    return colores;
}



// ========== FUNCIÓN PARA LIMPIAR FILTROS DE PRODUCTOS ==========

function limpiarFiltrosProductos() {
    console.log('🧹 Limpiando filtros de productos...');
    
    // Restablecer todos los selects a su valor por defecto
    document.getElementById('selectProducto').value = '';
    document.getElementById('selectCausaProducto').value = '';
    document.getElementById('selectVendedorProducto').value = '';
    document.getElementById('selectDepartamentoProducto').value = '';
    
    // Restablecer los datos filtrados a todos los productos
    productosFiltrados = [...datosProductos];
    
    // Recalcular porcentajes
    const totalGeneral = productosFiltrados.reduce((sum, p) => sum + p.cantidad, 0);
    productosFiltrados.forEach(producto => {
        producto.porcentaje = totalGeneral > 0 ? ((producto.cantidad / totalGeneral) * 100).toFixed(1) : 0;
    });
    
    // Actualizar la vista
    actualizarVistaProductos();
    
    // Mostrar mensaje de confirmación
    mostrarMensaje('Filtros limpiados correctamente', 'success');
}

// ========== FUNCIÓN PARA MOSTRAR MENSAJES TEMPORALES ==========

function mostrarMensaje(mensaje, tipo = 'info') {
    // Crear elemento de mensaje
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    mensajeDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio de la pestaña de productos
    const productosTab = document.getElementById('productos');
    productosTab.insertBefore(mensajeDiv, productosTab.firstChild);
    
    // Auto-eliminar después de 3 segundos
    setTimeout(() => {
        if (mensajeDiv.parentNode) {
            mensajeDiv.remove();
        }
    }, 3000);
}


// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
    
    // Event listeners para filtros de productos
    const selectProducto = document.getElementById('selectProducto');
    const selectCausaProducto = document.getElementById('selectCausaProducto');
    const selectVendedorProducto = document.getElementById('selectVendedorProducto');
    const selectDepartamentoProducto = document.getElementById('selectDepartamentoProducto');
    
    if (selectProducto) selectProducto.addEventListener('change', filtrarProductos);
    if (selectCausaProducto) selectCausaProducto.addEventListener('change', filtrarProductos);
    if (selectVendedorProducto) selectVendedorProducto.addEventListener('change', filtrarProductos);
    if (selectDepartamentoProducto) selectDepartamentoProducto.addEventListener('change', filtrarProductos);

    // Cargar datos de productos cuando se active la pestaña - MEJORADO
    const productosTab = document.getElementById('productos-tab');
    if (productosTab) {
        productosTab.addEventListener('click', function() {
            console.log('🔄 Pestaña de productos activada');
            setTimeout(() => {
                if (datosProductos.length > 0) {
                    productosFiltrados = [...datosProductos];
                    actualizarVistaProductos();
                } else {
                    console.log('⏳ Esperando datos de productos...');
                    // Intentar cargar datos si no hay
                    cargarDatosProductos();
                }
            }, 100);
        });
    }
});