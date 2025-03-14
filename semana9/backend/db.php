<?php

$host = 'localhost';
$dbname = "todo_app";
$user = "Jorvi";
$password = "132430jorviA";

try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "conexion exitosa". PHP_EOL;
}catch(Exception $e){
    die("Error de conexion: ". $e -> getMessage());
}