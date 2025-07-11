<?php
// Plik: includes/class-fcm-frontend-admin.php

if (!defined('ABSPATH')) exit;

class FCM_Frontend_Admin {

    public function __construct() {
        add_shortcode('fc_mistrzaki_admin_panel', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'fc_mistrzaki_admin_panel')) {
            
            wp_register_script('fcm-frontend-script', false, ['jquery'], '3.4.0', true);
            
            // Przekazujemy dane z PHP do JavaScript
            $grupy_wiekowe = fcm_get_grupy_wiekowe();
            wp_localize_script('fcm-frontend-script', 'fcm_data', ['grupy' => $grupy_wiekowe]);

            $script = "
                jQuery(document).ready(function($) {
                    const modal = $('#fcm-group-modal');
                    const modalTitle = $('#fcm-modal-title');
                    const modalGroupsContainer = $('#fcm-modal-groups');
                    const closeModalBtn = $('#fcm-modal-close-btn');

                    // Logika otwierania okna
                    $('body').on('click', '#trening-calendar td:not(.pad)', function(e) {
                        e.preventDefault(); 

                        const cell = $(this);
                        const date = cell.data('date');
                        const formattedDate = cell.data('date-formatted');

                        modalTitle.text('Wybierz grupę na ' + formattedDate);

                        modalGroupsContainer.empty();
                        const baseUrl = new URL(window.location.href);
                        baseUrl.searchParams.set('trening_date', date);

                        $.each(fcm_data.grupy, function(key, label) {
                            const groupUrl = new URL(baseUrl.toString());
                            groupUrl.searchParams.set('grupa', key);
                            const button = $('<a></a>')
                                .attr('href', groupUrl.toString())
                                .addClass('button button-primary')
                                .text(label);
                            modalGroupsContainer.append(button);
                        });

                        modal.addClass('is-visible');
                    });

                    // Logika zamykania okna
                    function closeModal() {
                        modal.removeClass('is-visible');
                    }

                    closeModalBtn.on('click', closeModal);
                    modal.on('click', function(e) {
                        if ($(e.target).is(modal)) {
                            closeModal();
                        }
                    });
                });
            ";
            wp_add_inline_script('fcm-frontend-script', $script);

            wp_enqueue_style(
                'fcm-frontend-styles',
                FCM_PLUGIN_URL . 'assets/css/frontend-admin.css',
                [],
                '3.2.1'
            );
            wp_enqueue_style('dashicons');

            wp_enqueue_script('fcm-frontend-script');

            // Enqueue new frontend-panel.js for player panel navigation
            wp_enqueue_script(
                'fcm-player-panel-script',
                FCM_PLUGIN_URL . 'assets/js/frontend-panel.js',
                ['jquery'], // Depends on jQuery
                '1.0.0', // Version
                true // Load in footer
            );
        }
    }

    public function render_shortcode() {
        ob_start();

        if (current_user_can('manage_options')) {
            $this->render_admin_panel();
        } else {
            echo '<h3>Zaloguj się, aby uzyskać dostęp do panelu</h3>';
            wp_login_form(['redirect' => get_permalink()]);
        }

        return ob_get_clean();
    }

    private function render_admin_panel() {
        $current_action = $_GET['fcm_action'] ?? 'calendar'; // Domyślna akcja to kalendarz
        ?>
        <div class="fcm-frontend-admin-wrapper">
            <div class="fcm-sidebar-nav">
                <div class="fcm-sidebar-header">
                    <a href="<?php echo home_url(); ?>" class="fcm-logo-link"><img src="<?php echo FCM_PLUGIN_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="fcm-logo"></a>
                    <button class="fcm-sidebar-toggle"><span class="dashicons dashicons-menu"></span></button>
                </div>
                <a href="<?php echo esc_url(add_query_arg('fcm_action', 'calendar')); ?>" class="button <?php echo ($current_action === 'calendar') ? 'button-primary' : ''; ?>"><span class="dashicons dashicons-calendar-alt"></span><span class="fcm-nav-text">Kalendarz Treningów</span></a><a href="<?php echo esc_url(add_query_arg('fcm_action', 'schedule')); ?>" class="button <?php echo ($current_action === 'schedule') ? 'button-primary' : ''; ?>"><span class="dashicons dashicons-clock"></span><span class="fcm-nav-text">Harmonogram Zajęć</span></a><a href="<?php echo esc_url(add_query_arg('fcm_action', 'players_admin')); ?>" class="button <?php echo ($current_action === 'players_admin') ? 'button-primary' : ''; ?>"><span class="dashicons dashicons-groups"></span><span class="fcm-nav-text">Zawodnicy</span></a><a href="<?php echo esc_url(add_query_arg('fcm_action', 'parents_admin')); ?>" class="button <?php echo ($current_action === 'parents_admin') ? 'button-primary' : ''; ?>"><span class="dashicons dashicons-admin-users"></span><span class="fcm-nav-text">Rodzice</span></a><a href="<?php echo esc_url(add_query_arg('fcm_action', 'announcements_admin')); ?>" class="button <?php echo ($current_action === 'announcements_admin') ? 'button-primary' : ''; ?>"><span class="dashicons dashicons-megaphone"></span><span class="fcm-nav-text">Ogłoszenia</span></a><a href="<?php echo wp_logout_url(home_url()); ?>" class="button fcm-logout-button"><span class="dashicons dashicons-lock"></span><span class="fcm-nav-text">Wyloguj się</span></a>
            </div>
            <div class="fcm-content-area">
                <?php
                switch ($current_action) {
                    case 'schedule':
                        include FCM_PLUGIN_PATH . 'templates/frontend/trainer-schedule-admin.php';
                        break;
                    case 'attendance':
                        if (isset($_GET['trening_date']) && isset($_GET['grupa'])) {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-attendance.php';
                        } elseif (isset($_GET['trening_date'])) {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-groups.php';
                        }
                        else {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-calendar.php';
                        }
                        break;
                    case 'players': // This was for trainer's player management, keep it separate if needed, or merge.
                        $player_action = $_GET['player_action'] ?? 'list';
                        if ($player_action === 'edit' || $player_action === 'add') {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-player-form.php';
                        } else {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-players-list.php';
                        }
                        break;
                    case 'players_admin': // New case for admin-level player management
                        $action = $_GET['action'] ?? 'list';
                        if ($action === 'edit' || $action === 'add') {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-zawodnicy-form.php';
                        }
                        else {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-zawodnicy-list.php';
                        }
                        break;
                    case 'parents_admin': // New case for admin-level parent management
                        $action = $_GET['action'] ?? 'list';
                        if ($action === 'pending') {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-rodzice-pending.php';
                        } elseif ($action === 'assign') {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-rodzice-assign.php';
                        }
                        else {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-rodzice-list.php';
                        }
                        break;
                    case 'announcements_admin': // New case for admin-level announcements
                        include FCM_PLUGIN_PATH . 'templates/frontend/trainer-announcements.php';
                        break;
                    case 'calendar':
                    default:
                        if (isset($_GET['trening_date']) && isset($_GET['grupa'])) {
                            include FCM_PLUGIN_PATH . 'templates/admin/treningi-attendance.php';
                        } elseif (isset($_GET['trening_date'])) {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-groups.php';
                        }
                        else {
                            include FCM_PLUGIN_PATH . 'templates/frontend/trainer-calendar.php';
                        }
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
}