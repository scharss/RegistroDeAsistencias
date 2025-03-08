<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['grupo_id']) || !is_numeric($_GET['grupo_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de grupo no válido']);
    exit;
}

$grupo_id = (int)$_GET['grupo_id'];

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT id, nombre, apellidos, documento 
        FROM estudiantes 
        WHERE grupo_id = ? 
        ORDER BY nombre, apellidos
    ");
    $stmt->execute([$grupo_id]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($estudiantes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener estudiantes']);
} 