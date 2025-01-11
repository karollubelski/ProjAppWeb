function showOrderForm() {
    document.getElementById('orderFormModal').style.display = 'flex';
}

function hideOrderForm() {
    document.getElementById('orderFormModal').style.display = 'none';
}

document.querySelector('button[name="kup"]').addEventListener('click', function(e) {
    e.preventDefault();
    showOrderForm();
});