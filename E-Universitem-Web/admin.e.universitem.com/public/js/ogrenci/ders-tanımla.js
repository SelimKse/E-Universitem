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

// ----------------------------------------------------------------------------------------- \\

// Search işlemi için event listener
var searchInput = document.getElementById("search");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase().trim(); // Arama kutusundaki değeri al
  const ogrencilerList = document.getElementById("ogrenciler");
  const ogrenciler = ogrencilerList.getElementsByTagName("li");

  let found = false;
  let noResultsItem = document.getElementById("noResults");
  let noInputItem = document.getElementById("noInput");

  // Eğer "Öğrenci bulunamadı" mesajı yoksa oluştur
  if (!noResultsItem) {
    noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults";
    noResultsItem.textContent = "Öğrenci bulunamadı";
    noResultsItem.style.display = "none"; // Başlangıçta gizli
    ogrencilerList.appendChild(noResultsItem);
  }

  // "Lütfen öğrenci giriniz" mesajını sadece arama kutusu boşken ekleyin
  if (!noInputItem) {
    noInputItem = document.createElement("li"); // Bu sefer <li> olarak ekleyelim
    noInputItem.id = "noInput";
    noInputItem.textContent = "Lütfen öğrenci ismi veya numarası giriniz";
    noInputItem.style.display = "none"; // Başlangıçta gizli
    ogrencilerList.appendChild(noInputItem);
  }

  // Eğer arama kutusu tamamen boşsa:
  if (filter === "") {
    // Tüm liste elemanlarını gizle
    for (let i = 0; i < ogrenciler.length; i++) {
      ogrenciler[i].style.display = "none";
    }

    // "Lütfen öğrenci giriniz" mesajını göster
    noInputItem.style.display = "list-item"; // Görünür yap
    noResultsItem.style.display = "none"; // "Öğrenci bulunamadı" mesajını gizle
    return;
  }

  // "Lütfen öğrenci giriniz" mesajını sadece arama kutusu boşken göster
  noInputItem.style.display = "none";

  // Öğrenci listesi kontrolü
  for (let i = 0; i < ogrenciler.length; i++) {
    noInputItem.style.display = "none"; // "Lütfen öğrenci giriniz" mesajını gizle
    const ogrenciText = ogrenciler[i].textContent.toLowerCase();
    if (ogrenciText.includes(filter)) {
      ogrenciler[i].style.display = ""; // Eşleşenleri göster
      found = true; // Eşleşme bulundu
    } else {
      ogrenciler[i].style.display = "none"; // Eşleşmeyenleri gizle
    }
  }

  // Eğer eşleşme yoksa "Öğrenci bulunamadı" mesajını göster
  noResultsItem.style.display = found ? "none" : "list-item";
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
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

window.addEventListener("load", () => {
  checkFontLoaded();
  changeTheme();
});

document.addEventListener("DOMContentLoaded", function () {
  const ogrencilerList = document.getElementById("ogrenciler");
  const ogrenciler = ogrencilerList.getElementsByTagName("li");

  // Başlangıçta tüm öğrencileri gizle
  for (let i = 0; i < ogrenciler.length; i++) {
    ogrenciler[i].style.display = "none";
  }

  // "Lütfen öğrenci giriniz" mesajını oluştur
  let noInputItem = document.getElementById("noInput");
  if (!noInputItem) {
    noInputItem = document.createElement("li"); // <li> olarak ekleyelim
    noInputItem.id = "noInput";
    noInputItem.textContent = "Lütfen öğrenci ismi veya numarası giriniz";
    ogrencilerList.appendChild(noInputItem);
  }

  // "Öğrenci bulunamadı" mesajını oluştur
  let noResultsItem = document.getElementById("noResults");
  if (!noResultsItem) {
    noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults";
    noResultsItem.textContent = "Öğrenci bulunamadı";
    noResultsItem.style.display = "none"; // Başlangıçta gizli
    ogrencilerList.appendChild(noResultsItem);
  }

  // Tüm li etiketlerini seç
  const listItems = document.querySelectorAll("#ogrenciler > li");

  listItems.forEach(li => {
    li.addEventListener("click", async function (event) {
      // Eğer tıklama dersler kutusuna yapılmışsa, bunu engelle
      if (event.target.closest(".dersler")) {
        return; // Dersler kutusuna tıklanırsa hiçbir şey yapma
      }

      // Diğer tüm li öğelerinden active sınıfını kaldır
      listItems.forEach(item => {
        if (item !== li) {
          item.classList.remove("active");
        }
      });

      li.classList.toggle("active");

      const id = li.id;
      const ogrenci = await fetch("../../function/admin/ogrenci_bilgi.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          ogrenci_no: id,
        }),
      });

      const result = await ogrenci.json();
      let dersler = result.ogrenci.aldigi_dersler;

      // Eğer dersler bir string ise, bunu array'e dönüştür
      if (typeof dersler === "string") {
        try {
          dersler = JSON.parse(dersler); // String'i array'e dönüştür
        } catch (error) {
          console.error("Dersler array'a dönüştürülemedi:", error);
        }
      }

      if (dersler != null) {
        // Derslerin idlerini iste
        const derslerResponse = await fetch("../../function/admin/ders_isim_alma.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            dersler: dersler,
          }),
        });

        const derslerResult = await derslerResponse.json();
        const dersIds = derslerResult.data.map(ders => ders.ders_id); // ders_id'leri alıyoruz

        // Tıklanan li'nin altındaki checkbox'ları al
        const checkboxes = li.querySelectorAll(".input"); // Bu satırda sadece tıklanan li'yi hedef alıyoruz

        // Önce tüm checkbox'ların işaretini kaldırıyoruz
        checkboxes.forEach(checkbox => {
          checkbox.checked = false;
        });

        // Şimdi sadece tıklanan li'ye ait ders ID'lerine göre checkbox'ları işaretliyoruz
        checkboxes.forEach(checkbox => {
          if (dersIds.includes(parseInt(checkbox.value))) {
            checkbox.checked = true; // Ders ID'sine karşılık gelen checkbox'ı işaretliyoruz
          }
        });
      }
    });
  });
});

// ------------------------------------------------------------------------------------------ \\

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\

async function dersKaydet(event, id) {
  const checkboxes = document.querySelectorAll(".input");
  const selectedDersIds = [];
  checkboxes.forEach(function (checkbox) {
    if (checkbox.checked) {
      selectedDersIds.push(checkbox.value);
    }
  });

  const derskaydet = await fetch("../../function/admin/ogrenci_ders_tanımlama.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ogrenci_no: id,
      ders_id: selectedDersIds,
    }),
  });

  const result = await derskaydet.json();

  if (result.status === "success") {
    Toast.fire({
      icon: "success",
      title: result.message,
    });
  } else {
    Toast.fire({
      icon: "error",
      title: result.message,
    });
    console.log(result);
  }
}
