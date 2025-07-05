<?php
/**
 * Plugin Name:       FC Mistrzaki Zarządzanie Treningami
 * Description:       Wtyczka do zarządzania zawodnikami i ich obecnością na treningach.
 * Version:           3.1.0
 * Author:            FestDev
 * Text Domain:       fc-mistrzaki
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('FCM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FCM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Główna klasa wtyczki, działająca jako kontroler.
 */
class FC_Mistrzaki_Manager_Main {

    private static $instance;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_plugin();
    }

    private function load_dependencies() {
        require_once FCM_PLUGIN_PATH . 'includes/class-fcm-db.php';
        require_once FCM_PLUGIN_PATH . 'includes/helpers.php';
        require_once FCM_PLUGIN_PATH . 'includes/class-fcm-admin.php';
        require_once FCM_PLUGIN_PATH . 'includes/class-fcm-post-handlers.php';
        require_once FCM_PLUGIN_PATH . 'includes/class-fcm-shortcode.php';
    }

    private function init_plugin() {
        register_activation_hook(__FILE__, ['FCM_DB', 'activate']);
        
        add_action('init', [$this, 'start_session'], 1);

        new FCM_Admin();
        new FCM_Post_Handlers();
        new FCM_Shortcode();
    }

    public function start_session() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
}

// Uruchomienie wtyczki
FC_Mistrzaki_Manager_Main::get_instance();