<?php
// Plik: includes/class-fcm-frontend-admin.php

if (!defined('ABSPATH')) exit;

class FCM_Frontend_Admin {

    public function __construct() {
        add_shortcode('fc_mistrzaki_admin_panel', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Ładuje skrypty i style na stronach front-endowych.
     */
    public function enqueue_assets() {
        global $post;
        // Sprawdzamy, czy na stronie jest nasz shortcode, aby nie ładować zasobów niepotrzebnie.
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'fc_mistrzaki_admin_panel')) {
            
            // Rejestrujemy i dodajemy nasz kod JavaScript.
            wp_register_script(
                'fcm-frontend-script', // Unikalna nazwa (handle)
                false, // Brak pliku źródłowego, bo dodamy kod inline
                ['jquery'], // Zależność od jQuery
                '3.3.3', // Wersja
                true // Ładuj w stopce
            );
            
            $script = "
                jQuery(document).ready(function($) {
                    // Sprawia, że cała komórka kalendarza jest klikalna
                    $('body').on('click', '#trening-calendar td:not(.pad)', function(e) {
                        // Ignoruj kliknięcie, jeśli celem jest już sam link lub jego zawartość
                        if ($(e.target).is('a') || $(e.target).closest('a').length) {
                            return;
                        }
                        
                        var link = $(this).find('a');
                        if (link.length) {
                            window.location.href = link.attr('href');
                        }
                    });
                });
            ";
            wp_add_inline_script('fcm-frontend-script', $script);

            // Ładujemy zewnętrzny arkusz stylów, kompilowany przez SASS.
            wp_enqueue_style(
                'fcm-frontend-styles',
                FCM_PLUGIN_URL . 'assets/css/frontend-admin.css', // Prawidłowa ścieżka do pliku
                [],
                '3.2.1' // Wersja
            );

            // Prosimy WordPressa o załadowanie skryptu.
            wp_enqueue_script('fcm-frontend-script');
        }
    }

    /**
     * Renderuje zawartość shortcode'u.
     */
    public function render_shortcode() {
        ob_start();

        if (current_user_can('manage_options')) {
            // Użytkownik jest adminem - pokazujemy panel
            $this->render_admin_panel();
        } else {
            // Użytkownik nie jest adminem lub nie jest zalogowany - pokazujemy formularz logowania
            echo '<h3>Zaloguj się, aby uzyskać dostęp do panelu</h3>';
            wp_login_form(['redirect' => get_permalink()]);
        }

        return ob_get_clean();
    }

    private function render_admin_panel() {
        // Ta logika jest podobna do tej z FCM_Admin, ale dostosowana do frontu
        if (isset($_GET['trening_date']) && isset($_GET['grupa'])) {
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-attendance.php';
        } elseif (isset($_GET['trening_date'])) {
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-groups.php';
        } else {
             include FCM_PLUGIN_PATH . 'templates/admin/treningi-calendar.php';
        }
    }
}
