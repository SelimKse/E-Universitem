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

$sql = "SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
$stmt->execute();

$ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ogrenci) {
    echo "Öğrenci bulunamadı!";
    die();
}

?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli</title>
    <link rel="stylesheet" href="/public/css/hesabim.css">
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
                <h2>Hesabım</h2>
            </div>
            <div class="main-content">
                <div class="user-info">
                    <div class="profil-photo">
                        <img src="/assets/ogrenci.png" alt="Profil Fotoğrafı" />
                    </div>
                    <div class="user-detail">
                        <div class="ogrenci-no">
                            <span class="detail-header">Öğrenci No:</span>
                            <span class="detail-content"><?php echo htmlspecialchars($ogrenci_no); ?></span>
                        </div>
                        <div class="isim">
                            <span class="detail-header">Adı:</span>
                            <span class="detail-content"><?php echo htmlspecialchars($ogrenci_adi); ?></span>
                        </div>
                        <div class="soyisim">
                            <span class="detail-header">Soyadı:</span>
                            <span class="detail-content"><?php echo htmlspecialchars($ogrenci_soyadi); ?></span>
                        </div>
                        <div class="email">
                            <span class="detail-header">Email:</span>
                            <span class="detail-content"><?php echo htmlspecialchars($ogrenci_email); ?></span>
                        </div>
                        <div class="telefon">
                            <span class="detail-header">Telefon:</span>
                            <span
                                class="detail-content"><?php echo htmlspecialchars($ogrenci["ogrenci_telefon"]); ?></span>
                        </div>
                        <div class="kayit-tarihi">
                            <span class="detail-header">Kayıt Tarihi:</span>
                            <span
                                class="detail-content"><?php echo htmlspecialchars($ogrenci["kayit_tarihi"]); ?></span>
                        </div>
                    </div>
                </div>
                <div class="islemler">
                    <button class="btn" id="sifre-degistir" onclick="sifreDegistirMenu()">Şifre Değiştir</button>
                </div>
            </div>
        </div>

        <div class="sifre-degistir-menu" id="sifre-degistir-menu">
            <div class="sifre-degistir-content">
                <div class="sifre-degistir-header">
                    <h2>Şifre Değiştir</h2>
                    <span class="close-btn" id="close-btn" onclick="sifreDegistirMenu()">
                        <i class="fas fa-times"></i>
                    </span>
                </div>
                <div class="sifre-degistir-form">
                    <div class="input-group">
                        <label for="eski-sifre">Eski Şifre</label>
                        <input type="password" id="eski-sifre" name="eski-sifre" placeholder="Eski şifrenizi giriniz." />
                        <span class="material-symbols-outlined" id="show-password-eski-sifre"
                            onclick="showPassword('eski-sifre')">visibility_off</span>
                    </div>
                    <div class="input-group">
                        <label for="yeni-sifre">Yeni Şifre</label>
                        <input type="password" id="yeni-sifre" name="yeni-sifre" placeholder="Yeni şifrenizi giriniz." />
                        <span class="material-symbols-outlined" id="show-password-yeni-sifre"
                            onclick="showPassword('yeni-sifre')">visibility_off</span>
                    </div>
                    <div class="input-group">
                        <label for="yeni-sifre-tekrar">Yeni Şifre Tekrar</label>
                        <input type="password" id="yeni-sifre-tekrar" name="yeni-sifre-tekrar" placeholder="Yeni şifrenizi tekrar giriniz." />
                        <span class="material-symbols-outlined" id="show-password-yeni-sifre-tekrar"
                            onclick="showPassword('yeni-sifre-tekrar')">visibility_off</span>
                    </div>
                    <button class="btn" id="sifre-degistir-btn" onclick="sifreDegistir()">Şifre Değiştir</button>
                </div>
            </div>
        </div>
</body>

<script src="/public/js/hesabim.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>