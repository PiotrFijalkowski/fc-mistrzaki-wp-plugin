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
        wp_enqueue_script('fc-mistrzaki-admin-script', false, ['jquery'], '3.2.0', true);
            $script = "
                jQuery(document).ready(function($) {
                    // Wyszukiwarka zawodników
                    $('#zawodnik-search').on('keyup', function() {
                        var value = $(this).val().toLowerCase();
                        $('#zawodnicy-table tbody tr').filter(function() { 
                            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1) 
                        });
                    });

                    // Sprawia, że cała komórka kalendarza jest klikalna
                    $('#trening-calendar td:not(.pad)').on('click', function(e) {
                        // Upewnij się, że nie kliknięto już na link w środku
                        if (e.target.tagName !== 'A' && e.target.tagName !== 'STRONG') {
                            var link = $(this).find('a');
                            if (link.length) {
                                window.location.href = link.attr('href');
                            }
                        }
                    });
                });
            ";
        wp_add_inline_script('fc-mistrzaki-admin-script', $script);
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
