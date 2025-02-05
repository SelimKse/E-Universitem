<?php
session_start();

include("../connect_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ogrenci_no = $data->ogrenciNo;
    $ogrenci_adi = $data->ogrenciAdı;
    $ogrenci_soyadi = $data->ogrenciSoyadı;
    $ogrenci_eposta = $data->ogrenciEmail;
    $ogrenci_sifre = $data->ogrenciSifre;
    $ogrenci_telefon = $data->ogrenciPhoneNo;

    // Yanıt için bir dizi oluştur
    $response = [];

    $stmt = $pdo->prepare("SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no OR ogrenci_eposta = :ogrenci_eposta OR ogrenci_telefon = :ogrenci_telefon");
    $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
    $stmt->bindParam(':ogrenci_eposta', $ogrenci_eposta, PDO::PARAM_STR);
    $stmt->bindParam(':ogrenci_telefon', $ogrenci_telefon, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if (isset($result['ogrenci_no']) && $result['ogrenci_no'] === $ogrenci_no) {
            $response['status'] = 'error';
            $response['message'] = 'Bu öğrenci numarası başka bir öğrenciye ait!';
        } else if (isset($result['ogrenci_eposta']) && $result['ogrenci_eposta'] === $ogrenci_eposta) {
            $response['status'] = 'error';
            $response['message'] = 'Bu e-posta adresi başka bir öğrencide tanımlı!';
        } else if (isset($result['ogrenci_telefon']) && $result['ogrenci_telefon'] === $ogrenci_telefon) {
            $response['status'] = 'error';
            $response['message'] = 'Bu telefon numarası başka bir öğrencide tanımlı!';
        }
    } else {
        $hashed_sifre = password_hash($ogrenci_sifre, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO ogrenciler (ogrenci_no, ogrenci_adi, ogrenci_soyadi, ogrenci_eposta, ogrenci_sifre, ogrenci_telefon) VALUES (:ogrenci_no, :ogrenci_adi, :ogrenci_soyadi, :ogrenci_eposta, :ogrenci_sifre, :ogrenci_telefon)");
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
        $stmt->bindParam(':ogrenci_adi', $ogrenci_adi, PDO::PARAM_STR);
        $stmt->bindParam(':ogrenci_soyadi', $ogrenci_soyadi, PDO::PARAM_STR);
        $stmt->bindParam(':ogrenci_eposta', $ogrenci_eposta, PDO::PARAM_STR);
        $stmt->bindParam(':ogrenci_sifre', $hashed_sifre, PDO::PARAM_STR);
        $stmt->bindParam(':ogrenci_telefon', $ogrenci_telefon, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Öğrenci başarıyla eklendi.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Öğrenci eklenirken bir hata oluştu.';
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