<?php
// comprobar_conexion.php
require_once 'db.php';

$conn = conectar_bd();
if ($conn) {
    echo "Conexión exitosa a la base de datos.";
    pg_close($conn);
} else {
    echo "Error en la conexión a la base de datos.";
}
?>
