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

// Dropdown toggle in sidebar (DOMContentLoaded ensures elements exist)
document.addEventListener('DOMContentLoaded', function () {
    // Ensure menus start hidden
    document.querySelectorAll('.sidebar .dropdown .dropdown-menu').forEach(function (menu) {
        menu.classList.remove('show');
    });

    document.querySelectorAll('.sidebar .dropdown .dropdown-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = this.nextElementSibling;
            if (menu) {
                // Close other open menus
                document.querySelectorAll('.sidebar .dropdown .dropdown-menu.show').forEach(function (openMenu) {
                    if (openMenu !== menu) openMenu.classList.remove('show');
                });
                menu.classList.toggle('show');
                this.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        const isInsideDropdown = e.target.closest('.sidebar .dropdown');
        if (!isInsideDropdown) {
            document.querySelectorAll('.sidebar .dropdown .dropdown-menu.show').forEach(function (openMenu) {
                openMenu.classList.remove('show');
            });
            document.querySelectorAll('.sidebar .dropdown .dropdown-toggle[aria-expanded="true"]').forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
        }
    });
});
