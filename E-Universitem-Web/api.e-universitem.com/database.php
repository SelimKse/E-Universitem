<?php
// Veritabanı bağlantısı için gerekli bilgileri girin
$host = "localhost";   // Veritabanı sunucusu (genellikle localhost)
$dbname = "eun96ersitemcom_ogrenci_yoklama";  // Veritabanı adı
$username = "eun96ersitemcom_admin";  // Veritabanı kullanıcı adı
$password = "E*QcqDh{[k}8";  // Veritabanı şifresi

try {
    // PDO ile veritabanına bağlanıyoruz
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Hata durumunda Exception fırlatmasını sağlıyoruz
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Bağlantı hatası varsa ekrana hata mesajı basıyoruz
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}