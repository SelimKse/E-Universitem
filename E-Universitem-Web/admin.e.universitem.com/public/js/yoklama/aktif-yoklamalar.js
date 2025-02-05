const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: toast => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

async function logout(event) {
  const response = await fetch("../../function/logout.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  });

  const request = await response.json();

  if (request.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Çıkış Başarılı!",
    });
  } else {
    Toast.fire({
      icon: "success",
      title: "Çıkış Başarılı!",
    });
  }

  setTimeout(() => {
    window.location.href = "/";
  }, 3000);
}

var sidebar = document.querySelector(".sidebar");

// Sidebar'a hover yapıldığında accordion menülerin açılmasını sağlayan fonksiyon
function toggleAccordion(event, id) {
  event.preventDefault();
  var accordionMenu = document.getElementById(id);
  var arrow = event.target.querySelector(".arrow") || event.target.parentElement.querySelector(".arrow") || event.target.children[2]; // Ok öğesini seç

  // Menü açık mı kontrol et
  var isOpen = accordionMenu.style.maxHeight;

  if (isOpen) {
    accordionMenu.style.maxHeight = null; // Menü kapat
    arrow.classList.remove("rotate"); // Oku eski haline çevir
  } else {
    accordionMenu.style.maxHeight = accordionMenu.scrollHeight + "px"; // Menü aç
    arrow.classList.add("rotate"); // Oku döndür
  }

  // Diğer menüleri kapat ve okları eski haline getir
  var menus = document.querySelectorAll(".accordion-menu");
  menus.forEach(menu => {
    if (menu.id !== id) {
      menu.style.maxHeight = null; // Kapalı menüyü kapat
      var otherArrow = menu.previousElementSibling.querySelector(".arrow"); // Ok öğesini seç
      if (otherArrow) {
        otherArrow.classList.remove("rotate"); // Diğer okları eski haline çevir
      }
    }
  });
}

// Sidebar üzerine gelindiğinde 'active' sınıfını ekleyin
sidebar.addEventListener("mouseenter", function () {
  sidebar.classList.add("active");
});

// Sidebar'dan ayrıldığında menüleri kapatın ve 'active' sınıfını kaldırın
sidebar.addEventListener("mouseleave", function () {
  sidebar.classList.remove("active");

  // Tüm accordion menüleri kapat
  var menus = document.querySelectorAll(".accordion-menu");
  menus.forEach(menu => {
    menu.style.maxHeight = null;
    var arrow = menu.previousElementSibling.querySelector(".arrow");
    if (arrow) {
      arrow.classList.remove("rotate"); // Okları eski haline çevir
    }
  });
});
// Sayfa tamamen yüklendiğinde font kontrolü yap

function checkFontLoaded() {
  const font = new FontFaceObserver("Font Awesome 6 Free");
  const loader = document.querySelector(".loader-div");

  function hideLoader() {
    loader.style.display = "none";
  }

  function retryLoad() {
    setTimeout(() => {
      font
        .load()
        .then(hideLoader)
        .catch(() => {
          console.error("İconlar yüklenirken tekrar bir sorun oluştu.");
          retryLoad(); // Hata durumunda tekrar dene
        });
    }, 500); // 500 ms sonra yeniden dene
  }

  font
    .load()
    .then(hideLoader)
    .catch(() => {
      console.error("İconlar yüklenirken bir sorun oluştu.");
      retryLoad();
    });
}

window.addEventListener("load", () => {
  checkFontLoaded();
  changeTheme();

  const yoklamaList = document.getElementById("yoklamalar");
  const yoklamalar = yoklamaList.getElementsByTagName("li");

  // Eğer sınıf yoksa, "Yoklama bulunamadı" mesajını içeren bir <li> ekle
  if (yoklamalar.length === 0) {
    const noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults"; // Kimlik ataması yap
    noResultsItem.textContent = "Aktif yoklama bulunamadı!"; // Mesajı ayarla
    noResultsItem.style.color = "#000"; // Yazı rengini kırmızı yap
    noResultsItem.style.display = "list-item"; // Görünür yap
    yoklamaList.appendChild(noResultsItem); // Listeye ekle

    const searchInput = document.getElementById("search_yoklama");
    searchInput.setAttribute("disabled", "disabled"); // Arama girişini devre dışı bırak
  }
});

// ------------------------------------------------------------------------------------------ \\

function changeTheme() {
  const theme = localStorage.getItem("theme");
  const root = document.querySelector("html");
  const themeButton = document.getElementById("themeButton");

  if (theme === "dark") {
    root.classList.add("dark");
    root.classList.remove("light");
    themeButton.classList.add("fa-sun");
    themeButton.classList.remove("fa-moon");
    localStorage.setItem("theme", "dark"); // Varsayılan olarak 'dark' ayarla
  } else {
    root.classList.add("light");
    root.classList.remove("dark");
    themeButton.classList.add("fa-moon");
    themeButton.classList.remove("fa-sun");
    localStorage.setItem("theme", "light"); // Varsayılan olarak 'light' ayarla
  }
}

function toggleTheme() {
  const theme = localStorage.getItem("theme") === "dark" ? "light" : "dark";
  console.log(theme);
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

// ------------------------------------------------------------------------------------------ \\

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\
var yoklamaId;

async function yoklamaDetay(event, yoklama_id) {
  const response = await fetch("../../function/admin/yoklamalar.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ yoklama_id }),
  });

  const request = await response.json();
  yoklamaId = yoklama_id;

  const yoklamaDetay = document.getElementsByClassName("yoklamaDetay")[0];
  yoklamaDetay.classList.toggle("active");

  const baslangicTarihi = new Date(request.data.baslatilma_tarihi);
  // gün ay yıl saat dakika saniye
  const formattedDate = new Intl.DateTimeFormat("tr-TR", {
    dateStyle: "full",
    timeStyle: "short",
  }).format(baslangicTarihi);

  const bitisTarihi = new Date(request.data.bitis_tarihi);
  const formattedEndDate = new Intl.DateTimeFormat("tr-TR", {
    dateStyle: "full",
    timeStyle: "short",
  }).format(bitisTarihi);

  console.log(request.data.aktiflik);

  const aktiflik = request.data.aktiflik === 1 ? "Aktif" : "Pasif";

  const ogrenciler = JSON.parse(request.data.katilan_ogrenciler);
  console.log(ogrenciler);
  if (ogrenciler != undefined) {
    const ogrenciList = [];
    ogrenciler.forEach(async ogrenci => {
      const ogrenciDetay = await ogrencilerDetay(ogrenci);
      ogrenciList.push(ogrenciDetay);
      document.getElementById("yoklama_ogrenciler").textContent = ogrenciList.join(", ");
    });
  } else {
    document.getElementById("yoklama_ogrenciler").textContent = "Öğrenci bulunamadı!";
  }

  document.getElementById("yoklama_id").textContent = request.data.yoklama_id;
  document.getElementById("yoklama_dersi").textContent = await dersDetay(request.data.ders_id);
  document.getElementById("yoklama_sinifi").textContent = await sınıfDetay(request.data.sinif_id);
  document.getElementById("yoklama_ogretmeni").textContent = await ogretmenDetay(request.data.ogretmen_no);
  document.getElementById("yoklama_baslangic").textContent = formattedDate;
  document.getElementById("yoklama_bitis").textContent = formattedEndDate;
  document.getElementById("yoklama_aktiflik").textContent = aktiflik;
  document.getElementById("yoklama_kod").textContent = request.data.ozel_kod;
}

async function dersDetay(ders_id) {
  const response = await fetch("../../function/admin/dersler.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ders_id }),
  });

  const request = await response.json();
  return request.data.ders_adi;
}

async function sınıfDetay(sinif_id) {
  const response = await fetch("../../function/admin/sınıflar.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ sinif_id }),
  });

  const request = await response.json();
  return request.data.sinif_adi;
}

async function ogretmenDetay(ogretmen_no) {
  const response = await fetch("../../function/admin/ogretmenler.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogretmen_no }),
  });

  const request = await response.json();
  return `${request.data.ogretmen_adi} ${request.data.ogretmen_soyadi}`;
}

async function ogrencilerDetay(ogrenci_no) {
  const response = await fetch("../../function/admin/ogrenciler.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogrenci_no }),
  });

  const request = await response.json();
  console.log(request);
  return `${request.data.ogrenci_adi} ${request.data.ogrenci_soyadi}`;
}

// ------------------------------------------------------------------------------------------ \\
function closeYoklamaDetay() {
  const yoklamaDetay = document.getElementsByClassName("yoklamaDetay")[0];
  yoklamaDetay.classList.remove("active");
}

function closeKatilanOgrenciler() {
  const yoklamaList = document.getElementsByClassName("katilanOgrenciler")[0];
  yoklamaList.classList.remove("active");
}

async function yoklamaListesi() {
  const yoklamaDetay = document.getElementsByClassName("yoklamaDetay")[0];
  yoklamaDetay.classList.remove("active");

  const yoklamaList = document.getElementsByClassName("katilanOgrenciler")[0];
  yoklamaList.classList.toggle("active");

  const response = await fetch("../../function/admin/yoklamalar.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ yoklama_id: yoklamaId }),
  });

  const request = await response.json();

  const ogrenciler = JSON.parse(request.data.katilan_ogrenciler);
  if (ogrenciler != undefined) {
    const ogrenciList = [];
    ogrenciler.forEach(async ogrenci => {
      const ogrenciDetay = await ogrencilerDetay(ogrenci);
      ogrenciList.push(ogrenciDetay);
      document.getElementById("katilan_ogrenciler_liste").textContent = ogrenciList.join(", ");
    });
  } else {
    document.getElementById("katilan_ogrenciler_liste").textContent = "Öğrenci bulunamadı!";
  }
}
