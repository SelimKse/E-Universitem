function menuFunction() {
  var menu = document.getElementsByClassName("mobile-menu")[0];
  menu.classList.toggle("show");
}

function settingsMenuButton(event) {
  const dropdown = document.querySelector(".dropdown-menu");
  dropdown.classList.toggle("show"); // Menü görünürlüğünü değiştir
}

function changeTheme(section) {
  const root = document.querySelector("html");
  if (section === "light") {
    root.classList.remove("dark");
    root.classList.add("light");
    localStorage.setItem("theme", "light");

    document.querySelector("#logo").src = "/assets/img/logo-siyah.png";
  } else {
    root.classList.remove("light");
    root.classList.add("dark");
    localStorage.setItem("theme", "dark");
    document.querySelector("#logo").src = "/assets/img/logo-renkli-2.png";
  }
}

function login() {
  window.location.href = "/auth/index.php";
}

document.addEventListener("DOMContentLoaded", function () {
  const theme = localStorage.getItem("theme") || "dark";
  if (theme) {
    changeTheme(theme);
  }
});

// ------------------------------------------------------------------------------------------ \\

function checkFontLoaded() {
  const font = new FontFaceObserver("Material Symbols Outlined");
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
          console.error("İconlar yüklenirken tekrar bir sorun oluştu. err_code: 51");
          retryLoad(); // Hata durumunda tekrar dene
        });
    }, 500); // 500 ms sonra yeniden dene
  }

  font
    .load()
    .then(hideLoader)
    .catch(() => {
      console.error("İconlar yüklenirken bir sorun oluştu. err_code: 51");
      retryLoad();
    });
}

// Sayfa tamamen yüklendiğinde font kontrolü yap
window.addEventListener("load", () => {
  checkFontLoaded();
});

// ------------------------------------------------------------------------------------------ \\
const menuToggle = document.getElementById("mobile-menu");
const menu = document.getElementById("menu-button");

// Menüdeki bir a etiketine tıklandığında menüyü kapat
document.querySelectorAll("#links ul li a").forEach(item => {
  item.addEventListener("click", () => {
    menuToggle.classList.toggle("show");
  });
});
