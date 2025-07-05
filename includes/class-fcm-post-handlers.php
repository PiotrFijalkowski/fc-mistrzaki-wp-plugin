<?php
class FCM_Post_Handlers {
    public function __construct() {
        add_action('admin_post_fcm_add_edit_zawodnik', [$this, 'handle_add_edit_zawodnik']);
        add_action('admin_post_fcm_delete_zawodnik', [$this, 'handle_delete_zawodnik']);
        add_action('admin_post_fcm_save_attendance', [$this, 'handle_save_attendance']);
    }

    public function handle_add_edit_zawodnik() {
        if (!current_user_can('manage_options') || !isset($_POST['fcm_zawodnik_nonce']) || !wp_verify_nonce($_POST['fcm_zawodnik_nonce'], 'fcm_zawodnik_nonce_action')) { wp_die('Błąd bezpieczeństwa.'); }
        global $wpdb;
        $table_name = $wpdb->prefix . 'fcm_zawodnicy';
        $id = isset($_POST['zawodnik_id']) ? intval($_POST['zawodnik_id']) : 0;
        $data = [
            'imie_nazwisko' => sanitize_text_field($_POST['imie_nazwisko']),
            'lokalizacja' => sanitize_key($_POST['lokalizacja']),
            'grupa_wiekowa' => sanitize_key($_POST['grupa_wiekowa']),
            'liczba_treningow' => intval($_POST['liczba_treningow']),
        ];
        $result = ($id > 0) ? $wpdb->update($table_name, $data, ['id' => $id]) : $wpdb->insert($table_name, $data);
        wp_redirect(add_query_arg('fcm_notice', ($result === false) ? 'error' : 'success', admin_url('admin.php?page=fc-mistrzaki-zawodnicy')));
        exit;
    }

    public function handle_delete_zawodnik() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!current_user_can('manage_options') || !isset($_GET['fcm_delete_nonce']) || !wp_verify_nonce($_GET['fcm_delete_nonce'], 'fcm_delete_zawodnik_nonce_action_' . $id) || $id === 0) { wp_die('Błąd bezpieczeństwa.'); }
        global $wpdb;
        $table_name = $wpdb->prefix . 'fcm_zawodnicy';
        $result = $wpdb->delete($table_name, ['id' => $id], ['%d']);
        wp_redirect(add_query_arg('fcm_notice', ($result === false) ? 'delete_error' : 'delete_success', admin_url('admin.php?page=fc-mistrzaki-zawodnicy')));
        exit;
    }

    public function handle_save_attendance() {
        if (!current_user_can('manage_options') || !isset($_POST['fcm_save_attendance_nonce']) || !wp_verify_nonce($_POST['fcm_save_attendance_nonce'], 'fcm_save_attendance_nonce_action')) { wp_die('Błąd bezpieczeństwa.'); }
        global $wpdb;
        $zawodnicy_table = $wpdb->prefix . 'fcm_zawodnicy';
        $obecnosci_table = $wpdb->prefix . 'fcm_obecnosci';
        $data_treningu = sanitize_text_field($_POST['data_treningu']);
        $displayed_players = isset($_POST['displayed_players']) ? explode(',', sanitize_text_field($_POST['displayed_players'])) : [];
        $checked_players = isset($_POST['obecnosc']) ? array_map('intval', (array)$_POST['obecnosc']) : [];
        
        if(empty($displayed_players)) {
            wp_redirect(admin_url('admin.php?page=fc-mistrzaki'));
            exit;
        }

        $obecnosci_before_raw = $wpdb->get_results($wpdb->prepare("SELECT zawodnik_id FROM $obecnosci_table WHERE data_treningu = %s AND zawodnik_id IN (" . implode(',', array_fill(0, count($displayed_players), '%d')) . ")", $data_treningu, ...$displayed_players));
        $players_present_before = wp_list_pluck($obecnosci_before_raw, 'zawodnik_id');

        $to_add = array_diff($checked_players, $players_present_before);
        $to_remove = array_diff($players_present_before, $checked_players);
        $changes_count = 0;

        if (!empty($to_add)) {
            foreach($to_add as $zawodnik_id) {
                $wpdb->query('START TRANSACTION');
                $wpdb->insert($obecnosci_table, ['zawodnik_id' => $zawodnik_id, 'data_treningu' => $data_treningu]);
                $wpdb->query($wpdb->prepare("UPDATE $zawodnicy_table SET liczba_treningow = liczba_treningow - 1 WHERE id = %d AND liczba_treningow > 0", $zawodnik_id));
                $wpdb->query('COMMIT');
                $changes_count++;
            }
        }

        if (!empty($to_remove)) {
            foreach($to_remove as $zawodnik_id) {
                $wpdb->query('START TRANSACTION');
                $wpdb->delete($obecnosci_table, ['zawodnik_id' => $zawodnik_id, 'data_treningu' => $data_treningu]);
                $wpdb->query($wpdb->prepare("UPDATE $zawodnicy_table SET liczba_treningow = liczba_treningow + 1 WHERE id = %d", $zawodnik_id));
                $wpdb->query('COMMIT');
                $changes_count++;
            }
        }
        
        $redirect_url = admin_url('admin.php?page=fc-mistrzaki');
        $redirect_url = add_query_arg([
            'trening_date' => $data_treningu,
            'grupa' => sanitize_key($_POST['grupa']),
            'lokalizacja' => sanitize_key($_POST['lokalizacja']),
            'fcm_notice' => 'attendance_saved',
            'saved_count' => $changes_count
        ], $redirect_url);
        
        wp_redirect($redirect_url);
        exit;
    }
}
