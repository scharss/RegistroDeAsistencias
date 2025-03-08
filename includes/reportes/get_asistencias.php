<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Construir la consulta base
    $sql = "
        SELECT 
            CONCAT(e.nombre, ' ', e.apellidos) as estudiante,
            e.documento,
            g.nombre as grupo,
            CONCAT(u.nombre, ' ', u.apellidos) as profesor,
            DATE_FORMAT(a.fecha_hora, '%d/%m/%Y %H:%i') as fecha_hora
        FROM asistencias a
        JOIN estudiantes e ON a.estudiante_id = e.id
        JOIN usuarios u ON a.profesor_id = u.id
        LEFT JOIN grupos g ON e.grupo_id = g.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filtrar por grupo
    if (!empty($_POST['grupo'])) {
        $sql .= " AND e.grupo_id = ?";
        $params[] = $_POST['grupo'];
    }
    
    // Filtrar por profesor
    if (!empty($_POST['profesor'])) {
        $sql .= " AND a.profesor_id = ?";
        $params[] = $_POST['profesor'];
    }
    
    // Filtrar por fechas
    if (!empty($_POST['fechas'])) {
        $fechas = explode(' - ', $_POST['fechas']);
        if (count($fechas) == 2) {
            $sql .= " AND DATE(a.fecha_hora) BETWEEN ? AND ?";
            $params[] = $fechas[0];
            $params[] = $fechas[1];
        }
    }
    
    // Ordenar por fecha
    $sql .= " ORDER BY a.fecha_hora DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'data' => $asistencias
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener las asistencias',
        'message' => $e->getMessage()
    ]);
} 