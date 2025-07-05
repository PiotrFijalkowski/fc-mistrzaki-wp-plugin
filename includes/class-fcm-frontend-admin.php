<?php
// Plik: includes/class-fcm-frontend-admin.php

if (!defined('ABSPATH')) exit;

class FCM_Frontend_Admin {

    public function __construct() {
        add_shortcode('fc_mistrzaki_admin_panel', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function enqueue_styles() {
        // Dodajemy style tylko, gdy shortcode jest używany
      if (is_a(get_post(), 'WP_Post') && has_shortcode(get_post()->post_content, 'fc_mistrzaki_admin_panel')) {
              wp_enqueue_style(
                  'fcm-frontend-admin-styles',
                  FCM_PLUGIN_URL . 'assets/css/frontend-admin.css',
                  [],
                  '3.1.0'
              );
          }
      }

    public function render_shortcode() {
        ob_start();

        if (current_user_can('manage_options')) {
            // Użytkownik jest adminem - pokazujemy panel
            $this->render_admin_panel();
        } else {
            // Użytkownik nie jest adminem lub nie jest zalogowany - pokazujemy formularz logowania
            echo '<h3>Zaloguj się, aby uzyskać dostęp</h3>';
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