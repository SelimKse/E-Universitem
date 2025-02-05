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

// yoklamalar tablosunda durumu aktif olan 1 olan yoklamaları çekiyoruz
$sql = "SELECT * FROM yoklamalar WHERE aktiflik = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$yoklamalar = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $yoklamalar[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli</title>
    <link rel="stylesheet" href="/public/css/yoklama-katil.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

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
                <h2>Yoklamaya Katıl</h2>
            </div>
            <div class="main-content">
                <div class="aktif-yoklamalar">
                    <div class="aktif-yoklamalar-header">
                        <span class="material-symbols-outlined"> notifications_unread </span>
                        <h3>Aktif Yoklamalar</h3>
                    </div>
                    <div class="aktif-yoklamalar-content">
                        <?php if (!empty($yoklamalar)): ?>
                            <?php foreach ($yoklamalar as $yoklama): ?>
                                <div class="yoklama">
                                    <div class="yoklama-text">
                                        <h4><?php echo htmlspecialchars($yoklama['yoklama_id']) ?></h4>
                                        <span><?php echo htmlspecialchars($yoklama['baslatilma_tarihi']); ?></span>
                                    </div>
                                    <div class="yoklama-icon">
                                        <button class="katil-button"
                                            data-yoklama-id="<?php echo htmlspecialchars($yoklama['yoklama_id']); ?>">
                                            <span class="material-symbols-outlined"> note_add </span>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="yoklama">
                                <div class="empty-yoklama">
                                    <h4>Aktif Yoklama bulunamamıştır!</h4>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="yoklama-kodu">
                    <div class="yoklama-kodu-header">
                        <span class="material-symbols-outlined"> password </span>
                        <h3>Yoklama Kodu ile Katıl</h3>
                    </div>
                    <div class="yoklama-kodu-content">
                        <div class="yoklama-kodu-input">
                            <input type="text" placeholder="Yoklama Kodu" id="yoklama-kodu" />
                            <button id="yoklama-kodu-katil">
                                Yoklamaya Katıl
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="/public/js/yoklama-katil.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>