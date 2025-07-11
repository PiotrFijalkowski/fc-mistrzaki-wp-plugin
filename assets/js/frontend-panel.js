document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.fcm-nav-menu a');
    const sections = document.querySelectorAll('.fcm-main-content .fcm-section-card');

    // Sidebar toggle functionality
    const sidebarToggleBtn = document.querySelector('.fcm-sidebar-toggle');
    const sidebarNav = document.querySelector('.fcm-sidebar-nav');

    if (sidebarToggleBtn && sidebarNav) {
        sidebarToggleBtn.addEventListener('click', function() {
            sidebarNav.classList.toggle('collapsed');
        });
    }

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

    // Schedule Modal Logic
    const scheduleModal = document.getElementById('fcm-schedule-modal');
    const addScheduleBtn = document.getElementById('add-schedule-btn');
    const closeScheduleModalBtn = document.getElementById('fcm-schedule-modal-close-btn');
    const scheduleForm = scheduleModal ? scheduleModal.querySelector('form') : null;
    const scheduleIdInput = document.getElementById('schedule_id');
    const grupaWiekowaSelect = document.getElementById('grupa_wiekowa');
    const lokalizacjaSelect = document.getElementById('lokalizacja');
    const dzienTygodniaSelect = document.getElementById('dzien_tygodnia');
    const godzinaInput = document.getElementById('godzina');
    const modalTitle = document.getElementById('fcm-modal-title');

    if (addScheduleBtn && scheduleModal && closeScheduleModalBtn && scheduleForm) {
        addScheduleBtn.addEventListener('click', function() {
            // Clear form for adding new schedule
            scheduleForm.reset();
            scheduleIdInput.value = '';
            modalTitle.textContent = 'Dodaj Harmonogram';
            scheduleModal.classList.add('is-visible');
        });

        closeScheduleModalBtn.addEventListener('click', function() {
            scheduleModal.classList.remove('is-visible');
        });

        scheduleModal.addEventListener('click', function(e) {
            if (e.target === scheduleModal) {
                scheduleModal.classList.remove('is-visible');
            }
        });

        // Handle edit buttons
        document.querySelectorAll('.fcm-edit-schedule-btn').forEach(button => {
            button.addEventListener('click', function() {
                const data = this.dataset;
                scheduleIdInput.value = data.scheduleId;
                grupaWiekowaSelect.value = data.grupaWiekowa;
                lokalizacjaSelect.value = data.lokalizacja;
                dzienTygodniaSelect.value = data.dzienTygodnia;
                godzinaInput.value = data.godzina;
                modalTitle.textContent = 'Edytuj Harmonogram';
                scheduleModal.classList.add('is-visible');
            });
        });
    }
});