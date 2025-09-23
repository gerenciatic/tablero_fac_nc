// SERVICIO CENTRALIZADO PARA DATOS
class ApiService {
    constructor() {
        this.baseUrl = 'api/';
        this.cache = new Map();
    }

    async obtenerDatosDashboard(filtros = {}) {
        const cacheKey = JSON.stringify(filtros);
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            // Simular llamada API - REEMPLAZAR con tu API real
            const datos = await this.simularApiCall(filtros);
            this.cache.set(cacheKey, datos);
            return datos;
        } catch (error) {
            console.error('Error obteniendo datos:', error);
            throw error;
        }
    }

    async simularApiCall(filtros) {
        // Datos de ejemplo - REEMPLAZAR con tu API real
        return {
            facturas: [
                {
                    numero: 'F-001',
                    fecha: '2023-11-15',
                    vendedor: 'Juan Pérez',
                    cliente: 'Cliente A',
                    total: 1500.00,
                    estado: 'ACTIVA'
                }
            ],
            notasCredito: [
                {
                    numero: 'NC-001',
                    fecha: '2023-11-10',
                    vendedor: 'María García',
                    cliente: 'Cliente B',
                    total: 250.00, // CABECERA
                    estado: 'ACTIVA',
                    detalles: [ // DETALLE
                        {
                            producto: 'Producto X',
                            cantidad: 2,
                            precio_unitario: 100.00,
                            subtotal: 200.00
                        },
                        {
                            producto: 'Producto Y',
                            cantidad: 1,
                            precio_unitario: 50.00,
                            subtotal: 50.00
                        }
                    ]
                }
            ],
            vendedores: [
                { id: 1, nombre: 'Juan Pérez', activo: true },
                { id: 2, nombre: 'María García', activo: true }
            ],
            departamentos: [
                { nombre: 'Electrónica', ventas: 50000 },
                { nombre: 'Hogar', ventas: 35000 }
            ]
        };
    }

    // Métodos específicos para cada tipo de dato
    async obtenerVendedores() {
        const datos = await this.obtenerDatosDashboard();
        return datos.vendedores || [];
    }

    async obtenerDepartamentos() {
        const datos = await this.obtenerDatosDashboard();
        return datos.departamentos || [];
    }
}

window.ApiService = ApiService;