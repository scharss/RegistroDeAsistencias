<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Datos del administrador
    $email = 'mycorreo@gmail.com';
    $password = 'mycontraseña';
    
    // Generar nuevo hash
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Actualizar la contraseña
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE correo = ?");
    if ($stmt->execute([$hash, $email])) {
        echo "Contraseña actualizada correctamente\n";
        echo "Nuevo hash: " . $hash . "\n";
        
        // Verificar que funciona
        $checkStmt = $conn->prepare("SELECT password FROM usuarios WHERE correo = ?");
        $checkStmt->execute([$email]);
        $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            echo "Verificación exitosa: la nueva contraseña funciona correctamente";
        } else {
            echo "Error: La verificación de la nueva contraseña falló";
        }
    } else {
        echo "Error al actualizar la contraseña";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 