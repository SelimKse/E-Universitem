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
    <link rel="stylesheet" href="/public/css/index.css">
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

    <div class="loader-div">
        <div class="loader"></div>
    </div>

    <aside class="sidebar" id="sidebar">
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
                <a href="/verdigin-dersler.php">
                    <span class="material-symbols-outlined"> script </span>Verdiğin Dersler</a>
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
            <div class="header">
                <h2>Hoşgeldin!</h2>
            </div>
            <div class="main-content">
                <div class="box analiz">
                    <div class="box-header">
                        <!-- Yeni H3 Başlık -->
                        <span class="material-symbols-outlined">
                            query_stats
                        </span>
                        <h3 class="box-title">Yoklama Analizi</h3>
                    </div>


                    <div class="card-counter">
                        <i class="fa fa-calendar-day"></i>
                        <div class="count-numbers" id="gunluk-yoklama-sayi">0</div>
                        <div class="count-name">Bugün Aldığın Yoklamalar</div>
                    </div>
                    <div class="card-counter">
                        <i class="fa fa-calendar-week"></i>
                        <div class="count-numbers" id="haftalik-yoklama-sayi">0</div>
                        <div class="count-name">Haftalık Aldığın Yoklamalar</div>
                    </div>
                    <div class="card-counter">
                        <i class="fa fa-calendar-days"></i>
                        <div class="count-numbers" id="aylik-yoklama-sayi">0</div>
                        <div class="count-name">Aylık Aldığın Yoklamalar</div>
                    </div>
                    <div class="card-counter">
                        <i class="fa fa-calendar-check"></i>
                        <div class="count-numbers" id="toplam-yoklama-sayi">0</div>
                        <div class="count-name">Toplam Yoklama Sayısı</div>
                    </div>
                </div>
                <div class="box top-lesson">
                    <div class="box-header">
                        <!-- Yeni H3 Başlık -->
                        <span class="material-symbols-outlined">
                            new_releases
                        </span>
                        <h3 class="box-title">Sık Verdiğin Dersler</h3>
                    </div>

                    <div class="top-lesson-header">
                        <div class="header-name" onclick="sortList('name', 'top-lesson-list')">
                            Ders Adı
                            <span id="name-arrow" class="sort-arrow"></span>
                        </div>
                        <div class="header-count" onclick="sortList('count', 'top-lesson-list')">
                            Katılım Sayısı
                            <span id="count-arrow" class="sort-arrow"></span>
                        </div>
                    </div>

                    <div class="top-lesson-list" id="top-lesson-list">
                        <!-- <div class="top-lesson-item">
                            <div class="top-lesson-name">Beden</div>
                            <div class="top-lesson-count">5</div>
                        </div> -->
                    </div>
                </div>

                <div class="box last-lesson">
                    <div class="box-header">
                        <!-- Yeni H3 Başlık -->
                        <span class="material-symbols-outlined">
                            history
                        </span>
                        <h3 class="box-title">Son Aldığın Yoklamalar</h3>
                    </div>
                    <div class="last-lesson-header">
                        <div class="header-name" onclick="sortList('name', 'last-lesson-list')">
                            Ders Adı
                            <span id="name-arrow" class="sort-arrow"></span>
                        </div>
                        <div class="header-date" onclick="sortList('date', 'last-lesson-list')">
                            Tarih
                            <span id="date-arrow" class="sort-arrow"></span>
                        </div>
                    </div>
                    <div class="last-lesson-list" id="last-lesson-list">
                        <!-- <div class="last-lesson-item">
                            <div class="last-lesson-name">Beden</div>
                            <div class="last-lesson-date">5.12.2021</div>
                        </div> -->
                    </div>
                </div>


                <div class="box bildirimler">
                    <div class="box-header">
                        <!-- Yeni H3 Başlık -->
                        <span class="material-symbols-outlined">
                            notifications
                        </span>
                        <h3 class="box-title">Bildirimler</h3>
                    </div>
                    <div class="bildirim-list" id="bildirim-list">
                        <!-- <div class="bildirim-item">
                            <div class="bildirim-content">
                                <span class="bildirim-date">12.12.2021</span>
                                <span class="bildirim-text">Beden dersine katıldınız.</span>
                            </div>
                            <div class="bildirim-icon">
                                <i class="fa fa-check"></i>
                            </div>
                        </div> -->
                    </div>
                </div>
</body>

<script src="/public/js/index.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>