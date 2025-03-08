<?php
$password = 'mycontraseña';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash generado: " . $hash . "\n";

// Verificar que el hash funciona
if (password_verify($password, $hash)) {
    echo "El hash es válido\n";
} else {
    echo "Error en el hash\n";
} 