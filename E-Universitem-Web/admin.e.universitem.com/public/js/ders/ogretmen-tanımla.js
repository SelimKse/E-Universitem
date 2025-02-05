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
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

window.addEventListener("load", () => {
  checkFontLoaded();
  changeTheme();
});

// ------------------------------------------------------------------------------------------ \\

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\

var searchInput = document.getElementById("search");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase(); // Arama metnini küçük harfe çevir
  const dersler = document.querySelectorAll("#dersler > li"); // Sadece #dersler altındaki li'leri seç
  console.log(dersler);
  let found = false; // Her aramada sonuç olup olmadığını kontrol etmek için bir değişken

  // Eğer daha önce oluşturulmuşsa "Ders bulunamadı" li'sini bulalım
  let noResultsItem = document.getElementById("noResults");

  // Eğer yoksa, yeni bir li oluşturalım
  if (!noResultsItem) {
    noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults";
    noResultsItem.textContent = "Ders bulunamadı";
    noResultsItem.style.display = "none"; // Başlangıçta gizli
    noResultsItem.style.color = "#000"; // Mesaj stilini ayarlayabilirsiniz
    noResultsItem.style.textAlign = "left"; // Ortalayın
    document.getElementById("dersler").appendChild(noResultsItem); // li öğesini ders listesinin sonuna ekle
  }

  dersler.forEach(ders => {
    const spanElement = ders.querySelectorAll("span")[1]; // İkinci span öğesini seç
    const dersAdi = spanElement ? spanElement.textContent.toLowerCase() : ""; // Ders ismini al

    // Eğer ders ismi arama metnini içeriyorsa
    if (dersAdi.includes(filter)) {
      ders.style.display = ""; // Görüntüle
      found = true; // En az bir sonuç bulundu
    } else {
      ders.style.display = "none"; // Gizle
    }
  });

  // Hiçbir sonuç bulunamadıysa "Ders bulunamadı" li öğesini göster, aksi halde gizle
  if (!found) {
    noResultsItem.style.display = "list-item";
  } else {
    noResultsItem.style.display = "none";
  }
});

async function ogretmenListe(event, dersId) {
  // Ders verilerini API'den çek
  const response = await fetch("../../function/admin/dersin_ogretmenleri.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ders_id: dersId }), // dersId'yi API'ye gönder
  });

  const result = await response.json();

  if (result.status === "success") {
    const ogretmenListe = document.getElementsByClassName("ogretmenListe")[0];
    ogretmenListe.classList.toggle("active");
    const ogretmenler = result.ogretmenler;

    ogretmenler.forEach(ogretmen => {
      const ogretmenListesi = document.getElementsByClassName("ogretmenList")[0];
      const ogretmenItem = document.createElement("div");

      ogretmenItem.id = ogretmen.ogretmen_no;
      ogretmenItem.classList.add("ogretmen");

      ogretmenItem.innerHTML = `
          <div class="text">
              <span id="ogretmen_no">${ogretmen.ogretmen_no}</span>
              <span>${ogretmen.ogretmen_adi} ${ogretmen.ogretmen_soyadi}</span>
          </div>
          <div class="icons">
              <i class="fa-solid fa-user-xmark" onclick="ogretmenSil(${ogretmen.ogretmen_no}, ${dersId})"></i>
          </div>
      `;

      // eğer öğrenci zaten listede varsa ekleme
      if (ogretmenListesi.children.namedItem(ogretmen.ogretmen_no) === null) {
        ogretmenListesi.appendChild(ogretmenItem);
      }
    });
  } else {
    Toast.fire({
      icon: "error",
      title: result.message,
    });
  }
}

function closeOgretmenListe() {
  const ogretmenListe = document.getElementsByClassName("ogretmenListe")[0];
  ogretmenListe.classList.remove("active");
  // sayfayı yeniden yükle
  location.reload();
}

function closeOgretmenEkle() {
  const ogretmenEkle = document.getElementsByClassName("ogretmenEkle")[0];
  ogretmenEkle.classList.remove("active");
}

var searchInput = document.getElementById("searchOgretmen");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase(); // Arama metnini küçük harfe çevir
  const ogretmenler = document.querySelectorAll("#ogretmenList .ogretmen"); // ogretmenList altındaki ogretmen sınıfını seç

  let found = false; // Sonuç olup olmadığını kontrol etmek için

  // Daha önce oluşturulmuş "Ders bulunamadı" öğesini bul veya oluştur
  let noResultsItem = document.getElementById("noResults");
  if (!noResultsItem) {
    const noResultsDiv = document.createElement("span");
    noResultsDiv.id = "noResults";
    noResultsDiv.textContent = "Öğretmen bulunamadı"; // Mesaj metni
    noResultsDiv.style.color = "#fff"; // Mesaj stilini ayarla
    noResultsDiv.style.textAlign = "left"; // Mesaj hizalam
    document.getElementById("ogrenciList").appendChild(noResultsDiv); // Listeye ekle
  }

  ogretmenler.forEach(ogretmen => {
    const textElement = ogretmen.querySelector(".text span:nth-child(2)"); // İsim içeren span öğesini seç
    const ogretmenAdi = textElement ? textElement.textContent.toLowerCase() : ""; // Öğrenci adını al

    if (ogretmenAdi.includes(filter)) {
      ogretmen.style.display = ""; // Öğrenciyi göster
      found = true; // En az bir sonuç bulundu
    } else {
      ogretmen.style.display = "none"; // Öğrenciyi gizle
    }
  });

  // Hiçbir sonuç bulunamadıysa mesajı göster, aksi halde gizle
  if (!found) {
    noResultsItem.style.display = "list-item";
  } else {
    noResultsItem.style.display = "none";
  }
});

async function ogretmenSil(ogretmen_no, ders_id) {
  const response = await fetch("../../function/admin/ders_ogretmen_sil.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogretmen_no, ders_id }),
  });

  const result = await response.json();

  if (result.status === "success") {
    // tıklanan iconun üstündeki li'yi sil
    const ogretmenItem = document.getElementById(ogretmen_no);
    ogretmenItem.remove();

    const ogretmenListe = document.getElementsByClassName("ogretmenList")[0];
    // ogretmen listesi boş ise ogretmenListe'yi kapat
    if (ogretmenListe.children.length === 0) {
      closeOgretmenListe();
      // sayfayı yenile
      location.reload();
    }

    Toast.fire({
      icon: "success",
      title: result.message,
    });
  } else {
    Toast.fire({
      icon: "error",
      title: result.message,
    });
  }
}

var dersId;

let deletedStudents = [];

async function ogretmenEkle(event, ders_id) {
  dersId = ders_id;

  const ogretmenEkle = document.getElementsByClassName("ogretmenEkle")[0];
  ogretmenEkle.classList.toggle("active");

  const response = await fetch("../../function/admin/dersin_ogretmenleri.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ders_id: ders_id }),
  });

  const result = await response.json();

  // Dersi alan öğrencilerin listesini al
  const ogretmenler = result.ogretmenler;

  // Öğrencileri silmeden önce DOM'dan kaldırıyoruz
  const ogretmenlerToRemove = [];
  if (ogretmenler !== undefined) {
    ogretmenler.forEach(ogretmen => {
      const ogretmenItem = document.getElementById(ogretmen.ogretmen_no);
      if (ogretmenItem) {
        ogretmenlerToRemove.push(ogretmenItem); // Kaldırılacak öğrencileri işaretle
        deletedStudents.push(ogretmen.ogretmen_no); // Silinen öğrenciyi kaydet
      }
    });
    ogretmenlerToRemove.forEach(ogretmenItem => ogretmenItem.remove());
  }

  // Eğer listede öğrenci kalmadıysa mesaj göster
  const ogretmenBul = document.getElementsByClassName("ogretmenBul")[0];
  if (ogretmenBul.children.length === 0) {
    const ogretmenItem = document.createElement("div");
    ogretmenItem.classList.add("ogretmen");
    ogretmenItem.innerHTML = "<span>Öğretmen bulunamadı.</span>";
    ogretmenBul.appendChild(ogretmenItem);
  }

  // Silinen öğrencileri listeden kaldırıyoruz
  deletedStudents = [];
}

var searchInput = document.getElementById("searchOgretmen2");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase(); // Arama metnini küçük harfe çevir
  const ogretmenler = document.querySelectorAll("#ogretmenBul .ogretmen"); // ogretmenList altındaki ogretmen sınıfını seç
  console.log(ogretmenler);

  let found = false; // Sonuç olup olmadığını kontrol etmek için

  // Daha önce oluşturulmuş "Ders bulunamadı" öğesini bul veya oluştur
  let noResultsItem = document.getElementById("noResults");
  if (!noResultsItem) {
    const noResultsDiv = document.createElement("span");
    noResultsDiv.id = "noResults";
    noResultsDiv.textContent = "Öğretmen bulunamadı"; // Mesaj metni
    noResultsDiv.style.color = "#fff"; // Mesaj stilini ayarla
    noResultsDiv.style.textAlign = "left"; // Mesaj hizalam
    document.getElementById("ogrenciList").appendChild(noResultsDiv); // Listeye ekle
  }

  ogretmenler.forEach(ogretmen => {
    const textElement = ogretmen.querySelector(".text span:nth-child(2)"); // İsim içeren span öğesini seç
    const ogretmenAdi = textElement ? textElement.textContent.toLowerCase() : ""; // Öğrenci adını al

    if (ogretmenAdi.includes(filter)) {
      ogretmen.style.display = ""; // Öğrenciyi göster
      found = true; // En az bir sonuç bulundu
    } else {
      ogretmen.style.display = "none"; // Öğrenciyi gizle
    }
  });

  // Hiçbir sonuç bulunamadıysa mesajı göster, aksi halde gizle
  if (!found) {
    noResultsItem.style.display = "list-item";
  } else {
    noResultsItem.style.display = "none";
  }
});

async function dersEkle(event, ogretmen_no) {
  const response = await fetch("../../function/admin/ders_ogretmen_ekle.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogretmen_no, ders_id: dersId }),
  });

  const result = await response.json();

  if (result.status === "success") {
    const ogretmenItem = document.getElementById(ogretmen_no);
    ogretmenItem.remove();

    const ogretmenListe = document.getElementsByClassName("ogretmenBul")[0];
    if (ogretmenListe.children.length === 0) {
      closeOgretmenEkle();
    }

    Toast.fire({
      icon: "success",
      title: result.message,
    });
  } else {
    Toast.fire({
      icon: "error",
      title: result.message,
    });
  }
}
