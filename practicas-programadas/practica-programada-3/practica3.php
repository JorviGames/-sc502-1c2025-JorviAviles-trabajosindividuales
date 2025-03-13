<?php

session_start();
if (!isset($_SESSION['transacciones'])) {
    $_SESSION['transacciones'] = [];
}

function registrarTransaccion($id, $descripcion, $monto) {
    $_SESSION['transacciones'][] = [
        'id' => $id,
        'descripcion' => $descripcion,
        'monto' => $monto
    ];
}

function generarEstadoDeCuenta() {
    $transacciones = $_SESSION['transacciones'];
    $montoContado = 0;
    
    foreach ($transacciones as $transaccion) {
        $montoContado += $transaccion['monto'];
    }
    
    $interes = 0.026 * $montoContado;
    $montoConInteres = $montoContado + $interes;
    $cashBack = 0.001 * $montoContado;
    $montoFinal = $montoConInteres - $cashBack;
    
    $estadoCuenta = "ESTADO DE CUENTA\n";
    $estadoCuenta .= "-------------------------------------\n";
    $estadoCuenta .= "Transacciones:\n";
    
    foreach ($transacciones as $transaccion) {
        $estadoCuenta .= "ID: " . $transaccion['id'] . " | Descripción: " . $transaccion['descripcion'] . " | Monto: ₡" . number_format($transaccion['monto'], 2) . "\n";
    }
    
    $estadoCuenta .= "-------------------------------------\n";
    $estadoCuenta .= "Monto total de contado: ₡" . number_format($montoContado, 2) . "\n";
    $estadoCuenta .= "Monto total con interés (2.6%): ₡" . number_format($montoConInteres, 2) . "\n";
    $estadoCuenta .= "Cashback aplicado (0.1%): ₡" . number_format($cashBack, 2) . "\n";
    $estadoCuenta .= "Monto final a pagar: ₡" . number_format($montoFinal, 2) . "\n";
    
    file_put_contents("estado_cuenta.txt", $estadoCuenta);
    echo "Estado de cuenta generado y guardado en 'estado_cuenta.txt'.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
    $id = count($_SESSION['transacciones']) + 1;
    $descripcion = $_POST['descripcion'];
    $monto = floatval($_POST['monto']);
    registrarTransaccion($id, $descripcion, $monto);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generar'])) {
    generarEstadoDeCuenta();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Transacciones</title>
</head>
<body>
    <h2>Registrar Nueva Transacción</h2>
    <form method="POST">
        <label>Descripción:</label>
        <input type="text" name="descripcion" required>
        <label>Monto:</label>
        <input type="number" step="0.01" name="monto" required>
        <button type="submit" name="registrar">Registrar</button>
    </form>
    
    <h2>Transacciones Registradas</h2>
    <ul>
        <?php foreach ($_SESSION['transacciones'] as $transaccion): ?>
            <li>ID: <?= $transaccion['id'] ?> - <?= $transaccion['descripcion'] ?> - ₡<?= number_format($transaccion['monto'], 2) ?></li>
        <?php endforeach; ?>
    </ul>
    
    <form method="POST">
        <button type="submit" name="generar">Generar Estado de Cuenta</button>
    </form>
    
    <?php if (file_exists("estado_cuenta.txt")): ?>
        <h2>Descargar Estado de Cuenta</h2>
        <a href="estado_cuenta.txt" download>Descargar estado_cuenta.txt</a>
    <?php endif; ?>
</body>
</html>