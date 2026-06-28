document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".eye-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var input = btn.parentElement.querySelector("input");
      if (!input) return;
      input.type = input.type === "password" ? "text" : "password";
      btn.textContent = input.type === "password" ? "Show" : "Hide";
    });
  });

  document.querySelectorAll(".package input[type='radio']").forEach(function (radio) {
    radio.addEventListener("change", function () {
      document.querySelectorAll(".package").forEach(function (item) {
        item.classList.remove("selected");
      });
      radio.closest(".package").classList.add("selected");
    });
  });
});
