<?php

session_start();

// Veritabanı bağlantısını dahil et
include 'connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents("php://input"));

    $ogretmen_no = $data->ogretmen_no;

    $response = [];

    try {
        // sistemdeki tüm yoklamaları çek
        $sql = "SELECT * FROM yoklamalar";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $yoklamalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // yoklamalar arasında ogretmen sutunundaki ogretmen_noya eşit olan yoklamaları al
        $yoklama = array_filter($yoklamalar, function ($yoklama) use ($ogretmen_no) {
            return $yoklama['ogretmen_no'] == $ogretmen_no;
        });

        // var olan yoklamaları döndür
        $response = [
            'status' => 'success',
            'data' => $yoklama,
        ];

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }

    // Yoklama bilgilerini döndür
    header('Content-Type: application/json');
    echo json_encode($response);

    exit;

} else {
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz istek!'
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}