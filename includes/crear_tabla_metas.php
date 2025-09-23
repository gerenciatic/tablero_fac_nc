<?php
include_once 'conexsql.php';

// Crear tabla para almacenar las metas anuales
$sql = "
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='metas_anuales' AND xtype='U')
BEGIN
    CREATE TABLE metas_anuales (
        id INT IDENTITY(1,1) PRIMARY KEY,
        ano INT NOT NULL,
        meta DECIMAL(5,2) NOT NULL,
        fecha_creacion DATETIME NOT NULL,
        fecha_actualizacion DATETIME NULL,
        UNIQUE(ano)
    )
    
    PRINT 'Tabla metas_anuales creada correctamente';
END
ELSE
BEGIN
    PRINT 'La tabla metas_anuales ya existe';
END
";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    die("Error creando tabla: " . print_r($errors, true));
}

echo "Tabla de metas verificada/creada correctamente";

sqlsrv_free_stmt($stmt);
?>