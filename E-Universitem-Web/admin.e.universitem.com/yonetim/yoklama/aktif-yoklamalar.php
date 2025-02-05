<?php
session_start();
include '../../function/connect_db.php';

// Ogrencileri çekmek için SQL sorgusu
$sql = "SELECT * FROM yoklamalar";
$stmt = $pdo->prepare($sql);
$stmt->execute();

echo '<script>console.log("Yoklamalar çekildi.")</script>';

// Ogremcileri al
$yoklamalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['username'])) {
    // Oturum yoksa bir JavaScript yönlendirmesi oluştur
    echo '<script>
            alert("Lütfen önce giriş yapın!");
            window.location.href = "/"; // Giriş sayfası
          </script>';
    exit(); // Kodun devamını çalıştırma
}

?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoklama Başlat - Yönetim Paneli</title>
    <link rel="stylesheet" href="../../public/css/yoklama/aktif-yoklamalar.css">
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
                <i class="fa-solid fa-sun" id="themeButton" onclick="toggleTheme(event)"></i>
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
            <h2>Yoklama Listesi</h2>
            <div class="listBox">
                <div class="search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" placeholder="Ara...">
                </div>
                <div class="liste">
                    <ul id="yoklamalar">
                        <?php foreach ($yoklamalar as $yoklama): ?>
                            <?php if ($yoklama['aktiflik'] == 1): // Yalnızca aktif olanları göster ?>
                                <li>
                                    <div class="yoklama" id="<?= $yoklama['yoklama_id'] ?>">
                                        <span><?= $yoklama['yoklama_id'] ?></span>
                                        <div class="icons">
                                            <i class="fas fa-eye"
                                                onclick="yoklamaDetay(event, <?= $yoklama['yoklama_id'] ?>)"></i>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="yoklamaDetay">
            <div class="content">
                <h2>Öğrenci Detay</h2>
                <i class="fas fa-times closeButton" onclick="closeYoklamaDetay()"></i>
                <div class="yoklamaDetayContainer">
                    <!-- yoklama id -->
                    <div class="yoklama_id">
                        <label for="yoklama_id">Yoklama ID</label>
                        <span id="yoklama_id">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama dersi -->
                    <div class="yoklama_dersi">
                        <label for="yoklama_dersi">Ders</label>
                        <span id="yoklama_dersi">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama sınıfı -->
                    <div class="yoklama_sinifi">
                        <label for="yoklama_sinifi">Sınıf</label>
                        <span id="yoklama_sinifi">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama öğretmeni -->
                    <div class="yoklama_ogretmeni">
                        <label for="yoklama_ogretmeni">Öğretmen</label>
                        <span id="yoklama_ogretmeni">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama başlangıç tarihi -->
                    <div class="yoklama_baslangic">
                        <label for="yoklama_baslangic">Başlangıç Tarihi</label>
                        <span id="yoklama_baslangic">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama bitiş tarihi -->
                    <div class="yoklama_bitis">
                        <label for="yoklama_bitis">Bitiş Tarihi</label>
                        <span id="yoklama_bitis">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama aktiflik -->
                    <div class="yoklama_aktiflik">
                        <label for="yoklama_aktiflik">Aktif mi?</label>
                        <span id="yoklama_aktiflik">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="yoklama_kod">
                        <label for="yoklama_kod">Yoklama Kodu</label>
                        <span id="yoklama_kod">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <!-- yoklama öğrencileri -->
                    <div class="yoklama_ogrenciler">
                        <label for="yoklama_ogrenciler">Katılan Öğrenciler</label>
                        <span id="yoklama_ogrenciler">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="buttons">
                        <button class="button" onclick="yoklamaListesi(event)">Katılan Öğrenciler</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="katilanOgrenciler">
            <div class="content">
                <h2>Katılan Öğrenciler</h2>
                <i class="fas fa-times closeButton" onclick="closeKatilanOgrenciler()"></i>
                <div class="katilanOgrencilerContainer">
                    <span id="katilan_ogrenciler_liste">
                        <!-- Otomasyon ile doldurulacak -->
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="../../public/js/yoklama/aktif-yoklamalar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>