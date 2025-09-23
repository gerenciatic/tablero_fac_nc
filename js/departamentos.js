// ========== FUNCIONES PARA DEPARTAMENTOS ==========

// Función para cargar datos de departamentos
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

// Función para actualizar el top departamentos chart
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

// Función para actualizar el resumen de departamentos
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

// Función para actualizar tabla de departamentos
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