<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Oturum yoksa bir JavaScript yönlendirmesi oluştur
    echo '<script>
            alert("Lütfen önce giriş yapın!");
            window.location.href = "/"; // Giriş sayfası
          </script>';
    exit(); // Kodun devamını çalıştırma
}

include '../../function/connect_db.php';

$sql = "SELECT * FROM dersler";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Ogremcileri al
$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ogretmenleri çekmek için SQL sorgusu
$sql = "SELECT * FROM ogretmenler";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Ogremcileri al
$ogretmenler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ders Ekle - Yönetim Paneli</title>
    <link rel="stylesheet" href="../../public/css/ders/ogretmen-tanımla.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
</head>


<body>
    <div class="loader-div">
        <div class="loader"></div>
    </div>

    <header>
        <nav>
            <div class="mobileMenu">
                <i class="fas fa-bars" onclick="toggleMenu()"></i>
            </div>
            <h4>Yönetim Paneli</h4>
            <div class="menu">
                <i class="fa-solid fa-sun" id="themeButton" onclick="toggleTheme()"></i>
                <i class="fa-solid fa-arrow-right-from-bracket" onclick="logout(event)"></i>
            </div>
        </nav>
    </header>

    <aside class="sidebar">
        <div class="sidebar-header">
            <button onclick="toggleMenu()">
                <i class="fas fa-times"></i>
            </button>
            <img src="../../assets/img/dashboard.png" alt="Admin">
            <h3>Yönetim Paneli</h3>
        </div>
        <ul class="sidebar-links">
            <h4>
                <span>Bilgi İşlem</span>
                <div class="menu-seperator"></div>
            </h4>
            <li>
                <a href="/dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Ana Sayfa</span>
                </a>
            </li>
            <li>
                <a href="/ogrenciler.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Öğrenciler</span>
                </a>
            </li>

            <li>
                <a href="/ogretmenler.php">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Öğretmenler</span>
                </a>
            </li>
            <li>
                <a href="/sınıflar.php">
                    <i class="fa-solid fa-school"></i>
                    <span>Sınıflar</span>
                </a>
            </li>
            <li>
                <a href="/dersler.php">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    <span>Dersler</span>
                </a>
            </li>
            <h4>
                <span>İşlemler</span>
                <div class="menu-seperator"></div>
            </h4>
            <li>
                <a href="" onclick="toggleAccordion(event, 'ogrenciler-menu')">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Öğrenci İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="ogrenciler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ogrenci/ogrenci-ekle.php">Yeni Öğrenci Ekle</a></li>
                    <li><a href="/yonetim/ogrenci/ders-tanımla.php">Ders Tanımla</a></li>
                </ul>
            </li>
            <li>
                <a href="#" onclick="toggleAccordion(event, 'ogretmenler-menu')">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Öğretmen İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="ogretmenler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ogretmen/ogretmen-ekle.php">Yeni Öğretmen Ekle</a></li>
                    <li><a href="/yonetim/ogretmen/ders-tanımla.php">Ders Tanımla</a></li>
                </ul>
            </li>
            <li>
                <a href="#" onclick="toggleAccordion(event, 'sınıflar-menu')">
                    <i class="fa-solid fa-school"></i>
                    <span>Sınıf İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="sınıflar-menu" class="accordion-menu">
                    <li><a href="/yonetim/sınıf/sınıf-ekle.php">Yeni Sınıf Ekle</a></li>
                </ul>
            </li>
            <li>
                <a href="#" onclick="toggleAccordion(event, 'dersler-menu')">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    <span>Ders İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="dersler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ders/ders-ekle.php">Yeni Ders Ekle</a></li>
                    <li><a href="/yonetim/ders/ogretmen-tanımla.php">Öğretmen Tanımla</a></li>
                    <li><a href="/yonetim/ders/ogrenci-tanımla.php">Öğrenci Tanımla</a></li>
                </ul>
            </li>

            <h4>
                <span>Yoklama</span>
                <div class="menu-seperator"></div>
            </h4>
            <li>
                <a href="/yonetim/yoklama/yoklamalar.php">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    <span>Yoklamalar</span>
                </a>
            </li>
            <li>
                <a href="/yonetim/yoklama/aktif-yoklamalar.php">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span>Aktif Yoklamalar</span>
                </a>
            </li>
            <li>
                <a href="/yonetim/yoklama/yoklama-baslat.php">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span>Yoklama Başlat</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="container">
        <div class="box">
            <h2>Ders Listesi</h2>
            <div class="listBox">
                <div class="search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" placeholder="Ders belirtin...">
                </div>
                <div class="liste" id="liste">
                    <ul id="dersler">
                        <?php foreach ($dersler as $ders): ?>
                            <li id="<?= $ders['ders_id'] ?>">
                                <!-- Öğrenci Bilgisi -->
                                <div class="ders" id="<?= $ders['ders_id'] ?>">
                                    <div class="text">
                                        <span id="ders_id"><?= $ders['ders_id'] ?></span>
                                        <span><?= $ders['ders_adi'] ?></span>
                                    </div>
                                    <div class="icons">
                                        <i class="fa-solid fa-user-plus"
                                            onclick="ogretmenEkle(event, '<?= $ders['ders_id'] ?>')"></i>
                                        <i class="fa-solid fa-eye"
                                            onclick="ogretmenListe(event, '<?= $ders['ders_id'] ?>')"></i>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="ogretmenListe">
            <div class="content">
                <h2>Öğretmen Listesi</h2>
                <i class="fas fa-times closeButton" onclick="closeOgretmenListe()"></i>
                <div class="ogretmenListeContainer">
                    <div class="search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchOgretmen" placeholder="Öğrenci belirtin...">
                    </div>
                    <div class="ogretmenList" id="ogretmenList">

                    </div>
                </div>
            </div>
        </div>

        <div class="ogretmenEkle">
            <div class="content">
                <h2>Öğretmen Ekle</h2>
                <i class="fas fa-times closeButton" onclick="closeOgretmenEkle()"></i>
                <div class="ogretmenEkleContainer">
                    <div class="search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchOgretmen2" placeholder="Öğretmen belirtin...">
                    </div>
                    <div class="ogretmenBul" id="ogretmenBul">
                        <?php foreach ($ogretmenler as $ogretmen): ?>
                            <li id="<?= $ogretmen['ogretmen_no'] ?>">
                                <!-- Öğrenci Bilgisi -->
                                <div class="ogretmen" id="<?= $ogretmen['ogretmen_no'] ?>">
                                    <div class="text">
                                        <span id="ogretmen_no"><?= $ogretmen['ogretmen_no'] ?></span>
                                        <span><?= $ogretmen['ogretmen_adi'] . " " . $ogretmen["ogretmen_soyadi"] ?></span>
                                    </div>
                                    <div class="icons">
                                        <i class="fa-solid fa-plus"
                                            onclick="dersEkle(event, <?= $ogretmen['ogretmen_no'] ?>)"></i>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="../../public/js/ders/ogretmen-tanımla.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>