<?php
class FCM_Shortcode {
    public function __construct() {
        add_shortcode('fc_mistrzaki_panel', [$this, 'render_shortcode_panel']);
    }

    public function render_shortcode_panel() {
        global $wpdb;
        $login_error = '';

        if (isset($_GET['fcm_logout'])) {
            unset($_SESSION['fcm_zawodnik_id']);
            wp_redirect(get_permalink());
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fcm_login'])) {
            $zawodnik_id = intval($_POST['fcm_zawodnik_id']);
            $password = $_POST['fcm_password'];

            if ($password === '123') {
                $zawodnik = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}fcm_zawodnicy WHERE id = %d", $zawodnik_id));
                if ($zawodnik) {
                    $_SESSION['fcm_zawodnik_id'] = $zawodnik->id;
                } else {
                    $login_error = 'Nieprawidłowe ID zawodnika.';
                }
            } else {
                $login_error = 'Nieprawidłowe hasło.';
            }
        }

        ob_start();

        if (isset($_SESSION['fcm_zawodnik_id'])) {
            $zawodnik_id = $_SESSION['fcm_zawodnik_id'];
            include FCM_PLUGIN_PATH . 'templates/shortcode/panel-zawodnik.php';
        } else {
            include FCM_PLUGIN_PATH . 'templates/shortcode/panel-login.php';
        }

        return ob_get_clean();
    }
}
