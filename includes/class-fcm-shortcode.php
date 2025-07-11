<?php
class FCM_Shortcode {
    public function __construct() {
        add_shortcode('fc_mistrzaki_panel', [$this, 'render_shortcode_panel']);
    }

    public function render_shortcode_panel() {
        if (isset($_GET['fcm_logout'])) {
            unset($_SESSION['fcm_rodzic_id']);
            wp_redirect(get_permalink());
            exit;
        }

        ob_start();

        if (isset($_SESSION['fcm_rodzic_id'])) {
            global $wpdb;
            $powiazania_table = $wpdb->prefix . 'fcm_powiazania';
            $rodzic_id = $_SESSION['fcm_rodzic_id'];

            $powiazanie = $wpdb->get_row($wpdb->prepare("SELECT * FROM $powiazania_table WHERE rodzic_id = %d AND status = 'aktywne'", $rodzic_id));

            if ($powiazanie) {
                $zawodnik_id = $powiazanie->zawodnik_id;
                include FCM_PLUGIN_PATH . 'templates/shortcode/panel-zawodnik.php';
            } else {
                echo '<p>Twoje konto nie jest jeszcze powiązane z żadnym zawodnikiem lub oczekuje na akceptację administratora.</p>';
                echo '<a href="' . add_query_arg('fcm_logout', 'true') . '">Wyloguj</a>';
            }
        } else {
            include FCM_PLUGIN_PATH . 'templates/shortcode/panel-login-register.php';
        }

        return ob_get_clean();
    }
}
