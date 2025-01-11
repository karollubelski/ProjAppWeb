function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('active');
    
    // Zamykanie menu po kliknięciu poza nim
    document.addEventListener('click', function closeMenu(e) {
        const userPanel = document.querySelector('.user-panel');
        if (!userPanel.contains(e.target)) {
            menu.classList.remove('active');
            document.removeEventListener('click', closeMenu);
        }
    });
}