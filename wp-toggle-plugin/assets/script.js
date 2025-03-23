document.addEventListener("DOMContentLoaded", function () {
    let toggleButton = document.getElementById("toggleButton");
    let toggleContent = document.getElementById("toggleContent");

    if (toggleButton && toggleContent) {
        toggleButton.addEventListener("click", function () {
            if (toggleContent.style.display === "none" || toggleContent.style.display === "") {
                toggleContent.style.display = "block";
                toggleButton.textContent = "Hide";
            } else {
                toggleContent.style.display = "none";
                toggleButton.textContent = "Show";
            }
        });
    }
});