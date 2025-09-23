// Variables globales
        let evolucionChart, distribucionChart;
        let intervaloActualizacion;
        let datosActuales = {};
        let vistaActual = 'diaria';

        // Función segura para obtener elementos por ID
        function getElementSafe(id) {
            const element = document.getElementById(id);
            if (!element) {
                console.error(`Elemento con ID '${id}' no encontrado en el DOM`);
                return null;
            }
            return element;
        }

        // Función segura para establecer textContent
        function setTextSafe(elementId, text) {
            const element = getElementSafe(elementId);
            if (element) {
                element.textContent = text;
            }
        }

        // Función para cambiar entre vistas de tabla
        function cambiarVista(vista) {
            vistaActual = vista;
            
            // Actualizar botones de selector
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Ocultar todas las tablas
            document.querySelectorAll('.vista-tabla').forEach(tabla => {
                tabla.style.display = 'none';
            });
            
            // Mostrar la tabla seleccionada
            if (vista === 'diaria') {
                document.getElementById('seccionTablaDiaria').style.display = 'block';
            } else if (vista === 'mensual') {
                document.getElementById('seccionTablaMensual').style.display = 'block';
                // Si ya tenemos datos, actualizar la tabla mensual
                if (datosActuales.tabla) {
                    actualizarTablaMensual(datosActuales.tabla);
                }
            }
        }

        // Función para cargar datos desde el servidor
        function cargarDatos() {
            // Mostrar estado de carga
            document.getElementById('estadoCarga').style.display = 'flex';
            document.getElementById('metricasPrincipales').style.display = 'none';
            document.getElementById('barraProgresoContainer').style.display = 'none';
            document.getElementById('seccionGraficos').style.display = 'none';
            document.getElementById('selectorTabla').style.display = 'none';
            document.querySelectorAll('.vista-tabla').forEach(tabla => {
                tabla.style.display = 'none';
            });
            document.getElementById('mensajeError').style.display = 'none';

            // Obtener parámetros del formulario
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const agrupacion = document.getElementById('agrupacion').value;

            // Realizar solicitud al servidor
            fetch('obtener_datos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    tipoMetrica: agrupacion
                })
            })
            .then(response => {
                // Verificar si la respuesta es JSON válido
                return response.text().then(text => {
                    try {
                        const data = JSON.parse(text);
                        console.log('Respuesta del servidor:', data);
                        return data;
                    } catch (e) {
                        console.error('Error parseando JSON:', text);
                        throw new Error('El servidor devolvió una respuesta no válida. Verifica la consola para más detalles.');
                    }
                });
            })
            .then(data => {
                if (data && data.success) {
                    // Guardar datos para uso posterior
                    datosActuales = data;
                    
                    // Actualizar la interfaz con los datos recibidos
                    actualizarMetricas(data.metricas);
                    actualizarBarraProgreso(data.metricas);
                    actualizarGraficos(data.graficos);
                    actualizarTablaDiaria(data.tabla);
                    
                    // Mostrar secciones de contenido
                    document.getElementById('estadoCarga').style.display = 'none';
                    document.getElementById('metricasPrincipales').style.display = 'flex';
                    document.getElementById('barraProgresoContainer').style.display = 'block';
                    document.getElementById('seccionGraficos').style.display = 'flex';
                    document.getElementById('selectorTabla').style.display = 'block';
                    
                    // Mostrar la vista actual
                    if (vistaActual === 'diaria') {
                        document.getElementById('seccionTablaDiaria').style.display = 'block';
                    } else {
                        document.getElementById('seccionTablaMensual').style.display = 'block';
                        actualizarTablaMensual(data.tabla);
                    }
                } else {
                    throw new Error(data.error || 'Error desconocido del servidor');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('estadoCarga').style.display = 'none';
                document.getElementById('mensajeError').style.display = 'block';
                document.getElementById('textoError').textContent = error.message;
            });
        }

        
        // Función para actualizar las métricas con validación
function actualizarMetricas(metricas) {
    // Validar que las propiedades existan
    if (!metricas || typeof metricas !== 'object') {
        console.error('Datos de métricas no válidos:', metricas);
        return;
    }
    
    // Calcular el porcentaje de notas sobre facturas
    const totalFacturas = metricas.total_facturas || 0;
    const totalNotas = metricas.total_notas || 0;
    const porcentajeNotas = totalFacturas > 0 
        ? (totalNotas / totalFacturas * 100).toFixed(2) 
        : 0;
    
    // Actualizar elementos con valores por defecto si no existen
    setTextSafe('totalFacturas', totalFacturas.toLocaleString());
    setTextSafe('totalNotas', totalNotas.toLocaleString());
    setTextSafe('porcentajeNotas', `${porcentajeNotas}%`);
    
    // Mostrar el promedio histórico con el cálculo correcto
    const promedioHistorico = metricas.promedio_historico || 0;
    setTextSafe('promedioHistorico', `${promedioHistorico.toFixed(2)}%`);
    
    // Actualizar la descripción del promedio histórico
    const promedioDescElement = document.querySelector('#promedioHistorico').nextElementSibling;
    if (promedioDescElement && promedioDescElement.classList.contains('metric-desc')) {
        promedioDescElement.textContent = `Promedio de ${metricas.meses_historicos || 0} meses`;
    }
    
    setTextSafe('metaAutomatica', `${metricas.meta_automatica || 0}%`);
    
    // Usar progreso_meta con validación
    const progresoMeta = metricas.progreso_meta || 0;
    setTextSafe('progresoMeta', `${progresoMeta.toFixed(1)}%`);
    
    setTextSafe('textoProgreso', metricas.texto_progreso || 'Sin datos');
    setTextSafe('diasHabiles', `${metricas.dias_transcurridos || 0}/${metricas.dias_totales || 0}`);
    setTextSafe('porcentajeDias', `${metricas.porcentaje_dias || 0}% del período transcurrido`);
    
    // Establecer colores según el porcentaje
    const porcentajeElement = getElementSafe('tendenciaPorcentaje');
    const metaAutomatica = metricas.meta_automatica || 0;
    
    // Agrega esto para mostrar el estado de la meta:
    setTextSafe('textoProgreso', metricas.texto_progreso || 'Sin datos');

    // Agrega color según el estado
    const textoProgresoElement = getElementSafe('textoProgreso');
    if (textoProgresoElement) {
        if (porcentajeNotas <= metaAutomatica) {
            textoProgresoElement.className = 'positive-change';
        } else {
            textoProgresoElement.className = 'negative-change';
            textoProgresoElement.style.fontWeight = 'bold';
        }
    }

    if (porcentajeElement) {
        if (porcentajeNotas <= metaAutomatica) {
            porcentajeElement.className = 'positive-change';
            porcentajeElement.textContent = 'Bajo (Meta cumplida)';
        } else if (porcentajeNotas <= promedioHistorico) {
            porcentajeElement.className = 'warning-change';
            porcentajeElement.textContent = 'Medio (Atención requerida)';
        } else {
            porcentajeElement.className = 'negative-change';
            porcentajeElement.style.fontWeight = 'bold';
            porcentajeElement.textContent = 'Alto (Acción inmediata)';
        }
    }
    
    // Mostrar detalles del promedio histórico en consola para debug
    if (metricas.detalle_mensual) {
        console.log('Detalle mensual histórico:', metricas.detalle_mensual);
    }
}


    // Función para mostrar el detalle del promedio histórico
function mostrarDetalleHistorico(metricas) {
    if (metricas.detalle_mensual && metricas.detalle_mensual.length > 0) {
        console.group('Detalle del Promedio Histórico');
        console.log('Total de meses considerados:', metricas.meses_historicos);
        console.log('Promedio calculado:', metricas.promedio_historico.toFixed(2) + '%');
        console.log('Detalle por mes:');
        
        metricas.detalle_mensual.forEach(mes => {
            console.log(`- ${mes.mes}/${mes.ano}: ${mes.porcentaje_notas.toFixed(2)}%`);
        });
        
        console.groupEnd();
    }
}

// Llama a esta función después de actualizar las métricas
// mostrarDetalleHistorico(data.metricas);        
        
       // Función para actualizar la barra de progreso con dos colores
function actualizarBarraProgreso(metricas) {
    console.log('Actualizando barra de progreso:', metricas);
    
    const barraDentroMeta = document.getElementById('barraDentroMeta');
    const barraExcedente = document.getElementById('barraExcedente');
    const valorActualProgreso = document.getElementById('valorActualProgreso');
    const textoMeta = document.getElementById('textoMeta');
    const indicadorMeta = document.getElementById('indicadorMeta');
    const textoExcedente = document.getElementById('textoExcedente');
    const textoValorActual = document.getElementById('textoValorActual');
    const estadoMeta = document.getElementById('estadoMeta');
    const tooltipMeta = document.getElementById('tooltipMeta');
    
    if (!barraDentroMeta || !barraExcedente) {
        console.error('Elementos de la barra de progreso no encontrados');
        return;
    }
    
    // Validar que las propiedades existan
    const metaAutomatica = parseFloat(metricas.meta_automatica) || 0;
    const porcentajeActual = parseFloat(metricas.porcentaje_actual) || 0;
    
    console.log('Meta:', metaAutomatica, 'Actual:', porcentajeActual);
    
    // Actualizar texto
    textoMeta.textContent = `Meta: ${metaAutomatica.toFixed(2)}%`;
    tooltipMeta.textContent = `Meta: ${metaAutomatica.toFixed(2)}%`;
    valorActualProgreso.textContent = `${porcentajeActual.toFixed(2)}%`;
    
    // Posicionar el indicador de meta
    const posicionMeta = Math.min(100, metaAutomatica);
    indicadorMeta.style.left = `${posicionMeta}%`;
    tooltipMeta.style.left = `${posicionMeta}%`;
    
    // Resetear ambas barras
    barraDentroMeta.style.width = '0%';
    barraExcedente.style.width = '0%';
    barraExcedente.style.left = '0%';
    
    // Remover animaciones anteriores
    barraDentroMeta.classList.remove('pulse');
    barraExcedente.classList.remove('pulse');
    
    if (metaAutomatica === 0) {
        // Si no hay meta definida
        barraDentroMeta.style.width = `${Math.min(100, porcentajeActual)}%`;
        barraDentroMeta.style.backgroundColor = '#6c757d';
        indicadorMeta.style.display = 'none';
        tooltipMeta.style.display = 'none';
        textoValorActual.textContent = `${porcentajeActual.toFixed(2)}% (Meta no definida)`;
        textoValorActual.className = 'progress-current-value';
        estadoMeta.textContent = 'Meta no configurada';
        estadoMeta.className = 'meta-status warning';
        return;
    } else {
        indicadorMeta.style.display = 'block';
        tooltipMeta.style.display = 'block';
        barraDentroMeta.style.backgroundColor = '';
    }
    
    // Calcular los anchos de cada parte
    if (porcentajeActual <= metaAutomatica) {
        // Todo está dentro de la meta
        barraDentroMeta.style.width = `${porcentajeActual}%`;
        barraExcedente.style.width = '0%';
        
        const disponible = metaAutomatica - porcentajeActual;
        textoValorActual.textContent = `${porcentajeActual.toFixed(2)}% (Dentro de la meta)`;
        textoValorActual.className = 'progress-current-value within';
        estadoMeta.textContent = '✅ Meta en buen estado';
        estadoMeta.className = 'meta-status achieved';
        barraDentroMeta.classList.add('pulse');
        
    } else {
        // Hay excedente
        const anchoDentroMeta = metaAutomatica;
        const anchoExcedente = Math.min(100, porcentajeActual) - metaAutomatica;
        
        barraDentroMeta.style.width = `${anchoDentroMeta}%`;
        barraExcedente.style.width = `${anchoExcedente}%`;
        barraExcedente.style.left = `${anchoDentroMeta}%`;
        
        const excedente = porcentajeActual - metaAutomatica;
        textoValorActual.textContent = `${porcentajeActual.toFixed(2)}% (+${excedente.toFixed(2)}% sobre meta)`;
        textoValorActual.className = 'progress-current-value exceeded';
        estadoMeta.textContent = '⚠️ Meta excedida - Atención requerida';
        estadoMeta.className = 'meta-status exceeded';
        barraExcedente.classList.add('pulse');
    }
    
    // Mostrar el contenedor
    document.getElementById('barraProgresoContainer').style.display = 'block';
}

        // Función para actualizar los gráficos
        function actualizarGraficos(graficos) {
            // Destruir gráficos existentes si los hay
            if (evolucionChart) evolucionChart.destroy();
            if (distribucionChart) distribucionChart.destroy();
            
            // Gráfico de evolución
            const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');
            evolucionChart = new Chart(evolucionCtx, {
                type: 'line',
                data: {
                    labels: graficos.evolution.labels,
                    datasets: [
                        {
                            label: 'Facturas',
                            data: graficos.evolution.facturas,
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Notas de Crédito',
                            data: graficos.evolution.notas,
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Evolución de Facturas vs Notas de Crédito'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gráfico de distribución
            const distribucionCtx = document.getElementById('distribucionChart').getContext('2d');
            distribucionChart = new Chart(distribucionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Facturas', 'Notas de Crédito'],
                    datasets: [{
                        data: [graficos.distribution.facturas, graficos.distribution.notas],
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
                                    return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Función para actualizar la tabla diaria
        function actualizarTablaDiaria(datos) {
            const tablaBody = document.getElementById('tablaDatosDiaria');
            tablaBody.innerHTML = '';
            
            if (!datos || datos.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="8" class="text-center">No hay datos para mostrar</td></tr>';
                return;
            }
            
            datos.forEach(item => {
                // Calcular el porcentaje de notas/facturas para cada fila
                const porcentajeNotas = item.Total_Facturas > 0 
                    ? ((item.Total_Notas_Credito / item.Total_Facturas) * 100).toFixed(2) 
                    : 0;
                
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${item.Fecha}</td>
                    <td>${item.ANNO}</td>
                    <td>${item.MES}</td>
                    <td>${item.DIA}</td>
                    <td>${item.Total_Facturas.toLocaleString()}</td>
                    <td>${item.Total_Notas_Credito.toLocaleString()}</td>
                    <td>${porcentajeNotas}%</td>
                    <td>${item.Total_Documentos.toLocaleString()}</td>
                `;
                tablaBody.appendChild(fila);
            });
        }

        // Función para actualizar la tabla mensual
        function actualizarTablaMensual(datosDiarios) {
            const tablaBody = document.getElementById('tablaDatosMensual');
            tablaBody.innerHTML = '';
            
            if (!datosDiarios || datosDiarios.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="8" class="text-center">No hay datos para mostrar</td></tr>';
                return;
            }
            
            // Agrupar datos por mes
            const datosPorMes = {};
            
            datosDiarios.forEach(item => {
                const clave = `${item.ANNO}-${item.MES}`;
                
                if (!datosPorMes[clave]) {
                    datosPorMes[clave] = {
                        ANNO: item.ANNO,
                        MES: item.MES,
                        Total_Facturas: 0,
                        Total_Notas_Credito: 0,
                        Total_Documentos: 0,
                        Dias: 0
                    };
                }
                
                datosPorMes[clave].Total_Facturas += item.Total_Facturas;
                datosPorMes[clave].Total_Notas_Credito += item.Total_Notas_Credito;
                datosPorMes[clave].Total_Documentos += item.Total_Documentos;
                datosPorMes[clave].Dias += 1;
            });
            
            // Convertir el objeto a array y ordenar por año y mes
            const datosMensuales = Object.values(datosPorMes).sort((a, b) => {
                if (a.ANNO !== b.ANNO) return a.ANNO - b.ANNO;
                return a.MES - b.MES;
            });
            
            // Llenar la tabla con los datos mensuales
            datosMensuales.forEach(item => {
                // Calcular el porcentaje de notas/facturas
                const porcentajeNotas = item.Total_Facturas > 0 
                    ? ((item.Total_Notas_Credito / item.Total_Facturas) * 100).toFixed(2) 
                    : 0;
                
                // Calcular promedios diarios
                const promedioDiarioFacturas = (item.Total_Facturas / item.Dias).toFixed(1);
                const promedioDiarioNotas = (item.Total_Notas_Credito / item.Dias).toFixed(1);
                
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${item.ANNO}</td>
                    <td>${obtenerNombreMes(item.MES)}</td>
                    <td>${item.Total_Facturas.toLocaleString()}</td>
                    <td>${item.Total_Notas_Credito.toLocaleString()}</td>
                    <td>${porcentajeNotas}%</td>
                    <td>${item.Total_Documentos.toLocaleString()}</td>
                    <td>${promedioDiarioFacturas}</td>
                    <td>${promedioDiarioNotas}</td>
                `;
                tablaBody.appendChild(fila);
            });
        }

        // Función auxiliar para obtener el nombre del mes
        function obtenerNombreMes(numeroMes) {
            const meses = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ];
            return meses[numeroMes - 1] || 'Desconocido';
        }

        // Función para exportar datos
        function exportarDatos() {
            alert('Funcionalidad de exportación: Esto descargaría un archivo CSV/Excel con los datos actuales');
        }

        // Configurar auto-actualización
        document.getElementById('autoActualizar').addEventListener('change', function() {
            if (this.checked) {
                intervaloActualizacion = setInterval(cargarDatos, 300000); // 5 minutos
            } else {
                clearInterval(intervaloActualizacion);
            }
        });

        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarDatos();
        });


        // Verificar que los elementos de la barra de progreso existan
function verificarElementosBarra() {
    const elementos = [
        'barraDentroMeta',
        'barraExcedente',
        'valorActualProgreso',
        'textoMeta',
        'indicadorMeta',
        'textoExcedente'
    ];
    
    elementos.forEach(id => {
        const elemento = document.getElementById(id);
        console.log(`${id}:`, elemento ? 'EXISTE' : 'NO EXISTE');
        if (elemento) {
            console.log('  Estilos:', window.getComputedStyle(elemento));
        }
    });
}

// Ejecutar después de que la página cargue
setTimeout(verificarElementosBarra, 1000);