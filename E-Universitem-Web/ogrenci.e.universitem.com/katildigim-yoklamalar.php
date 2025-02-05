<?php
include "function/connect_db.php";

session_start();

if (!isset($_SESSION['ogrenci_data']['ogrenci_no'])) {
    header("Location: https://e-universitem.com/auth/ogrenci/giris.php");
    die();
}

$ogrenci_no = $_SESSION['ogrenci_data']['ogrenci_no'];
$ogrenci_adi = $_SESSION['ogrenci_data']['ogrenci_adi'];
$ogrenci_soyadi = $_SESSION['ogrenci_data']['ogrenci_soyadi'];
$ogrenci_email = $_SESSION['ogrenci_data']['ogrenci_eposta'];

// yoklamalar tablosunda katilan_ogrenciler kısmındaki ogrenci_no arasından ogrenci_no'yu bulup yoklama_id'yi alıyoruz
$sql = "SELECT yoklama_id FROM yoklamalar WHERE katilan_ogrenciler LIKE :ogrenci_no";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
$stmt->execute();

$yoklamalar = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $yoklamalar[] = $row['yoklama_id'];
}

?>


<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli</title>
    <link rel="stylesheet" href="/public/css/katildigim-yoklamalar.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="shortcut icon" href="/assets/icon-beyaz.png" type="image/x-icon">
</head>

<body>

    <div class="loader-div">
        <div class="loader"></div>
    </div>

    <header>
        <div class="header-menu">
            <button class="menu-button" id="menu-button" onclick="toogleMenu()">
                <span class="material-symbols-outlined"> menu </span>
            </button>
        </div>
        <div class="header-logo">
            <img id="header-logo" src="/assets/logo-beyaz.png" alt="logo" />
        </div>
        <div class="header-buttons">
            <button class="logout-button" onclick="logout()">
                <span class="material-symbols-outlined"> logout </span>
            </button>
        </div>
    </header>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/assets/icon-beyaz.png" alt="logo" />
            <h2>Öğrenci Menü</h2>
            <button>
                <span class="material-symbols-outlined" onclick="toogleMenu()"> close </span>
            </button>
        </div>
        <ul class="sidebar-links">
            <h4>
                <span>Bilgi İşlem</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/">
                    <span class="material-symbols-outlined"> home </span>Ana Sayfa</a>
            </li>
            <li>
                <a href="/derslerim.php">
                    <span class="material-symbols-outlined"> script </span>Derslerim</a>
            </li>
            <h4>
                <span>Yoklama</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/katildigim-yoklamalar.php"><span class="material-symbols-outlined"> overview </span>Katıldığım
                    Yoklamalar</a>
            </li>
            <li>
                <a href="/yoklama-katil.php"><span class="material-symbols-outlined"> note_add </span>Yoklamaya
                    Katıl</a>
            </li>
            <h4>
                <span>Hesap</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/hesabim.php"><span class="material-symbols-outlined"> account_circle </span>Hesabım</a>
            </li>
            <li>
                <a href="/ayarlar.php"><span class="material-symbols-outlined"> settings </span>Ayarlar</a>
            </li>
            <li>
                <a href="/cikis-yap.php"><span class="material-symbols-outlined"> logout </span>Çıkış Yap</a>
            </li>
        </ul>
        <div class="user-account">
            <div class="user-profile">
                <img src="/assets/ogrenci.png" alt="Ogrenci Profil" />
                <div class="user-detail">
                    <h3><?php echo htmlspecialchars($ogrenci_adi . " " . $ogrenci_soyadi); ?></h3>
                    <span><?php echo htmlspecialchars($ogrenci_email); ?></span>
                </div>
            </div>
        </div>
    </aside>

    <div class="container">
        <div class="content">
            <div class="header">
                <h2>Katıldığım Yoklamalar</h2>
            </div>
            <div class="main-content">
                <div class="yoklamalar">
                    <div class="yoklamalar-header">
                        <div class="yoklama-ara">
                            <span class="material-symbols-outlined"> search </span>
                            <input type="text" placeholder="Yoklama Ara" />
                        </div>
                        <div class="yoklama-head">
                            <span>Yoklama ID</span>
                            <span>Öğretmen</span>
                            <span>Yoklama Tarihi</span>
                        </div>
                    </div>
                    <div class="yoklamalar-body">
                        <?php if (!empty($yoklamalar)): ?>
                            <?php foreach ($yoklamalar as $yoklama): ?>
                                <div class="yoklama">
                                    <div class="yoklama-text">
                                        <h4><?php echo htmlspecialchars($yoklama); ?></h4>
                                    </div>
                                    <div class="yoklama-icon">
                                        <span class="material-symbols-outlined"> visibility </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="yoklama">
                                <div class="empty-yoklama">
                                    <h4>Henüz katıldığınız yoklama bulunmamaktadır.</h4>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
</body>

<script src="/public/js/katildigim-yoklamalar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>