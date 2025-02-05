<?php
include "function/connect_db.php";

session_start();

if (!isset($_SESSION['ogretmen_data']['ogretmen_no'])) {
    header("Location: https://e-universitem.com/auth/ogretmen/giris.php");
    die();
}

$ogretmen_no = $_SESSION['ogretmen_data']['ogretmen_no'];
$ogretmen_adi = $_SESSION['ogretmen_data']['adi'];
$ogretmen_soyadi = $_SESSION['ogretmen_data']['soyadi'];
$ogretmen_email = $_SESSION['ogretmen_data']['email'];

?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Paneli</title>
    <link rel="stylesheet" href="/public/css/ayarlar.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
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
            <h2>Öğretmen Menü</h2>
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
                <a href="/Verilen Dersler.php">
                    <span class="material-symbols-outlined"> script </span>Verilen Dersler</a>
            </li>
            <h4>
                <span>Yoklama</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/aldigin-yoklamalar.php"><span class="material-symbols-outlined"> overview </span>Aldığın
                    Yoklamalar</a>
            </li>
            <li>
                <a href="/yoklama-baslat.php"><span class="material-symbols-outlined"> add_task </span>Yoklama
                    Başlat</a>
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
                <img src="/assets/ogretmen.png" alt="ogretmen Profil" />
                <div class="user-detail">
                    <h3><?php echo htmlspecialchars($ogretmen_adi . " " . $ogretmen_soyadi); ?></h3>
                    <span><?php echo htmlspecialchars($ogretmen_email); ?></span>
                </div>
            </div>
        </div>
    </aside>

    <div class="container">
        <div class="content">
            <div class="main-content">
                <div class="header">
                    <h1>Ayarlar</h1>
                </div>
                <div class="settings">
                    <div class="settings-item theme-settings">
                        <div class="settings-header">
                            <h2>Temayı Değiştir</h2>
                            <p>Mevcut tema: <span id="theme-icon"></span> </p>
                        </div>
                        <div class="theme-options">
                            <div class="light-theme" onclick="toggleTheme('light')">
                                <span class="material-symbols-outlined">
                                    light_mode
                                </span>
                                <span class="text">Açık Tema</span>
                            </div>
                            <div class="dark-theme" onclick="toggleTheme('dark')">
                                <span class="material-symbols-outlined">
                                    dark_mode
                                </span>
                                <span class="text">Koyu Tema</span>
                            </div>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="header">
                            <h2>İletişim</h2>
                        </div>
                        <div class="settings-content">
                            <div class="contact-info">
                                <div class="contact-item">
                                    <span class="material-symbols-outlined">mail</span>
                                    <span>Bir sorun yada öneriniz mi var? Bize <a
                                            href="https://mail.google.com/mail/?view=cm&fs=1&to=destek@e-universitem.com&su=KONU&body=Mesajınız!"
                                            target="_blank">mail</a>
                                        gönderin!</span>
                                </div>
                                <div class="contact-item">
                                    <span class="material-symbols-outlined">message</span>
                                    <span>Whatsapp üzerinden <a href="https://wa.me/+905394399943"
                                            target="_blank">bize</a>
                                        ulaşın!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</body>

<script src="/public/js/ayarlar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>