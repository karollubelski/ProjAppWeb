function toggleCart() {
    const modal = document.getElementById("koszykModal");
    modal.style.display = modal.style.display === "block" ? "none" : "block";
  }

document.getElementById('koszykModal').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleCart();
    }
});