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

// Función principal cargarDatos
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
            
            // Mostrar totales
            document.getElementById('totalNotas').textContent = data.totalNotasCabecero.toLocaleString();
            document.getElementById('totalLineasDetalle').textContent = data.totalLineasDetalle.toLocaleString();
            document.getElementById('diasHabiles').textContent = diasHabiles;
            document.getElementById('causaPrincipal').textContent = data.causaPrincipalDetalle;
            
            // Cálculos
            const notasPorDia = diasHabiles > 0 ? (data.totalNotasCabecero / diasHabiles) : 0;
            document.getElementById('notasPorDia').textContent = notasPorDia.toFixed(1);
            
            const lineasPorNota = data.totalNotasCabecero > 0 ? (data.totalLineasDetalle / data.totalNotasCabecero) : 0;
            const eficiencia = data.totalNotasCabecero > 0 ? Math.min(100, (100 - (data.totalNotasCabecero / data.totalLineasDetalle * 100)).toFixed(1)) : 0;
            
            document.getElementById('lineasPorNota').textContent = lineasPorNota.toFixed(1);
            document.getElementById('eficiencia').textContent = `${eficiencia}%`;
            
            // Actualizar departamentos
            document.getElementById('totalDepartamentos').textContent = data.departamentosData ? data.departamentosData.length : 0;
            
            // Actualizar todas las pestañas
            actualizarGraficos(data);
            actualizarTablaCausas(data.causasDetalleData, data.totalLineasDetalle);
            actualizarTablaVendedores(data.vendedoresData, data.totalLineasDetalle);
            prepararAnalisisVendedor(data.vendedoresData, data.causasDetalleData, data.totalNotasCabecero, data.totalLineasDetalle);
            
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

// Función para actualizar todos los gráficos
function actualizarGraficos(data) {
    actualizarEvolucionMensual(data.evolucionMensual);
    actualizarDistribucionTipo(data.distribucionTipo);
    actualizarGraficoCausas(data.causasDetalleData);
    actualizarTopCausas(data.causasDetalleData, data.totalLineasDetalle);
    actualizarResumenCausas(data.causasDetalleData, data.totalLineasDetalle);
    actualizarGraficoVendedores(data.vendedoresData);
    actualizarTopVendedores(data.vendedoresData, data.totalLineasDetalle);
    actualizarRankingVendedores(data.vendedoresData, data.totalLineasDetalle);
}

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
});