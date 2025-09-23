// Variables globales para los gráficos
let evolucionMensualChart, distribucionTipoChart, causasChart, topCausasChart;
let vendedoresChart, topVendedoresChart, causasVendedorChart;
let departamentosChart, topDepartamentosChart;

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

// Función principal cargarDatos - CORREGIDA
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
            const diasHabiles = calcularDiasHabilesConFeriados(fechaInicio, fechaFin);
            
            // CORRECCIÓN: Mostrar correctamente los totales
            document.getElementById('totalNotas').textContent = data.totalNotasCabecero.toLocaleString();
            document.getElementById('totalLineasDetalle').textContent = data.totalLineasDetalle.toLocaleString();
            document.getElementById('diasHabiles').textContent = diasHabiles;
            document.getElementById('causaPrincipal').textContent = data.causaPrincipalDetalle;
            
            // CORRECCIÓN: Cálculos corregidos
            const notasPorDia = diasHabiles > 0 ? (data.totalNotasCabecero / diasHabiles) : 0;
            document.getElementById('notasPorDia').textContent = notasPorDia.toFixed(1);
            
            const lineasPorNota = data.totalNotasCabecero > 0 ? (data.totalLineasDetalle / data.totalNotasCabecero) : 0;
            const eficiencia = data.totalNotasCabecero > 0 ? Math.min(100, (100 - (data.totalNotasCabecero / data.totalLineasDetalle * 100)).toFixed(1)) : 0;
            
            document.getElementById('lineasPorNota').textContent = lineasPorNota.toFixed(1);
            document.getElementById('eficiencia').textContent = `${eficiencia}%`;
            
            // Actualizar departamentos
            document.getElementById('totalDepartamentos').textContent = data.departamentosData ? data.departamentosData.length : 0;
            
            actualizarGraficos(data);
            
            // CORRECCIÓN: Pasar totalLineasDetalle en lugar de totalNotasCabecero
            actualizarTablaCausas(data.causasDetalleData, data.totalLineasDetalle);
            actualizarTablaVendedores(data.vendedoresData, data.totalLineasDetalle);
            prepararAnalisisVendedor(data.vendedoresData, data.causasDetalleData, data.totalNotasCabecero, data.totalLineasDetalle);
            
            // CORRECCIÓN: Procesar datos de departamentos con totalLineasDetalle
            if (data.departamentosData) {
                cargarDatosDepartamentos(data.departamentosData, data.totalLineasDetalle);
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

// Fin Función para cargar datos desde el servidor


// Función para actualizar todos los gráficos - CORREGIDA
function actualizarGraficos(data) {
    actualizarEvolucionMensual(data.evolucionMensual);
    actualizarDistribucionTipo(data.distribucionTipo);
    actualizarGraficoCausas(data.causasDetalleData);
    actualizarTopCausas(data.causasDetalleData, data.totalLineasDetalle); // Pasar totalLineasDetalle
    actualizarResumenCausas(data.causasDetalleData, data.totalLineasDetalle); // Pasar totalLineasDetalle
    actualizarGraficoVendedores(data.vendedoresData);
    actualizarTopVendedores(data.vendedoresData, data.totalLineasDetalle); // Pasar totalLineasDetalle
    actualizarRankingVendedores(data.vendedoresData, data.totalLineasDetalle); // Pasar totalLineasDetalle
}

// ========== FUNCIONES PARA DEPARTAMENTOS ==========

// Función para cargar datos de departamentos - CORREGIDA
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


// Función para actualizar el gráfico de departamentos
function actualizarGraficoDepartamentos(datosDepartamentos) {
    const ctx = document.getElementById('departamentosChart').getContext('2d');
    
    if (departamentosChart) {
        departamentosChart.destroy();
    }
    
    const sortedData = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 15);
    
    departamentosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(item => item.descripcion || item.codigo),
            datasets: [{
                label: 'Cantidad de Notas',
                data: sortedData.map(item => item.cantidad),
                backgroundColor: sortedData.map((item, index) => {
                    return `hsl(${index * 25}, 70%, 60%)`;
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
                    text: 'Top 15 Departamentos'
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

// Función para actualizar el top departamentos chart - CORREGIDA
function actualizarTopDepartamentosChart(datosDepartamentos, totalLineasDetalle) {
    const ctx = document.getElementById('topDepartamentosChart').getContext('2d');
    
    if (topDepartamentosChart) {
        topDepartamentosChart.destroy();
    }
    
    const top5 = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
    topDepartamentosChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: top5.map(item => item.descripcion || item.codigo),
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
                    text: 'Top 5 Departamentos'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = totalLineasDetalle; // Usar el total real
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}


// Función para actualizar el resumen de departamentos - CORREGIDA
function actualizarResumenDepartamentos(datosDepartamentos, totalLineasDetalle) {
    const resumenDepartamentos = document.getElementById('resumenDepartamentos');
    
    if (!datosDepartamentos || datosDepartamentos.length === 0) {
        resumenDepartamentos.innerHTML = '<p class="text-center">No hay datos de departamentos</p>';
        return;
    }
    
    const top5 = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    const departamentoPrincipal = top5[0] || { descripcion: '-', cantidad: 0 };
    const porcentajePrincipal = totalLineasDetalle > 0 ? ((departamentoPrincipal.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
    
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
    
    resumenDepartamentos.innerHTML = html;
}


// Función para actualizar tabla de departamentos - CORREGIDA
function actualizarTablaDepartamentos(datosDepartamentos, totalLineasDetalle) {
    const tablaBody = document.getElementById('tablaDepartamentos');
    
    if (!datosDepartamentos || datosDepartamentos.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="6" class="text-center">No hay datos de departamentos disponibles</td></tr>';
        return;
    }
    
    const sortedData = [...datosDepartamentos].sort((a, b) => b.cantidad - a.cantidad);
    
    let html = '';
    sortedData.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(2) : 0;
        const causaPrincipal = item.causaPrincipal || '-';
        
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
        </tr>
        `;
    });
    
    tablaBody.innerHTML = html;
}

 
// ========== FUNCIONES EXISTENTES ==========

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
            labels: ['Notas de Crédito'],
            datasets: [{
                data: [datos.ncCount],
                backgroundColor: ['#3498db']
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
    
    const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 15);
    
    causasChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(item => item.causa),
            datasets: [{
                label: 'Cantidad de Notas',
                data: sortedData.map(item => item.cantidad),
                backgroundColor: sortedData.map((item, index) => {
                    return `hsl(${index * 25}, 70%, 60%)`;
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
                    text: 'Top 15 Causas (Detalle)'
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

// Función para actualizar top causas chart - CORREGIDA
function actualizarTopCausas(datos, totalLineasDetalle) {
    const ctx = document.getElementById('topCausasChart').getContext('2d');
    
    if (topCausasChart) {
        topCausasChart.destroy();
    }
    
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
                            const total = totalLineasDetalle; // Usar el total real
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}



// Función para actualizar el resumen de causas - CORREGIDA
function actualizarResumenCausas(datos, totalLineasDetalle) {
    const resumenCausas = document.getElementById('resumenCausas');
    const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
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
    
    resumenCausas.innerHTML = html;
}

// Función para actualizar la tabla de causas - CORREGIDA
function actualizarTablaCausas(datos, totalLineasDetalle) {
    const tablaBody = document.getElementById('tablaCausas');
    
    const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
    
    let html = '';
    sortedData.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
        
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

// Función para actualizar el gráfico de vendedores
function actualizarGraficoVendedores(datos) {
    const ctx = document.getElementById('vendedoresChart').getContext('2d');
    
    if (vendedoresChart) {
        vendedoresChart.destroy();
    }
    
    const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 15);
    
    vendedoresChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(item => item.nombre),
            datasets: [{
                label: 'Cantidad de Notas',
                data: sortedData.map(item => item.cantidad),
                backgroundColor: sortedData.map((item, index) => {
                    return `hsl(${index * 25}, 70%, 60%)`;
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
                    text: 'Top 15 Vendedores'
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

// Función para actualizar top vendedores chart - CORREGIDA
function actualizarTopVendedores(datos, totalLineasDetalle) {
    const ctx = document.getElementById('topVendedoresChart').getContext('2d');
    
    if (topVendedoresChart) {
        topVendedoresChart.destroy();
    }
    
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
                            const total = totalLineasDetalle; // Usar el total real
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}



// Función para actualizar el ranking de vendedores - CORREGIDA
function actualizarRankingVendedores(datos, totalLineasDetalle) {
    const rankingVendedores = document.getElementById('rankingVendedores');
    const top5 = [...datos].sort((a, b) => b.cantidad - a.cantidad).slice(0, 5);
    
    let html = '<h6>Top 5 Vendedores:</h6>';
    top5.forEach((item, index) => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
        html += `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="badge bg-primary me-2">${index + 1}</span>
                <span style="font-size: 0.9em;">${item.nombre}</span>
            </div>
            <div>
                <strong>${item.cantidad}</strong>
                <small class="text-muted ms-2">${porcentaje}%</small>
            </div>
        </div>
        `;
    });
    
    rankingVendedores.innerHTML = html;
}

// Función para actualizar tabla de vendedores - CORREGIDA
function actualizarTablaVendedores(datos, totalLineasDetalle) {
    const tablaBody = document.getElementById('tablaVendedores');
    
    const sortedData = [...datos].sort((a, b) => b.cantidad - a.cantidad);
    
    let html = '';
    sortedData.forEach(item => {
        const porcentaje = totalLineasDetalle > 0 ? ((item.cantidad / totalLineasDetalle) * 100).toFixed(1) : 0;
        
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

// Función para preparar el análisis por vendedor
function prepararAnalisisVendedor(vendedoresData, causasData, totalNotas, totalLineasDetalle) {
    const selectVendedor = document.getElementById('selectVendedor');
    selectVendedor.innerHTML = '<option value="">Seleccione un vendedor</option>';
    
    vendedoresData.sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(vendedor => {
        const option = document.createElement('option');
        option.value = vendedor.codigo;
        option.textContent = `${vendedor.nombre} (${vendedor.codigo}) - ${vendedor.cantidad} notas`;
        selectVendedor.appendChild(option);
    });

    selectVendedor.addEventListener('change', function() {
        const codigoVendedor = this.value;
        if (codigoVendedor) {
            mostrarAnalisisVendedor(codigoVendedor, causasData, vendedoresData, totalNotas, totalLineasDetalle);
        } else {
            ocultarAnalisisVendedor();
        }
    });
}

// Función para mostrar análisis del vendedor - CORREGIDA
function mostrarAnalisisVendedor(codigoVendedor, causasData, vendedoresData, totalNotas, totalLineasDetalle) {
    const vendedor = vendedoresData.find(v => v.codigo === codigoVendedor);
    if (!vendedor) return;

    document.getElementById('vendedorTotalNotas').textContent = vendedor.cantidad;
    
    // CORRECCIÓN: Cálculos corregidos para vendedor
    const eficienciaVendedor = totalLineasDetalle > 0 ? Math.min(100, (vendedor.cantidad / totalLineasDetalle * 100)).toFixed(1) : 0;
    const lineasPorNotaVendedor = vendedor.cantidad > 0 ? (vendedor.cantidad / vendedor.cantidad).toFixed(1) : 0; // Esto siempre será 1
    
    document.getElementById('vendedorEficiencia').textContent = `${eficienciaVendedor}%`;
    document.getElementById('vendedorCausaPrincipal').textContent = vendedor.causaPrincipal || 'N/A';
    document.getElementById('vendedorLineasNota').textContent = lineasPorNotaVendedor;

    // Filtrar causas reales por vendedor (simulación mejorada)
    const causasVendedor = causasData.filter(causa => {
        // En producción, esto debería venir filtrado del backend
        return true; // Mostrar todas por ahora
    });

    actualizarGraficoCausasVendedor(causasVendedor, vendedor.nombre, vendedor.cantidad);
    actualizarTopCausasVendedor(causasVendedor, vendedor.cantidad);
    actualizarTablaCausasVendedor(causasVendedor, vendedor.cantidad);

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
    
    if (causasVendedorChart) {
        causasVendedorChart.destroy();
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
                            const total = totalVendedor; // Usar total del vendedor
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
    
    topCausasContainer.innerHTML = html;
}


// Función para actualizar tabla de causas por vendedor - CORREGIDA
function actualizarTablaCausasVendedor(causasData, totalVendedor) {
    const tablaBody = document.getElementById('tablaDetalleCausasVendedor');
    
    const sortedData = [...causasData].sort((a, b) => b.cantidad - a.cantidad);
    
    let html = '';
    sortedData.forEach((item, index) => {
        const porcentaje = totalVendedor > 0 ? ((item.cantidad / totalVendedor) * 100).toFixed(1) : 0;
        
        html += `
        <tr>
            <td>${item.causa}</td>
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


// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
});