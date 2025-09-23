// ========== FUNCIONES PARA CAUSAS ==========

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

// Función para actualizar top causas chart
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

// Función para actualizar el resumen de causas
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

// Función para actualizar tabla de causas
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