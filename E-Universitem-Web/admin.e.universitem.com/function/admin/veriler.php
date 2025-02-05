<?php

// Bağlantıyı dışarıdan dahil ediyoruz
include '../connect_db.php'; // Veritabanı bağlantı dosyasını dahil et

// 1. Fonksiyon: Öğrenciler
function ogrenciler()
{
    global $pdo; // PDO bağlantısını kullanıyoruz
    $sql = "SELECT * FROM ogrenciler";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $ogrenciler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($ogrenciler);
}

// 2. Fonksiyon: Öğretmenler
function ogretmenler()
{
    global $pdo; // PDO bağlantısını kullanıyoruz
    $sql = "SELECT * FROM ogretmenler";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $ogretmenler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($ogretmenler);
}

// 3. Fonksiyon: Sınıflar
function siniflar()
{
    global $pdo; // PDO bağlantısını kullanıyoruz
    $sql = "SELECT * FROM siniflar";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $siniflar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($siniflar);
}

// 4. Fonksiyon: Dersler
function dersler()
{
    global $pdo; // PDO bağlantısını kullanıyoruz
    $sql = "SELECT * FROM dersler";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($dersler);
}

// 5. Fonksiyon: Yoklamalar
function yoklamalar()
{
    global $pdo; // PDO bağlantısını kullanıyoruz
    $sql = "SELECT * FROM yoklamalar";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $yoklamalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($yoklamalar);
}

// Ana işleyiş: Parametreye göre fonksiyon çağırma
if (isset($_GET['veri'])) {
    $veri = $_GET['veri'];

    switch ($veri) {
        case 'ogrenciler':
            ogrenciler();
            break;
        case 'ogretmenler':
            ogretmenler();
            break;
        case 'siniflar':
            siniflar();
            break;
        case 'dersler':
            dersler();
            break;
        case 'yoklamalar':
            yoklamalar();
            break;
        default:
            echo json_encode(["message" => "Geçersiz veri isteği."]);
            break;
    }
} else {
    echo json_encode(["message" => "Veri parametresi sağlanmadı."]);
}

?>