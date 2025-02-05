<?php
session_start();
include './function/connect_db.php';

// ogretmenleri çekmek için SQL sorgusu
$sql = "SELECT * FROM ogretmenler";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Ogremcileri al
$ogretmenler = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Yönetim Paneli</title>
    <link rel="stylesheet" href="./public/css/ogretmen/ogretmenler.css">
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
                <a href="" onclick="toggleAccordion(event, 'ogretmenler-menu')">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Öğrenci İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="ogretmenler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ogretmen/ogretmen-ekle.php">Yeni Öğrenci Ekle</a></li>
                    <li><a href="/yonetim/ogretmen/ders-tanımla.php">Ders Tanımla</a></li>
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
                    <li><a href="/yonetim/ders/ogretmen-tanımla.php">Öğrenci Tanımla</a></li>
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
            <h2>Öğretmen Listesi</h2>
            <div class="listBox">
                <div class="search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" placeholder="Ara...">
                </div>
                <div class="liste">
                    <ul id="ogretmenler">
                        <?php foreach ($ogretmenler as $ogretmen): ?>
                            <li>
                                <div class="ogretmen" id="<?= $ogretmen['id'] ?>">
                                    <div class="text">
                                        <span><?= $ogretmen['ogretmen_no'] ?></span>
                                        <span><?= $ogretmen['ogretmen_adi'] . ' ' . $ogretmen['ogretmen_soyadi']; ?></span>
                                    </div>
                                    <div class="icons">
                                        <i class="fas fa-edit"
                                            onclick="editOgretmen(event, <?= $ogretmen['ogretmen_no'] ?>)"></i>
                                        <i class="fas fa-trash"
                                            onclick="deleteOgretmen(event, <?= $ogretmen['ogretmen_no'] ?>)"></i>
                                        <i class="fas fa-eye"
                                            onclick="showOgretmen(event, <?= $ogretmen['ogretmen_no'] ?>)"></i>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="editBox">
            <div class="content">
                <h2>Öğretmen Düzenle</h2>
                <i class="fas fa-times closeButton" onclick="closeEditBox()"></i>
                <div class="editForm">
                    <form action="POST" onsubmit="return updateOgretmen(event);" id="ogretmenEditForm">
                        <div class="ogretmanName">
                            <label for="ad">İsim</label>
                            <input type="text" id="ogretmenAdı" placeholder="Selim" name="ogretmenAdı"
                                pattern="^[a-zA-ZğüşöçıİĞÜŞÖÇ]+$" title="İsim sadece harf içerebilir."
                                title="İsim sadece harf içerebilir.">
                        </div>
                        <div class="ogretmenSoyad">
                            <label for="soyad">Soyisim</label>
                            <input type="text" id="ogretmenSoyadı" placeholder="Köse" name="ogretmenSoyadı"
                                pattern="^[a-zA-ZğüşöçıİĞÜŞÖÇ]+$" title="Soyisim sadece harf içerebilir."
                                title="Soyisim sadece harf içerebilir.">
                        </div>
                        <div class="ogretmenEposta">
                            <label for="eposta">E-Posta</label>
                            <input type="email" id="ogretmenEposta" placeholder="selim@iste.edu.tr"
                                name="ogretmenEposta">
                        </div>
                        <div class="ogretmenSifre">
                            <label for="sifre">Şifre</label>
                            <input type="password" id="ogretmenSifre" placeholder="Öğretmen Şifresi (Geçici)"
                                name="ogretmenSifre"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]{8,}$"
                                title="Şifre en az 8 karakter uzunluğunda olmalıdır ve en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.">
                        </div>
                        <div class="ogretmenPhoneNo">
                            <label for="telefon">Telefon Numarası</label>
                            <input type="text" id="ogretmenPhoneNo" placeholder="(123) 456 78-90" name="ogretmenPhoneNo"
                                pattern="^5\d{9}$" title="Telefon numarası 10 haneli olmalıdır ve 5 ile başlamalıdır.">
                        </div>
                    </form>
                </div>
                <div class="buttons">
                    <button id="kaydetButton" class="button submit" onclick="updateOgretmen(event)">Güncelle</button>
                    <button id="geciciSifre" class="button" onclick="geciciSifreGonder(event)">Geçiçi Şifre</button>
                </div>
            </div>
        </div>

        <div class="ogretmenDetay">
            <div class="content">
                <h2>Öğretmen Detay</h2>
                <i class="fas fa-times closeButton" onclick="closeOgretmenDetay()"></i>
                <div class="ogretmenDetayContainer">
                    <div class="ogretmenNo">
                        <label for="no">Öğretmen Numarası</label>
                        <span id="ogretmenNo">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="ogretmenAdi">
                        <label for="ad">İsim</label>
                        <span id="ogretmenAdi">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="ogretmenSoyad">
                        <label for="soyad">Soyisim</label>
                        <span id="ogretmenSoyad">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="ogretmenTelefon">
                        <label for="telefon">Telefon Numarası</label>
                        <span id="ogretmenTelefon">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="ogretmenEposta">
                        <label for="eposta">E-Posta</label>
                        <span id="ogretmenEmail">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="kayitTarihi">
                        <label for="kayit">Kayıt Tarihi</label>
                        <span id="kayitTarihi">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                    <div class="verdigiDersler">
                        <label for="dersler">Verdiği Dersler</label>
                        <span id="verdigiDersler">
                            <!-- Otomasyon ile doldurulacak -->
                        </span>
                    </div>
                </div>
                <div class="buttons">
                    <button id="dersleriDüzenle" class="button" onclick="editogretmenDersleri(event)">Dersleri
                        Düzenle</button>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

<script src="./public/js/ogretmen/ogretmenler.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>