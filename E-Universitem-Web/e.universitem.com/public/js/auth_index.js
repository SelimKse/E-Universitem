function ogretmenGiris() {
  window.location.href = "/auth/ogretmen/giris.php";
}

function ogrenciGiris() {
  window.location.href = "/auth/ogrenci/giris.php";
}

function divClick(root) {
  const div = document.querySelector(`#${root}`);
  switch (root) {
    case "ogretmen-giris":
      ogretmenGiris();
      break;
    case "ogrenci-giris":
      ogrenciGiris();
      break;
  }
}

const boxes = document.querySelectorAll(".box");

boxes.forEach(box => {
  box.addEventListener("click", () => {
    divClick(box.id);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const theme = localStorage.getItem("theme") || "dark";
  console.log("Tema yüklendi:", theme);
  if (theme) {
    changeTheme(theme);
  }
});

function changeTheme(section) {
  const root = document.querySelector("html");
  if (section === "light") {
    root.classList.remove("dark");
    root.classList.add("light");
    localStorage.setItem("theme", "light");
  } else {
    root.classList.remove("light");
    root.classList.add("dark");
    localStorage.setItem("theme", "dark");
  }
}
