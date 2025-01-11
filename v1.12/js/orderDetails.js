function toggleOrderDetails(orderId) {
    const details = document.getElementById(`order-${orderId}`);
    if (details) {
        const wasHidden = details.style.display === 'none' || !details.style.display;
        const allDetails = document.querySelectorAll('.order-details');
        allDetails.forEach(detail => detail.style.display = 'none');
        details.style.display = wasHidden ? 'block' : 'none';
    }
}