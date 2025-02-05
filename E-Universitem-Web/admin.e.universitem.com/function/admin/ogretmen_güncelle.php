<?php
session_start();
include("../connect_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $ogretmen_no = $data->ogretmenNo;
    $ogretmen_adi = $data->ogretmenAdi ?? null;
    $ogretmen_soyadi = $data->ogretmenSoyadi ?? null;
    $ogretmen_eposta = $data->ogretmenEposta ?? null;
    $ogretmen_sifre = $data->ogretmenSifre ?? null;
    $ogretmen_telefon = $data->ogretmenPhoneNo ?? null;

    $response = [];

    // Öğretmenin mevcut bilgilerini al
    $stmt = $pdo->prepare("SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no");
    $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
    $stmt->execute();
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingData) {
        $response['status'] = 'error';
        $response['message'] = 'Öğretmen bulunamadı.';
    } else {
        // Aynı veriler kontrolü
        $noChange = true;

        if ($ogretmen_adi && $ogretmen_adi !== $existingData['ogretmen_adi']) {
            $noChange = false;
        }
        if ($ogretmen_soyadi && $ogretmen_soyadi !== $existingData['ogretmen_soyadi']) {
            $noChange = false;
        }
        if ($ogretmen_eposta && $ogretmen_eposta !== $existingData['ogretmen_eposta']) {
            $noChange = false;
        }
        if ($ogretmen_telefon && $ogretmen_telefon !== $existingData['ogretmen_telefon']) {
            $noChange = false;
        }
        if ($ogretmen_sifre && !password_verify($ogretmen_sifre, $existingData['ogretmen_sifre'])) {
            $noChange = false;
        }

        if ($noChange) {
            $response['status'] = 'warning';
            $response['message'] = 'Hiçbir değişiklik yapılmadı.';
        } else {
            // Şifreyi hashle
            $hashed_sifre = $ogretmen_sifre ? password_hash($ogretmen_sifre, PASSWORD_DEFAULT) : $existingData['ogretmen_sifre'];

            // Verileri güncelle
            $stmt = $pdo->prepare("UPDATE ogretmenler SET
                ogretmen_adi = COALESCE(:ogretmen_adi, ogretmen_adi),
                ogretmen_soyadi = COALESCE(:ogretmen_soyadi, ogretmen_soyadi),
                ogretmen_eposta = COALESCE(:ogretmen_eposta, ogretmen_eposta),
                ogretmen_sifre = COALESCE(:ogretmen_sifre, ogretmen_sifre),
                ogretmen_telefon = COALESCE(:ogretmen_telefon, ogretmen_telefon)
                WHERE ogretmen_no = :ogretmen_no");

            $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
            $stmt->bindParam(':ogretmen_adi', $ogretmen_adi, PDO::PARAM_STR);
            $stmt->bindParam(':ogretmen_soyadi', $ogretmen_soyadi, PDO::PARAM_STR);
            $stmt->bindParam(':ogretmen_eposta', $ogretmen_eposta, PDO::PARAM_STR);
            $stmt->bindParam(':ogretmen_sifre', $hashed_sifre, PDO::PARAM_STR);
            $stmt->bindParam(':ogretmen_telefon', $ogretmen_telefon, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Öğretmen bilgileri başarıyla güncellendi.';
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
