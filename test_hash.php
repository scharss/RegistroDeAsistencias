<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Consultar el usuario
    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE correo = ?");
    $stmt->execute(['mycorreo@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Hash almacenado: " . $user['password'] . "\n";
        
        // Verificar la contraseña
        $password = 'mycontraseña';
        $isValid = password_verify($password, $user['password']);
        
        echo "Contraseña válida: " . ($isValid ? "Sí" : "No") . "\n";
        
        // Generar un nuevo hash para comparar
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        echo "Nuevo hash generado: " . $newHash . "\n";
    } else {
        echo "Usuario no encontrado";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 