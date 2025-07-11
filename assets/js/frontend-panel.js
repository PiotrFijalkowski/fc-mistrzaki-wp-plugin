document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.fcm-nav-menu a');
    const sections = document.querySelectorAll('.fcm-main-content .fcm-section-card');

    function showSection(id) {
        sections.forEach(section => {
            if (section.id === id) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            navLinks.forEach(item => item.classList.remove('active'));
            this.classList.add('active');

            const targetId = this.getAttribute('href').substring(1);
            if (targetId === '') { // For 'Panel Główny' which shows all initial cards
                sections.forEach(section => section.style.display = 'block');
            } else {
                showSection(targetId);
            }
        });
    });

    // Initial state: show all sections or the first one
    if (navLinks.length > 0) {
        navLinks[0].click(); // Simulate click on the first link (Panel Główny)
    }
});