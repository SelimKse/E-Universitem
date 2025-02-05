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
  var arrow =
    event.target.querySelector(".arrow") ||
    event.target.parentElement.querySelector(".arrow") ||
    event.target.children[2]; // Ok öğesini seç

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
          console.error(
            "İconlar yüklenirken tekrar bir sorun oluştu."
          );
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

  const sınıflarList = document.getElementById("sınıflar");
  const sınıflar = sınıflarList.getElementsByTagName("li");

  // Eğer sınıf yoksa, "Sınıf bulunamadı" mesajını içeren bir <li> ekle
  if (sınıflar.length === 0) {
    const noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults"; // Kimlik ataması yap
    noResultsItem.textContent = "Sınıf bulunamadı"; // Mesajı ayarla
    noResultsItem.style.display = "list-item"; // Görünür yap
    sınıflarList.appendChild(noResultsItem); // Listeye ekle

    const searchInput = document.getElementById("search");
    searchInput.setAttribute("disabled", "disabled"); // Arama girişini devre dışı bırak
  }
});

// ------------------------------------------------------------------------------------------ \\

// Search input element
var searchInput = document.getElementById("search");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase(); // Arama metnini küçük harfe çevir
  const sınıflarList = document.getElementById("sınıflar");
  const sınıflar = sınıflarList.getElementsByTagName("li");
  let found = false; // Her aramada sonuç olup olmadığını kontrol etmek için bir değişken

  // Eğer daha önce oluşturulmuşsa "Sınıf bulunamadı" li'sini bulalım
  let noResultsItem = document.getElementById("noResults");

  // Eğer yoksa, yeni bir li oluşturalım
  if (!noResultsItem) {
    noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults";
    noResultsItem.textContent = "Sınıf bulunamadı";
    noResultsItem.style.display = "none"; // Başlangıçta gizli
    noResultsItem.style.color = "#000"; // Mesaj stilini ayarlayabilirsiniz
    noResultsItem.style.textAlign = "left"; // Ortalayın
    sınıflarList.appendChild(noResultsItem); // li öğesini sınıf listesinin sonuna ekle
  }

  for (let i = 0; i < sınıflar.length; i++) {
    const spanElement = sınıflar[i].getElementsByTagName("span")[0];
    const sınıfAdi = spanElement
      ? spanElement.textContent || spanElement.innerText
      : ""; // Sınıf ismini al veya boş bırak

    // Eğer sınıf ismi arama metnini içeriyorsa
    if (sınıfAdi.toLowerCase().includes(filter)) {
      sınıflar[i].style.display = ""; // Görüntüle
      found = true; // En az bir sonuç bulundu
    } else {
      sınıflar[i].style.display = "none"; // Gizle
    }
  }

  // Hiçbir sonuç bulunamadıysa "Sınıf bulunamadı" li öğesini göster, aksi halde gizle
  if (!found) {
    noResultsItem.style.display = "list-item";
  } else {
    noResultsItem.style.display = "none";
  }
});
// ------------------------------------------------------------------------------------------ \\
// İcon İşlemleri

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

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\

var sınıf_id;

function editSınıf(event, id) {
  const popup = document.getElementsByClassName("editBox")[0];
  popup.classList.add("active");
  sınıf_id = id;
}

async function deleteSınıf(event, id) {
  Swal.fire({
    title: "Sınıf Sil",
    text: "Sınıfı silmek istediğinizden emin misiniz?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sil",
    cancelButtonText: "İptal",
  }).then(async result => {
    if (result.isConfirmed) {
      const response = await fetch("../../../function/admin/sınıf_sil.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ sinif_id: id }),
      });

      const result = await response.json();

      if (result.status === "success") {
        Toast.fire({
          icon: "success",
          title: "Sınıf başarıyla silindi!",
        });

        // Sınıf listesini güncelle
        var sınıflarList = document.getElementById("sınıflar");
        var sınıflar = sınıflarList.getElementsByTagName("li");
        var sınıf = event.target.closest("li");

        sınıflarList.removeChild(sınıf);

        sınıflarList = document.getElementById("sınıflar");
        sınıflar = sınıflarList.getElementsByTagName("li");

        // Eğer hiç sınıf kalmadıysa "Sınıf bulunamadı" mesajını göster
        if (sınıflar.length === 0 || sınıflar.length === null) {
          const noResultsItem = document.createElement("li");
          noResultsItem.id = "noResults"; // Kimlik ataması yap
          noResultsItem.textContent = "Sınıf bulunamadı"; // Mesajı ayarla
          noResultsItem.style.display = "list-item"; // Görünür yap
          sınıflarList.appendChild(noResultsItem); // Listeye ekle

          const searchInput = document.getElementById("search");
          searchInput.setAttribute("disabled", "disabled"); // Arama girişini devre dışı bırak
        }
      } else {
        Toast.fire({
          icon: "error",
          title: `${result.message}`,
        });
      }
    }
  });
}

function closeEditForm(event) {
  const popup = document.getElementsByClassName("editBox")[0];
  popup.classList.remove("active");
}

async function updateSınıf(event) {
  const sınıf = document.getElementById(sınıf_id);
  const input = document.getElementById("editSınıfAdi").value;

  if (input === "") {
    Toast.fire({
      icon: "error",
      title: "Sınıf adı boş bırakılamaz!",
    });
    return;
  }

  const response = await fetch("../../../function/admin/sınıf_guncelle.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ sinif_id: sınıf_id, sinif_adi: input }),
  });

  const result = await response.json();

  if (result.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Sınıf başarıyla güncellendi!",
    });

    sınıf.getElementsByTagName("span")[0].textContent = input;
    closeEditForm();
  } else {
    Toast.fire({
      icon: "error",
      title: `${result.message}`,
    });
  }
}
