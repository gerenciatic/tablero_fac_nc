// ========== FUNCIONES PARA ANÁLISIS DE VENDEDOR ==========

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

// Función para mostrar análisis del vendedor
function mostrarAnalisisVendedor(codigoVendedor, causasData, vendedoresData, totalNotas, totalLineasDetalle) {
    const vendedor = vendedoresData.find(v => v.codigo === codigoVendedor);
    if (!vendedor) return;

    document.getElementById('vendedorTotalNotas').textContent = vendedor.cantidad;
    
    const eficienciaVendedor = totalLineasDetalle > 0 ? Math.min(100, (vendedor.cantidad / totalLineasDetalle * 100)).toFixed(1) : 0;
    const lineasPorNotaVendedor = vendedor.cantidad > 0 ? (vendedor.cantidad / vendedor.cantidad).toFixed(1) : 0;
    
    document.getElementById('vendedorEficiencia').textContent = `${eficienciaVendedor}%`;
    document.getElementById('vendedorCausaPrincipal').textContent = vendedor.causaPrincipal || 'N/A';
    document.getElementById('vendedorLineasNota').textContent = lineasPorNotaVendedor;

    const causasVendedor = causasData.filter(causa => true); // Simulación

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

// Función para actualizar gráfico de causas por vendedor
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

// Función para actualizar top causas por vendedor
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

// Función para actualizar tabla de causas por vendedor
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