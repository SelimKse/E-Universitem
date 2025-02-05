<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $yoklama_id = $data->yoklama_id;

    $response = [];

    $sql = "SELECT * FROM yoklamalar WHERE yoklama_id = :yoklama_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':yoklama_id', $yoklama_id, PDO::PARAM_INT);
    $stmt->execute();

    $yoklama = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($yoklama) {
        $response['status'] = 'success';
        $response['data'] = $yoklama ? $yoklama : []; // Listeyi döndür
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Yoklama bulunamadı!';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz istek.'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>