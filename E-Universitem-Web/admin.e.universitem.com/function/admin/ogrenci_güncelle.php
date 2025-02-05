<?php
session_start();
include("../connect_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $ogrenci_no = $data->ogrenciNo;
    $ogrenci_adi = $data->ogrenciAdı ?? null;
    $ogrenci_soyadi = $data->ogrenciSoyadı ?? null;
    $ogrenci_eposta = $data->ogrenciEposta ?? null;
    $ogrenci_sifre = $data->ogrenciSifre ?? null;
    $ogrenci_telefon = $data->ogrenciPhoneNo ?? null;

    $response = [];

    // Öğrencinin mevcut bilgilerini al
    $stmt = $pdo->prepare("SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no");
    $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
    $stmt->execute();
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingData) {
        $response['status'] = 'error';
        $response['message'] = 'Öğrenci bulunamadı.';
    } else {
        // Aynı veriler kontrolü
        $noChange = true;

        if ($ogrenci_adi && $ogrenci_adi !== $existingData['ogrenci_adi']) {
            $noChange = false;
        }
        if ($ogrenci_soyadi && $ogrenci_soyadi !== $existingData['ogrenci_soyadi']) {
            $noChange = false;
        }
        if ($ogrenci_eposta && $ogrenci_eposta !== $existingData['ogrenci_eposta']) {
            $noChange = false;
        }
        if ($ogrenci_telefon && $ogrenci_telefon !== $existingData['ogrenci_telefon']) {
            $noChange = false;
        }
        if ($ogrenci_sifre && !password_verify($ogrenci_sifre, $existingData['ogrenci_sifre'])) {
            $noChange = false;
        }

        if ($noChange) {
            $response['status'] = 'warning';
            $response['message'] = 'Hiçbir değişiklik yapılmadı.';
        } else {
            // Şifreyi hashle
            $hashed_sifre = $ogrenci_sifre ? password_hash($ogrenci_sifre, PASSWORD_DEFAULT) : $existingData['ogrenci_sifre'];

            // Verileri güncelle
            $stmt = $pdo->prepare("UPDATE ogrenciler SET
                ogrenci_adi = COALESCE(:ogrenci_adi, ogrenci_adi),
                ogrenci_soyadi = COALESCE(:ogrenci_soyadi, ogrenci_soyadi),
                ogrenci_eposta = COALESCE(:ogrenci_eposta, ogrenci_eposta),
                ogrenci_sifre = COALESCE(:ogrenci_sifre, ogrenci_sifre),
                ogrenci_telefon = COALESCE(:ogrenci_telefon, ogrenci_telefon)
                WHERE ogrenci_no = :ogrenci_no");

            $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
            $stmt->bindParam(':ogrenci_adi', $ogrenci_adi, PDO::PARAM_STR);
            $stmt->bindParam(':ogrenci_soyadi', $ogrenci_soyadi, PDO::PARAM_STR);
            $stmt->bindParam(':ogrenci_eposta', $ogrenci_eposta, PDO::PARAM_STR);
            $stmt->bindParam(':ogrenci_sifre', $hashed_sifre, PDO::PARAM_STR);
            $stmt->bindParam(':ogrenci_telefon', $ogrenci_telefon, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Öğrenci bilgileri başarıyla güncellendi.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Güncelleme sırasında bir hata oluştu.';
            }
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
