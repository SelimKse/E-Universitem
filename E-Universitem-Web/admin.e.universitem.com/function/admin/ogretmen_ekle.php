<?php
session_start();

include("../connect_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ogretmen_no = $data->ogretmenNo;
    $ogretmen_adi = $data->ogretmenAdı;
    $ogretmen_soyadi = $data->ogretmenSoyadı;
    $ogretmen_eposta = $data->ogretmenEmail;
    $ogretmen_sifre = $data->ogretmenSifre;
    $ogretmen_telefon = $data->ogretmenPhoneNo;

    // Yanıt için bir dizi oluştur
    $response = [];

    $stmt = $pdo->prepare("SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no OR ogretmen_eposta = :ogretmen_eposta OR ogretmen_telefon = :ogretmen_telefon");
    $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
    $stmt->bindParam(':ogretmen_eposta', $ogretmen_eposta, PDO::PARAM_STR);
    $stmt->bindParam(':ogretmen_telefon', $ogretmen_telefon, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if (isset($result['ogretmen_no']) && $result['ogretmen_no'] === $ogretmen_no) {
            $response['status'] = 'error';
            $response['message'] = 'Bu öğretmen numarası başka bir öğretmene ait!';
        } else if (isset($result['ogretmen_eposta']) && $result['ogretmen_eposta'] === $ogretmen_eposta) {
            $response['status'] = 'error';
            $response['message'] = 'Bu e-posta adresi başka bir öğretmende tanımlı!';
        } else if (isset($result['ogretmen_telefon']) && $result['ogretmen_telefon'] === $ogretmen_telefon) {
            $response['status'] = 'error';
            $response['message'] = 'Bu telefon numarası başka bir öğretmen tanımlı!';
        }
    } else {
        $hashed_sifre = password_hash($ogretmen_sifre, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO ogretmenler (ogretmen_no, ogretmen_adi, ogretmen_soyadi, ogretmen_eposta, ogretmen_sifre, ogretmen_telefon) VALUES (:ogretmen_no, :ogretmen_adi, :ogretmen_soyadi, :ogretmen_eposta, :ogretmen_sifre, :ogretmen_telefon)");
        $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
        $stmt->bindParam(':ogretmen_adi', $ogretmen_adi, PDO::PARAM_STR);
        $stmt->bindParam(':ogretmen_soyadi', $ogretmen_soyadi, PDO::PARAM_STR);
        $stmt->bindParam(':ogretmen_eposta', $ogretmen_eposta, PDO::PARAM_STR);
        $stmt->bindParam(':ogretmen_sifre', $hashed_sifre, PDO::PARAM_STR);
        $stmt->bindParam(':ogretmen_telefon', $ogretmen_telefon, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Öğretmen başarıyla eklendi.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Öğretmen eklenirken bir hata oluştu.';
        }
    }


    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $response['status'] = 'error';
    $response['message'] = 'Geçersiz istek.';

    header('Content-Type: application/json');
    echo json_encode($response);

    exit();
}