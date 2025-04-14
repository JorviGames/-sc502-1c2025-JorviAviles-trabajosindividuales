<?php
require('db.php'); // Conexión a la base de datos

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');
session_start();

if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];
    switch ($method) {
        case 'POST':  // Agregar comentario
            $input = json_decode(file_get_contents("php://input"), true);
            if (isset($input['task_id'], $input['description'])) {
                try {
                    $sql = "INSERT INTO comments (task_id, description) VALUES (:task_id, :description)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'task_id' => $input['task_id'],
                        'description' => $input['description']
                    ]);
                    $commentId = $pdo->lastInsertId();
                    echo json_encode(["message" => "Comentario agregado", "comment_id" => $commentId]);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["error" => "Error agregando el comentario"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Datos insuficientes"]);
            }
            break;

        case 'DELETE':  // Eliminar comentario
            if (isset($_GET['task_id'], $_GET['comment_id'])) {
                try {
                    $sql = "DELETE FROM comments WHERE task_id = :task_id AND id = :comment_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'task_id' => $_GET['task_id'],
                        'comment_id' => $_GET['comment_id']
                    ]);
                    echo json_encode(["message" => "Comentario eliminado"]);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["error" => "Error eliminando el comentario"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Petición inválida"]);
            }
            break;

        case 'GET':  // Obtener comentarios
            if (isset($_GET['task_id'])) {
                try {
                    $sql = "SELECT id, description FROM comments WHERE task_id = :task_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['task_id' => $_GET['task_id']]);
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($comments);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["error" => "Error obteniendo los comentarios"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "ID de tarea requerido"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["error" => "Sesión no activa"]);
}
?>
