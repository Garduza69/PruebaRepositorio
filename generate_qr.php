<?php
session_start();

require 'phpqrcode/qrlib.php';
require_once "conexion.php";

// Verificar si el usuario está autenticado y obtener su correo electrónico
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Consultar el idUsuario del usuario autenticado
    $stmt_usuario = $pdo->prepare("SELECT idUsuario FROM usuario WHERE Email = :email");
    $stmt_usuario->bindParam(":email", $email);
    $stmt_usuario->execute();
    $row_usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if ($row_usuario) {
        $userId = $row_usuario['idUsuario'];
    } else {
        // Manejar el error apropiadamente si el usuario no se encuentra en la base de datos
        exit("Error: Usuario no encontrado en la base de datos.");
    }
} else {
    // Manejar el error si el usuario no está autenticado
    exit("Error: Usuario no autenticado.");
}

// Obtener el materia_id seleccionado desde la solicitud GET
if (isset($_GET['materia_id'])) {
    $materia_id = $_GET['materia_id'];
} else {
    // Manejar el error si no se proporciona un materia_id
    exit("Error: No se proporcionó un materia_id.");
}

// Generar un token único para el código QR
$token = bin2hex(random_bytes(16)); // Generar un token hexadecimal de 16 bytes

// Insertar el token, correo electrónico, idUsuario y materia_id en la tabla codigos_qr
$sql = "INSERT INTO codigos_qr (token, correo, id_usuario, materia_id) VALUES (?, ?, ?, ?)";
$stmt_insert = $pdo->prepare($sql);
$stmt_insert->bindParam(1, $token, PDO::PARAM_STR);
$stmt_insert->bindParam(2, $email, PDO::PARAM_STR);
$stmt_insert->bindParam(3, $userId, PDO::PARAM_INT);
$stmt_insert->bindParam(4, $materia_id, PDO::PARAM_INT);
$stmt_insert->execute();

// Ruta y nombre del archivo generado
$dir = 'temp/';
if (!file_exists($dir)) {
    mkdir($dir);
}
$filename = $dir . 'test' . $token . '.png';

// Parámetros de Configuración para generar el código QR
$tamanio = 10; // Tamaño de Pixel
$level = 'L'; // Precisión Baja
$framSize = 3; // Tamaño en blanco

// Generar el código QR y guardar el archivo
QRcode::png($token, $filename, $level, $tamanio, $framSize);

// Retornar la ruta del archivo generado
echo $filename;
?>