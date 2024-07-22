<?php
// realizar_transferencia.php
require_once 'db.php';

function realizar_transferencia($nombre_usuario_remitente, $nombre_usuario_receptor, $monto) {
    $conn = conectar_bd();
    if (!$conn) {
        echo "Error en la conexión a la base de datos.";
        return;
    }

    // Verificar que ambos usuarios existan y obtener sus saldos
    $query_remitente = "SELECT saldo FROM usuarios WHERE nombre_usuario = $1";
    $result_remitente = pg_query_params($conn, $query_remitente, array($nombre_usuario_remitente));
    
    $query_receptor = "SELECT saldo FROM usuarios WHERE nombre_usuario = $1";
    $result_receptor = pg_query_params($conn, $query_receptor, array($nombre_usuario_receptor));

    if (pg_num_rows($result_remitente) == 0 || pg_num_rows($result_receptor) == 0) {
        echo "El remitente o el receptor no existe.";
        pg_close($conn);
        return;
    }

    $saldo_remitente = pg_fetch_result($result_remitente, 0, 'saldo');
    $saldo_receptor = pg_fetch_result($result_receptor, 0, 'saldo');

    // Verificar si el remitente tiene suficiente saldo
    if ($saldo_remitente < $monto) {
        echo "El remitente no tiene suficiente saldo.";
        pg_close($conn);
        return;
    }

    // Realizar la transferencia
    pg_query($conn, 'BEGIN');

    $nuevo_saldo_remitente = $saldo_remitente - $monto;
    $nuevo_saldo_receptor = $saldo_receptor + $monto;

    $update_remitente = "UPDATE usuarios SET saldo = $1 WHERE nombre_usuario = $2";
    $result_update_remitente = pg_query_params($conn, $update_remitente, array($nuevo_saldo_remitente, $nombre_usuario_remitente));

    $update_receptor = "UPDATE usuarios SET saldo = $1 WHERE nombre_usuario = $2";
    $result_update_receptor = pg_query_params($conn, $update_receptor, array($nuevo_saldo_receptor, $nombre_usuario_receptor));

    if ($result_update_remitente && $result_update_receptor) {
        pg_query($conn, 'COMMIT');
        echo "Transferencia realizada correctamente.";
    } else {
        pg_query($conn, 'ROLLBACK');
        echo "Error al realizar la transferencia.";
    }

    pg_close($conn);
}
?>
