<?php

session_start();

// Veritabanı bağlantısını dahil et
include 'connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents("php://input"));

    $ogrenci_no = $data->ogrenci_no;
    $eski_sifre = $data->eski_sifre;
    $yeni_sifre = $data->yeni_sifre;
    $yeni_sifre_tekrar = $data->yeni_sifre_tekrar;

    $response = [];

    try {
        $sql = "SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
        $stmt->execute();

        $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);

        // Eski şifre doğru mu kontrol et
        if (password_verify($eski_sifre, $ogrenci['ogrenci_sifre'])) {
            // Yeni şifreler eşleşiyor mu kontrol et
            if ($yeni_sifre === $yeni_sifre_tekrar) {

                // eski şifre ile yeni şifre aynı olamaz
                if ($eski_sifre === $yeni_sifre) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Eski şifre ile yeni şifre aynı olamaz!'
                    ];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }

                // Yeni şifreyi hashle
                $hashed_password = password_hash($yeni_sifre, PASSWORD_DEFAULT);

                // Şifreyi güncelle
                $sql = "UPDATE ogrenciler SET ogrenci_sifre = :ogrenci_sifre WHERE ogrenci_no = :ogrenci_no";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':ogrenci_sifre', $hashed_password, PDO::PARAM_STR);
                $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
                $stmt->execute();

                $response = [
                    'status' => 'success',
                    'message' => 'Şifreniz başarıyla değiştirildi!'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Yeni şifreler eşleşmiyor!'
                ];
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Eski şifrenizi yanlış girdiniz!'
            ];
        }
    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }

    // Yanıtı döndür
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