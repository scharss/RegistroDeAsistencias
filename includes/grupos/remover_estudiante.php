<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$estudiante_id = filter_var($_POST['estudiante_id'], FILTER_VALIDATE_INT);
$grupo_id = filter_var($_POST['grupo_id'], FILTER_VALIDATE_INT);

if (!$estudiante_id || !$grupo_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar que el estudiante pertenezca al grupo
    $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE id = ? AND grupo_id = ?");
    $stmt->execute([$estudiante_id, $grupo_id]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El estudiante no pertenece a este grupo']);
        exit;
    }

    // Remover estudiante del grupo (establecer grupo_id a NULL)
    $stmt = $conn->prepare("UPDATE estudiantes SET grupo_id = NULL WHERE id = ?");
    $stmt->execute([$estudiante_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Estudiante removido del grupo exitosamente'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Error al remover el estudiante del grupo'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
} 