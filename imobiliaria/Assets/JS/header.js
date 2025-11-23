const userMenu = document.querySelector('.user-menu');
const avatar = document.getElementById('userAvatar');

if (avatar && userMenu) {
    avatar.addEventListener('click', () => {
        userMenu.classList.toggle('active');
    });

    // Fechar se clicar fora
    window.addEventListener('click', (e) => {
        if (!userMenu.contains(e.target)) {
            userMenu.classList.remove('active');
        }
    });
}
