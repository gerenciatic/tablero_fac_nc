<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual del Sistema - Proyecto de Gestión (SQL Server)</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #27ae60;
            --sqlserver-color: #cc2927;
            --whatsapp-color: #25D366;
            --email-color: #ea4335;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .db-badge {
            background-color: var(--sqlserver-color);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: var(--light-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .submenu {
            list-style: none;
            padding-left: 20px;
            display: none;
        }
        
        .submenu.active {
            display: block;
        }
        
        .submenu a {
            padding: 10px 20px;
            font-size: 0.9rem;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .section {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .section.active {
            display: block;
        }
        
        .section h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .section h3 {
            color: var(--dark-color);
            margin: 25px 0 15px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .code-block {
            background-color: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            position: relative;
        }
        
        .code-header {
            background-color: var(--sqlserver-color);
            color: white;
            padding: 5px 10px;
            margin: -15px -15px 10px -15px;
            border-radius: 5px 5px 0 0;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
        }
        
        .copy-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .file-structure {
            background-color: #f8f9fa;
            border-left: 4px solid var(--secondary-color);
            padding: 15px;
            margin: 15px 0;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .toggle-sidebar {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            z-index: 101;
            cursor: pointer;
        }
        
        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin: 15px 0;
        }
        
        .warning {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 10px 15px;
            margin: 15px 0;
        }
        
        /* WhatsApp Button Styles */
        .whatsapp-button {
            display: inline-flex;
            align-items: center;
            background-color: var(--whatsapp-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            margin-left: 10px;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .whatsapp-button:hover {
            background-color: #128C7E;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .email-button {
            display: inline-flex;
            align-items: center;
            background-color: var(--email-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            margin-left: 10px;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .email-button:hover {
            background-color: #c23321;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .whatsapp-button img, .email-button img {
            width: 20px;
            height: 20px;
            margin-right: 8px;
        }
        
        .contact-info {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            .contact-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .whatsapp-button, .email-button {
                margin-left: 0;
                margin-top: 5px;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .search-box {
            margin: 15px 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: none;
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .search-box input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .highlight {
            background-color: yellow;
            color: black;
        }
        
        .db-diagram {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .table-schema {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .table-schema th, .table-schema td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .table-schema th {
            background-color: #f2f2f2;
        }
        
        .floating-whatsapp {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .floating-whatsapp a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background-color: var(--whatsapp-color);
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            animation: pulse 2s infinite;
        }
        
        .floating-whatsapp img {
            width: 35px;
            height: 35px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .contact-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .schedule-badge {
            display: inline-block;
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-left: 10px;
            border: 1px solid #bbdefb;
        }
    </style>
</head>
<body>
    <button class="toggle-sidebar" id="toggleSidebar">☰</button>
    
    <!-- Botón flotante de WhatsApp -->
    <div class="floating-whatsapp">
        <a aria-label="Chat on WhatsApp" href="https://wa.me/584120326845" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="white">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
            </svg>
        </a>
    </div>
    
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Manual del Sistema <span class="db-badge">SQL Server</span></h2>
                <p>Proyecto de Gestión</p>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar en el manual...">
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="#introduccion" class="nav-link active">Introducción</a></li>
                <li><a href="#estructura" class="nav-link">Estructura del Proyecto</a></li>
                <li>
                    <a href="#instalacion" class="nav-link">Instalación y Configuración</a>
                    <ul class="submenu">
                        <li><a href="#requisitos" class="nav-link">Requisitos del Sistema</a></li>
                        <li><a href="#configuracion" class="nav-link">Configuración Inicial</a></li>
                    </ul>
                </li>
                <li><a href="#basedatos" class="nav-link">Base de Datos SQL Server</a></li>
                <li><a href="#manual-usuario" class="nav-link">Manual de Usuario</a></li>
                <li><a href="#manual-desarrollo" class="nav-link">Manual de Desarrollo</a></li>
                <li><a href="#api" class="nav-link">API y Funciones</a></li>
                <li><a href="#soporte" class="nav-link">Soporte y Contacto</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Introducción -->
            <section id="introduccion" class="section active">
                <h2>Introducción al Sistema</h2>
                <div class="card">
                    <p>Bienvenido al manual del sistema de gestión. Este documento proporciona información completa sobre el uso y desarrollo del proyecto.</p>
                    
                    <h3>Propósito del Sistema</h3>
                    <p>El sistema está diseñado para gestionar [describir propósito del sistema]. Proporciona una interfaz intuitiva para usuarios y una estructura sólida para desarrolladores.</p>
                    
                    <h3>Características Principales</h3>
                    <ul>
                        <li>Autenticación de usuarios segura</li>
                        <li>Tablero principal con métricas</li>
                        <li>Generación de reportes</li>
                        <li>Interfaz responsive</li>
                        <li>Base de datos <strong>SQL Server</strong></li>
                    </ul>
                    
                    <div class="note">
                        <strong>Nota:</strong> Este sistema utiliza Microsoft SQL Server como motor de base de datos. Asegúrese de tener los drivers y permisos necesarios.
                    </div>
                </div>
            </section>
            
            <!-- Estructura del Proyecto -->
            <section id="estructura" class="section">
                <h2>Estructura del Proyecto</h2>
                <div class="card">
                    <p>El proyecto sigue una estructura organizada que facilita el mantenimiento y la escalabilidad.</p>
                    
                    <h3>Archivos Principales</h3>
                    <div class="file-structure">
                        <p><strong>index.html</strong> - Página principal del sistema</p>
                        <p><strong>login.php</strong> - Sistema de autenticación de usuarios</p>
                        <p><strong>conexion.php</strong> - Manejo de conexión a SQL Server</p>
                        <p><strong>tablero.php</strong> - Tablero principal después del login</p>
                    </div>
                    
                    <h3>Directorios</h3>
                    <div class="file-structure">
                        <p><strong>reportes/</strong> - Contiene todos los archivos relacionados con la generación de reportes</p>
                        <p><strong>css/</strong> - Hojas de estilo para el diseño del sistema</p>
                        <p><strong>js/</strong> - Scripts JavaScript para funcionalidades del frontend</p>
                        <p><strong>img/</strong> - Imágenes y recursos gráficos del proyecto</p>
                        <p><strong>includes/</strong> - Archivos PHP reutilizables (opcional)</p>
                    </div>
                </div>
            </section>
            
            <!-- Instalación y Configuración -->
            <section id="instalacion" class="section">
                <h2>Instalación y Configuración</h2>
                
                <div id="requisitos" class="card">
                    <h3>Requisitos del Sistema</h3>
                    <p>Para ejecutar correctamente el sistema, se necesitan los siguientes componentes:</p>
                    <ul>
                        <li>Servidor web (Apache, Nginx, IIS)</li>
                        <li>PHP 7.4 o superior con extensión SQLSRV o PDO_SQLSRV</li>
                        <li>SQL Server 2012 o superior</li>
                        <li>SQL Server Management Studio (recomendado)</li>
                        <li>Navegador web moderno (Chrome, Firefox, Safari, Edge)</li>
                    </ul>
                    
                    <div class="warning">
                        <strong>Importante:</strong> Asegúrese de que la extensión de SQL Server para PHP esté instalada y habilitada en su servidor.
                    </div>
                </div>
                
                <div id="configuracion" class="card">
                    <h3>Configuración Inicial</h3>
                    <p>Siga estos pasos para configurar el sistema:</p>
                    <ol>
                        <li>Descomprima los archivos en el directorio del servidor web</li>
                        <li>Cree la base de datos en SQL Server</li>
                        <li>Ejecute el script SQL proporcionado para crear las tablas</li>
                        <li>Configure los parámetros de conexión en <code>conexion.php</code></li>
                        <li>Verifique la conexión a la base de datos</li>
                        <li>Acceda al sistema mediante el navegador</li>
                    </ol>
                    
                    <h4>Configuración de conexión a SQL Server</h4>
                    <div class="code-block">
                        <div class="code-header">
                            conexion.php - Configuración SQL Server
                            <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                        </div>
&lt;?php
$serverName = "localhost"; // o la IP del servidor SQL
$connectionInfo = array(
    "Database" => "NombreBaseDatos",
    "UID" => "usuario_sql",
    "PWD" => "contraseña_sql",
    "CharacterSet" => "UTF-8"
);

$conexion = sqlsrv_connect($serverName, $connectionInfo);

if ($conexion === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Alternativa con PDO (si está habilitado)
/*
try {
    $conexion = new PDO("sqlsrv:Server=$serverName;Database=NombreBaseDatos", "usuario", "contraseña");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
*/
?&gt;
                    </div>
                </div>
            </section>
            
            <!-- Base de Datos SQL Server -->
            <section id="basedatos" class="section">
                <h2>Base de Datos SQL Server</h2>
                <div class="card">
                    <h3>Estructura de la Base de Datos</h3>
                    <p>El sistema utiliza las siguientes tablas principales en SQL Server:</p>
                    
                    <div class="db-diagram">
                        <h4>Tabla: usuarios</h4>
                        <table class="table-schema">
                            <tr>
                                <th>Campo</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                            </tr>
                            <tr>
                                <td>id</td>
                                <td>INT IDENTITY(1,1)</td>
                                <td>Llave primaria autoincremental</td>
                            </tr>
                            <tr>
                                <td>usuario</td>
                                <td>VARCHAR(50)</td>
                                <td>Nombre de usuario único</td>
                            </tr>
                            <tr>
                                <td>password</td>
                                <td>VARCHAR(255)</td>
                                <td>Contraseña encriptada</td>
                            </tr>
                            <tr>
                                <td>nombre</td>
                                <td>VARCHAR(100)</td>
                                <td>Nombre completo</td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td>VARCHAR(100)</td>
                                <td>Correo electrónico</td>
                            </tr>
                            <tr>
                                <td>fecha_registro</td>
                                <td>DATETIME</td>
                                <td>Fecha de registro</td>
                            </tr>
                        </table>
                    </div>
                    
                    <h3>Script de Creación de Base de Datos</h3>
                    <div class="code-block">
                        <div class="code-header">
                            script_basedatos.sql - Creación de tablas
                            <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                        </div>
CREATE DATABASE SistemaGestion;
GO

USE SistemaGestion;
GO

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT IDENTITY(1,1) PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    fecha_registro DATETIME DEFAULT GETDATE(),
    activo BIT DEFAULT 1
);
GO

-- Insertar usuario admin por defecto
INSERT INTO usuarios (usuario, password, nombre, email) 
VALUES ('admin', CONVERT(VARCHAR(255), HASHBYTES('SHA2_256', 'admin123')), 'Administrador', 'admin@sistema.com');
GO

-- Tabla para logs del sistema
CREATE TABLE logs_sistema (
    id INT IDENTITY(1,1) PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100),
    fecha DATETIME DEFAULT GETDATE(),
    detalles TEXT
);
GO
                    </div>
                    
                    <h3>Consideraciones de SQL Server</h3>
                    <ul>
                        <li>Utilice consultas parametrizadas para prevenir inyección SQL</li>
                        <li>SQL Server diferencia entre mayúsculas y minúsculas según la collation configurada</li>
                        <li>Para mejores resultados, use la codificación UTF-8 en la conexión</li>
                        <li>Considere usar stored procedures para operaciones complejas</li>
                    </ul>
                </div>
            </section>
            
            <!-- Manual de Usuario -->
            <section id="manual-usuario" class="section">
                <h2>Manual de Usuario</h2>
                <div class="card">
                    <h3>Acceso al Sistema</h3>
                    <p>Para acceder al sistema, siga estos pasos:</p>
                    <ol>
                        <li>Abra su navegador web y vaya a la dirección del sistema</li>
                        <li>Ingrese su nombre de usuario y contraseña</li>
                        <li>Haga clic en el botón "Iniciar Sesión"</li>
                    </ol>
                    
                    <h3>Uso del Tablero Principal</h3>
                    <p>Después del login, accederá al tablero principal que muestra:</p>
                    <ul>
                        <li>Métricas principales del sistema</li>
                        <li>Accesos rápidos a funciones comunes</li>
                        <li>Menú de navegación lateral</li>
                        <li>Información del usuario conectado</li>
                    </ul>
                    
                    <h3>Generación de Reportes</h3>
                    <p>Para generar reportes:</p>
                    <ol>
                        <li>Navegue a la sección "Reportes"</li>
                        <li>Seleccione el tipo de reporte deseado</li>
                        <li>Defina los parámetros de filtrado (fechas, categorías, etc.)</li>
                        <li>Haga clic en "Generar Reporte"</li>
                        <li>Descargue o visualice el resultado (PDF, Excel, etc.)</li>
                    </ol>
                </div>
            </section>
            
            <!-- Manual de Desarrollo -->
            <section id="manual-desarrollo" class="section">
                <h2>Manual de Desarrollo</h2>
                <div class="card">
                    <h3>Estructura de Archivos</h3>
                    <p>El proyecto sigue el patrón MVC (Modelo-Vista-Controlador) de forma simplificada:</p>
                    <div class="code-block">
                        <div class="code-header">
                            Estructura de directorios
                        </div>
proyecto/
├── index.html          # Página principal
├── login.php           # Controlador de autenticación
├── conexion.php        # Conexión a SQL Server
├── tablero.php         # Vista principal
├── reportes/           # Módulo de reportes
│   ├── ventas.php
│   ├── usuarios.php
│   └── ...
├── css/
│   ├── estilo.css
│   └── ...
├── js/
│   ├── main.js
│   └── ...
└── img/
                    </div>
                    
                    <h3>Convenciones de Código para SQL Server</h3>
                    <ul>
                        <li>Usar consultas parametrizadas con <code>sqlsrv_prepare</code> o <code>PDO</code></li>
                        <li>Manejar correctamente los resultados con <code>sqlsrv_fetch_array</code></li>
                        <li>Liberar recursos con <code>sqlsrv_free_stmt</code> y <code>sqlsrv_close</code></li>
                        <li>Usar transacciones para operaciones críticas</li>
                        <li>Validar datos en ambos lados (cliente y servidor)</li>
                    </ul>
                    
                    <h3>Agregar Nuevas Funcionalidades</h3>
                    <p>Para agregar una nueva funcionalidad:</p>
                    <ol>
                        <li>Cree el archivo PHP en la carpeta correspondiente</li>
                        <li>Actualice el menú de navegación si es necesario</li>
                        <li>Agregue los estilos CSS necesarios</li>
                        <li>Incluya scripts JavaScript si se requieren</li>
                        <li>Actualice la documentación</li>
                    </ol>
                </div>
            </section>
            
            <!-- API y Funciones -->
            <section id="api" class="section">
                <h2>API y Funciones Principales</h2>
                <div class="card">
                    <h3>Función de Conexión a SQL Server</h3>
                    <div class="code-block">
                        <div class="code-header">
                            conexion.php - Función de conexión
                            <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                        </div>
// conexion.php
function conectarSQLServer() {
    $serverName = "localhost";
    $connectionInfo = array(
        "Database" => "SistemaGestion",
        "UID" => "usuario",
        "PWD" => "contraseña",
        "CharacterSet" => "UTF-8"
    );
    
    $conexion = sqlsrv_connect($serverName, $connectionInfo);
    
    if ($conexion === false) {
        $errors = sqlsrv_errors();
        error_log("Error de conexión SQL Server: " . print_r($errors, true));
        return false;
    }
    
    return $conexion;
}
                    </div>
                    
                    <h3>Función de Autenticación con SQL Server</h3>
                    <div class="code-block">
                        <div class="code-header">
                            login.php - Autenticación
                            <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                        </div>
// login.php
function autenticarUsuario($usuario, $password) {
    $conexion = conectarSQLServer();
    
    if ($conexion === false) {
        return false;
    }
    
    // Consulta parametrizada para prevenir SQL injection
    $sql = "SELECT id, usuario, password, nombre FROM usuarios WHERE usuario = ? AND activo = 1";
    $params = array($usuario);
    $stmt = sqlsrv_query($conexion, $sql, $params);
    
    if ($stmt === false) {
        sqlsrv_close($conexion);
        return false;
    }
    
    if (sqlsrv_fetch($stmt)) {
        $id = sqlsrv_get_field($stmt, 0);
        $usuario_db = sqlsrv_get_field($stmt, 1);
        $password_hash = sqlsrv_get_field($stmt, 2);
        $nombre = sqlsrv_get_field($stmt, 3);
        
        // Verificar contraseña
        if (password_verify($password, $password_hash)) {
            // Iniciar sesión
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario'] = $usuario_db;
            
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conexion);
            return true;
        }
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conexion);
    return false;
}
                    </div>
                    
                    <h3>Función para Ejecutar Consultas</h3>
                    <div class="code-block">
                        <div class="code-header">
                            funciones.php - Ejecutar consultas
                            <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                        </div>
// funciones.php
function ejecutarConsulta($sql, $params = array()) {
    $conexion = conectarSQLServer();
    
    if ($conexion === false) {
        return false;
    }
    
    $stmt = sqlsrv_query($conexion, $sql, $params);
    
    if ($stmt === false) {
        error_log("Error en consulta: " . print_r(sqlsrv_errors(), true));
        sqlsrv_close($conexion);
        return false;
    }
    
    $resultados = array();
    while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $resultados[] = $fila;
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conexion);
    
    return $resultados;
}
                    </div>
                </div>
            </section>
            
            <!-- Soporte y Contacto -->
            <section id="soporte" class="section">
                <h2>Soporte y Contacto</h2>
                <div class="card">
                    <h3>Recursos de Ayuda</h3>
                    <p>Si necesita ayuda con el sistema, consulte los siguientes recursos:</p>
                    <ul>
                        <li><strong>FAQ:</strong> Preguntas frecuentes sobre el uso del sistema</li>
                        <li><strong>Foro de la comunidad:</strong> Intercambio de ideas y soluciones</li>
                        <li><strong>Documentación técnica:</strong> Detalles de implementación</li>
                    </ul>
                    
                    <h3>Información de Contacto</h3>
                    <div class="contact-info">
                        <strong>Email:</strong> gerencia.tic.csb@gmail.com
                        <a class="email-button" href="mailto:gerencia.tic.csb@gmail.com?subject=Soporte Sistema Gestión&body=Hola, necesito ayuda con el sistema de gestión">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="white">
                                <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                            </svg>
                            Enviar Email
                        </a>
                    </div>
                    
                    <div class="contact-info">
                        <strong>Teléfono:</strong> +58 (412) 032-6845
                        <a class="whatsapp-button" aria-label="Chat on WhatsApp" href="https://wa.me/584120326845" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20" fill="white">
                                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                            </svg>
                            WhatsApp
                        </a>
                    </div>
                    
                    <div class="contact-info">
                        <strong>Horario de atención:</strong> Lunes a Miércoles, 7:00am - 6:00pm
                        <span class="schedule-badge">11 horas de atención</span>
                    </div>
                    
                    <h3>Solucionar Problemas Comunes</h3>
                    <div class="warning">
                        <strong>Error: "Unable to connect to SQL Server"</strong><br>
                        Verifique que el servicio de SQL Server esté ejecutándose y que las credenciales sean correctas.
                    </div>
                    
                    <div class="note">
                        <strong>Problema: Caracteres especiales se muestran incorrectamente</strong><br>
                        Asegúrese de que la conexión use UTF-8 y que la collation de la base de datos sea compatible.
                    </div>
                    
                    <h3>Reportar un Problema</h3>
                    <p>Al reportar un problema, por favor incluya:</p>
                    <ol>
                        <li>Descripción detallada del problema</li>
                        <li>Pasos para reproducir el error</li>
                        <li>Capturas de pantalla si es posible</li>
                        <li>Información del navegador y sistema operativo</li>
                    </ol>
                    
                    <p>Puede contactarnos por los siguientes medios:</p>
                    <div class="contact-buttons">
                        <a class="whatsapp-button" href="https://wa.me/584120326845?text=Hola,%20necesito%20ayuda%20con%20el%20sistema%20de%20gestión" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20" fill="white">
                                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                            </svg>
                            Contactar por WhatsApp
                        </a>
                        
                        <a class="email-button" href="mailto:gerencia.tic.csb@gmail.com?subject=Soporte Sistema Gestión&body=Hola, necesito ayuda con el sistema de gestión" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="white">
                                <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                            </svg>
                            Contactar por Email
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Navegación entre secciones
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = document.querySelectorAll('.section');
            const sidebar = document.getElementById('sidebar');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const searchInput = document.getElementById('searchInput');
            
            // Manejar clic en enlaces de navegación
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remover clase active de todos los enlaces
                    navLinks.forEach(l => l.classList.remove('active'));
                    
                    // Agregar clase active al enlace clickeado
                    this.classList.add('active');
                    
                    // Ocultar todas las secciones
                    sections.forEach(section => section.classList.remove('active'));
                    
                    // Mostrar la sección correspondiente
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.add('active');
                    
                    // Cerrar sidebar en móviles
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                    }
                    
                    // Scroll to top
                    window.scrollTo(0, 0);
                });
            });
            
            // Toggle sidebar en móviles
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Cerrar sidebar al hacer clic fuera de él en móviles
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    e.target !== toggleSidebar) {
                    sidebar.classList.remove('active');
                }
            });
            
            // Funcionalidad de búsqueda
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                if (searchTerm.length < 2) {
                    // Limpiar resaltado si la búsqueda es muy corta
                    document.querySelectorAll('.highlight').forEach(el => {
                        el.outerHTML = el.innerHTML;
                    });
                    return;
                }
                
                // Buscar en el contenido de las secciones
                sections.forEach(section => {
                    const content = section.textContent.toLowerCase();
                    
                    if (content.includes(searchTerm)) {
                        // Resaltar término encontrado
                        highlightText(section, searchTerm);
                    }
                });
            });
            
            // Función para resaltar texto
            function highlightText(element, searchTerm) {
                const regex = new RegExp(searchTerm, 'gi');
                const html = element.innerHTML;
                
                // Limpiar resaltados anteriores
                const cleanHtml = html.replace(/<span class="highlight">(.*?)<\/span>/gi, '$1');
                
                // Aplicar nuevo resaltado
                const newHtml = cleanHtml.replace(regex, match => 
                    `<span class="highlight">${match}</span>`
                );
                
                element.innerHTML = newHtml;
            }
            
            // Manejar submenús
            const menuItemsWithSubmenu = document.querySelectorAll('.sidebar-menu > li > a');
            menuItemsWithSubmenu.forEach(item => {
                if (item.nextElementSibling && item.nextElementSibling.classList.contains('submenu')) {
                    item.addEventListener('click', function(e) {
                        if (window.innerWidth > 768) {
                            e.preventDefault();
                            const submenu = this.nextElementSibling;
                            submenu.classList.toggle('active');
                        }
                    });
                }
            });
        });
        
        // Función para copiar código
        function copyCode(button) {
            const codeBlock = button.closest('.code-block');
            const code = codeBlock.querySelector('pre') ? 
                         codeBlock.querySelector('pre').textContent : 
                         codeBlock.textContent;
            
            // Eliminar el texto del header si existe
            const cleanCode = code.replace(/.*?conexion\.php - Configuración SQL Server/, '')
                                 .replace(/.*?script_basedatos\.sql - Creación de tablas/, '')
                                 .replace(/.*?login\.php - Autenticación/, '')
                                 .replace(/.*?funciones\.php - Ejecutar consultas/, '')
                                 .trim();
            
            navigator.clipboard.writeText(cleanCode).then(function() {
                const originalText = button.textContent;
                button.textContent = '¡Copiado!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            });
        }
    </script>
</body>
</html>