// ========== FUNCIONES PARA GRÁFICOS GENERALES ==========

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