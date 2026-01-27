// Sidebar toggle for mobile
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarNav = document.querySelector('.sidebar');

sidebarToggle?.addEventListener('click', function () {
    sidebarNav.classList.toggle('show');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function (e) {
    if (window.innerWidth <= 900 && sidebarNav.classList.contains('show')) {
        if (!sidebarNav.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebarNav.classList.remove('show');
        }
    }
});
