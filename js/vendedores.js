// ========== FUNCIONES PARA VENDEDORES ==========

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

// Función para actualizar top vendedores chart
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

// Función para actualizar el ranking de vendedores
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

// Función para actualizar tabla de vendedores
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