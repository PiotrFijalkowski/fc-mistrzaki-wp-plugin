<?php
if (!defined('ABSPATH')) exit;

class FCM_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_admin_menu() {
        add_menu_page('FC Mistrzaki', 'FC Mistrzaki', 'manage_options', 'fc-mistrzaki', [$this, 'render_treningi_page'], 'dashicons-groups', 6);
        add_submenu_page('fc-mistrzaki', 'Treningi', 'Treningi', 'manage_options', 'fc-mistrzaki', [$this, 'render_treningi_page']);
        add_submenu_page('fc-mistrzaki', 'Zawodnicy', 'Zawodnicy', 'manage_options', 'fc-mistrzaki-zawodnicy', [$this, 'render_zawodnicy_page']);
        add_submenu_page('fc-mistrzaki', 'Rodzice', 'Rodzice', 'manage_options', 'fc-mistrzaki-rodzice', [$this, 'render_rodzice_page']);
        add_submenu_page('fc-mistrzaki', 'Ogłoszenia', 'Ogłoszenia', 'manage_options', 'fc-mistrzaki-announcements', [$this, 'render_announcements_page']);
        add_submenu_page('fc-mistrzaki', 'Harmonogram Zajęć', 'Harmonogram Zajęć', 'manage_options', 'fc-mistrzaki-schedule', [$this, 'render_schedule_page']);
    }

    public function render_schedule_page() {
        include FCM_PLUGIN_PATH . 'templates/admin/schedule.php';
    }

    public function render_announcements_page() {
        include FCM_PLUGIN_PATH . 'templates/admin/announcements.php';
    }

    public function render_rodzice_page() {
        $action = $_GET['action'] ?? 'list';
        if ($action === 'pending') {
            include FCM_PLUGIN_PATH . 'templates/admin/rodzice-pending.php';
        } elseif ($action === 'assign') {
            include FCM_PLUGIN_PATH . 'templates/admin/rodzice-assign.php';
        } elseif ($action === 'edit') {
            include FCM_PLUGIN_PATH . 'templates/admin/rodzice-edit.php';
        } else {
            include FCM_PLUGIN_PATH . 'templates/admin/rodzice-list.php';
        }
    }

    public function render_zawodnicy_page() {
        $action = $_GET['action'] ?? 'list';
        if ($action === 'edit' || $action === 'add') {
            include FCM_PLUGIN_PATH . 'templates/admin/zawodnicy-form.php';
        } else {
            include FCM_PLUGIN_PATH . 'templates/admin/zawodnicy-list.php';
        }
    }

    public function render_treningi_page() {
        if (isset($_GET['trening_date']) && isset($_GET['grupa'])) {
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-attendance.php';
        } elseif (isset($_GET['trening_date'])) {
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-groups.php';
        } else {
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-calendar.php';
        }
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'fc-mistrzaki') === false) return;

        // Enqueue Select2 assets
        wp_enqueue_style('select2', FCM_PLUGIN_URL . 'assets/css/select2/select2.min.css', [], '4.0.13');
        wp_enqueue_script('select2', FCM_PLUGIN_URL . 'assets/js/select2/select2.min.js', ['jquery'], '4.0.13', true);

        wp_enqueue_script('fc-mistrzaki-admin-script', FCM_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], '3.2.0', true);
        wp_enqueue_script('fcm-admin-select2-script', FCM_PLUGIN_URL . 'assets/js/admin-select2.js', ['jquery', 'select2'], '1.0.0', true);
        $calendar_styles = "
            .calendar-wrap { max-width: 900px; margin: 20px auto; }
            .calendar-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
            .calendar-title { margin: 0; font-size: 20px; }
            #trening-calendar { border-collapse: collapse; width: 100%; }
            #trening-calendar th, #trening-calendar td { border: 1px solid #ccc; padding: 0; text-align: left; vertical-align: top; height: 100px; }
            #trening-calendar th { text-align: center; padding: 10px; background: #f7f7f7; }
            #trening-calendar td a { display: block; width: 100%; height: 100%; padding: 8px; box-sizing: border-box; text-decoration: none; color: #2271b1; }
            #trening-calendar td a:hover { background: #f0f6fc; }
            #trening-calendar td.today { background-color: #fff9c4; font-weight: bold; }
            #trening-calendar td.past { background-color: #f1f1f1; }
            #trening-calendar td.past a { color: #999; }
            #trening-calendar td.trening { border: 2px solid #2271b1; font-weight: bold; }
            #trening-calendar td.pad { background-color: #f9f9f9; }
            .present { background-color: #dff0d8 !important; }
            #trening-calendar td:not(.pad) { cursor: pointer; }
        ";
        wp_add_inline_style('fc-mistrzaki-admin-script', $calendar_styles);
    }
}
