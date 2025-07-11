<?php
class FCM_Post_Handlers {
    public function __construct() {
        add_action('admin_post_fcm_add_edit_zawodnik', [$this, 'handle_add_edit_zawodnik']);
        add_action('admin_post_fcm_delete_zawodnik', [$this, 'handle_delete_zawodnik']);
        add_action('admin_post_fcm_save_attendance', [$this, 'handle_save_attendance']);

        // Nowe akcje dla rodziców
        add_action('admin_post_nopriv_fcm_register_parent', [$this, 'handle_parent_registration']);
        add_action('admin_post_nopriv_fcm_login', [$this, 'handle_parent_login']);
        add_action('admin_post_fcm_approve_powiazanie', [$this, 'handle_approve_powiazanie']);
        add_action('admin_post_fcm_assign_parent', [$this, 'handle_assign_parent']);
        add_action('admin_post_fcm_edit_parent', [$this, 'handle_edit_parent']);
        add_action('admin_post_fcm_delete_parent', [$this, 'handle_delete_parent']);
        add_action('admin_post_fcm_save_schedule_frontend', [$this, 'handle_save_schedule_frontend']);
        add_action('admin_post_fcm_delete_schedule_frontend', [$this, 'handle_delete_schedule_frontend']);
    }

    public function handle_save_schedule_frontend() {
        if (!current_user_can('manage_options') || !isset($_POST['fcm_schedule_nonce']) || !wp_verify_nonce($_POST['fcm_schedule_nonce'], 'fcm_save_schedule_action')) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $harmonogram_table = $wpdb->prefix . 'fcm_harmonogram';

        $grupa_wiekowa = sanitize_key($_POST['grupa_wiekowa']);
        $lokalizacja = sanitize_key($_POST['lokalizacja']);
        $dzien_tygodnia = sanitize_key($_POST['dzien_tygodnia']);
        $godzina = sanitize_text_field($_POST['godzina']);
        $schedule_id = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : 0;

        $data = compact('grupa_wiekowa', 'lokalizacja', 'dzien_tygodnia', 'godzina');

        if ($schedule_id > 0) {
            $result = $wpdb->update($harmonogram_table, $data, ['id' => $schedule_id]);
        } else {
            $result = $wpdb->insert($harmonogram_table, $data);
        }

        if ($result === false) {
            wp_redirect(add_query_arg('fcm_error', 'schedule_save_failed', wp_get_referer()));
        } else {
            wp_redirect(add_query_arg('fcm_notice', 'schedule_saved', wp_get_referer()));
        }
        exit;
    }

    public function handle_delete_schedule_frontend() {
        if (!current_user_can('manage_options') || !isset($_GET['schedule_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_schedule_' . $_GET['schedule_id'])) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $harmonogram_table = $wpdb->prefix . 'fcm_harmonogram';
        $schedule_id = intval($_GET['schedule_id']);

        $result = $wpdb->delete($harmonogram_table, ['id' => $schedule_id]);

        if ($result === false) {
            wp_redirect(add_query_arg('fcm_error', 'schedule_delete_failed', wp_get_referer()));
        } else {
            wp_redirect(add_query_arg('fcm_notice', 'schedule_deleted', wp_get_referer()));
        }
        exit;
    }

    public function handle_edit_parent() {
        if (!current_user_can('manage_options') || !isset($_POST['fcm_edit_parent_nonce']) || !wp_verify_nonce($_POST['fcm_edit_parent_nonce'], 'fcm_edit_parent_action')) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $rodzice_table = $wpdb->prefix . 'fcm_rodzice';
        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';

        $rodzic_id = intval($_POST['rodzic_id']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $zawodnicy_ids = isset($_POST['zawodnicy_ids']) ? array_map('intval', (array)$_POST['zawodnicy_ids']) : [];

        $data = ['email' => $email];
        if (!empty($password)) {
            $data['password'] = wp_hash_password($password);
        }

        $result = $wpdb->update($rodzice_table, $data, ['id' => $rodzic_id]);

        // Usuń wszystkie istniejące powiązania dla tego rodzica
        $wpdb->delete($powiazania_table, ['rodzic_id' => $rodzic_id]);

        // Dodaj nowe powiązania
        foreach ($zawodnicy_ids as $zawodnik_id) {
            $wpdb->insert($powiazania_table, [
                'rodzic_id' => $rodzic_id,
                'zawodnik_id' => $zawodnik_id,
                'status' => 'aktywne'
            ]);
        }

        if ($result === false) {
            wp_redirect(add_query_arg('fcm_error', 'edit_parent_failed', admin_url('admin.php?page=fc-mistrzaki-rodzice&action=edit&rodzic_id=' . $rodzic_id)));
        } else {
            wp_redirect(add_query_arg('fcm_notice', 'edit_parent_success', admin_url('admin.php?page=fc-mistrzaki-rodzice')));
        }
        exit;
    }

    public function handle_delete_parent() {
        if (!current_user_can('manage_options') || !isset($_GET['rodzic_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_parent_' . $_GET['rodzic_id'])) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $rodzice_table = $wpdb->prefix . 'fcm_rodzice';
        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';

        $rodzic_id = intval($_GET['rodzic_id']);

        $wpdb->delete($rodzice_table, ['id' => $rodzic_id]);
        $wpdb->delete($powiazania_table, ['rodzic_id' => $rodzic_id]);

        wp_redirect(add_query_arg('fcm_notice', 'delete_parent_success', admin_url('admin.php?page=fc-mistrzaki-rodzice')));
        exit;
    }

    public function handle_parent_login() {
        if (!isset($_POST['fcm_login'])) {
            wp_redirect(wp_get_referer());
            exit;
        }

        global $wpdb;
        $rodzice_table = $wpdb->prefix . 'fcm_rodzice';
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $redirect_url = isset($_POST['_wp_http_referer']) ? esc_url_raw($_POST['_wp_http_referer']) : wp_get_referer();

        $rodzic = $wpdb->get_row($wpdb->prepare("SELECT * FROM $rodzice_table WHERE email = %s", $email));

        if ($rodzic && wp_check_password($password, $rodzic->password)) {
            $_SESSION['fcm_rodzic_id'] = $rodzic->id;
            wp_redirect($redirect_url); // Przekieruj z powrotem na stronę, z której przyszło żądanie
            exit;
        } else {
            $_SESSION['fcm_login_error'] = 'Nieprawidłowy email lub hasło.';
            wp_redirect($redirect_url); // Przekieruj z powrotem na stronę, z której przyszło żądanie
            exit;
        }
    }

    public function handle_parent_registration() {
        if (!isset($_POST['fcm_register_nonce']) || !wp_verify_nonce($_POST['fcm_register_nonce'], 'fcm_register_action')) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $rodzice_table = $wpdb->prefix . 'fcm_rodzice';
        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';

        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $zawodnik_id = !empty($_POST['zawodnik_id']) ? intval($_POST['zawodnik_id']) : 0;

        if (!is_email($email)) {
            // Obsługa błędu - nieprawidłowy email
            wp_redirect(add_query_arg('fcm_error', 'invalid_email', wp_get_referer()));
            exit;
        }

        $existing_user = $wpdb->get_var($wpdb->prepare("SELECT id FROM $rodzice_table WHERE email = %s", $email));
        if ($existing_user) {
            // Obsługa błędu - email już istnieje
            wp_redirect(add_query_arg('fcm_error', 'email_exists', wp_get_referer()));
            exit;
        }

        $hashed_password = wp_hash_password($password);
        $result = $wpdb->insert($rodzice_table, ['email' => $email, 'password' => $hashed_password]);

        if ($result) {
            $rodzic_id = $wpdb->insert_id;
            if ($zawodnik_id > 0) {
                $wpdb->insert($powiazania_table, ['rodzic_id' => $rodzic_id, 'zawodnik_id' => $zawodnik_id, 'status' => 'oczekujace']);
            }
            // Wysłanie maila z potwierdzeniem
            wp_mail($email, 'Rejestracja w FC Mistrzaki', 'Twoje konto zostało utworzone i oczekuje na aktywację przez administratora.');
            wp_redirect(add_query_arg('fcm_success', 'registration_complete', wp_get_referer()));
        } else {
            wp_redirect(add_query_arg('fcm_error', 'registration_failed', wp_get_referer()));
        }
        exit;
    }

    public function handle_approve_powiazanie() {
        if (!current_user_can('manage_options') || !isset($_GET['powiazanie_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'approve_' . $_GET['powiazanie_id'])) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';
        $powiazanie_id = intval($_GET['powiazanie_id']);

        $wpdb->update($powiazania_table, ['status' => 'aktywne'], ['id' => $powiazanie_id]);

        wp_redirect(add_query_arg('fcm_notice', 'powiazanie_approved', admin_url('admin.php?page=fc-mistrzaki-rodzice&action=pending')));
        exit;
    }

    public function handle_assign_parent() {
        if (!current_user_can('manage_options') || !isset($_POST['fcm_assign_nonce']) || !wp_verify_nonce($_POST['fcm_assign_nonce'], 'fcm_assign_action')) {
            wp_die('Błąd bezpieczeństwa.');
        }

        global $wpdb;
        $rodzice_table = $wpdb->prefix . 'fcm_rodzice';
        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';

        $rodzic_email = sanitize_email($_POST['rodzic_email']);
        $zawodnik_id = intval($_POST['zawodnik_id']);

        $rodzic = $wpdb->get_row($wpdb->prepare("SELECT id FROM $rodzice_table WHERE email = %s", $rodzic_email));

        if ($rodzic) {
            $wpdb->insert($powiazania_table, ['rodzic_id' => $rodzic->id, 'zawodnik_id' => $zawodnik_id, 'status' => 'aktywne']);
            wp_redirect(add_query_arg('fcm_notice', 'assign_success', admin_url('admin.php?page=fc-mistrzaki-rodzice&action=assign')));
        } else {
            wp_redirect(add_query_arg('fcm_error', 'parent_not_found', admin_url('admin.php?page=fc-mistrzaki-rodzice&action=assign')));
        }
        exit;
    }

    public function handle_add_edit_zawodnik() {
        if (!current_user_can('manage_options') || !isset($_POST['fcm_zawodnik_nonce']) || !wp_verify_nonce($_POST['fcm_zawodnik_nonce'], 'fcm_zawodnik_nonce_action')) { wp_die('Błąd bezpieczeństwa.'); }
        global $wpdb;
        $table_name = $wpdb->prefix . 'fcm_zawodnicy';
        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';

        $id = isset($_POST['zawodnik_id']) ? intval($_POST['zawodnik_id']) : 0;
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

        $data = [
            'imie_nazwisko' => sanitize_text_field($_POST['imie_nazwisko']),
            'lokalizacja' => sanitize_key($_POST['lokalizacja']),
            'grupa_wiekowa' => sanitize_key($_POST['grupa_wiekowa']),
            'liczba_treningow' => intval($_POST['liczba_treningow']),
        ];

        if ($id > 0) {
            $result = $wpdb->update($table_name, $data, ['id' => $id]);
        } else {
            $result = $wpdb->insert($table_name, $data);
            $id = $wpdb->insert_id; // Pobierz ID nowo dodanego zawodnika
        }

        // Obsługa powiązania z rodzicem
        if ($id > 0) {
            // Usuń istniejące powiązania dla tego zawodnika
            $wpdb->delete($powiazania_table, ['zawodnik_id' => $id]);

            // Dodaj nowe powiązanie, jeśli wybrano rodzica
            if ($parent_id > 0) {
                $wpdb->insert($powiazania_table, [
                    'rodzic_id' => $parent_id,
                    'zawodnik_id' => $id,
                    'status' => 'aktywne'
                ]);
            }
        }

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
        
        // Pobieramy URL strony, z której wysłano formularz
        $redirect_url = wp_get_referer();

        // Jeśli z jakiegoś powodu referer jest pusty, ustawiamy domyślny URL admina
        if (!$redirect_url) {
            $redirect_url = admin_url('admin.php?page=fc-mistrzaki&trening_date=' . $data_treningu . '&grupa=' . sanitize_key($_POST['grupa']));
        }

        // Czyścimy stare parametry powiadomień, aby się nie dublowały
        $redirect_url = remove_query_arg(['fcm_notice', 'saved_count'], $redirect_url);

        // Dodajemy nowe parametry do URL powrotnego
        $redirect_url = add_query_arg([
            'fcm_notice' => 'attendance_saved',
            'saved_count' => $changes_count
        ], $redirect_url);

        wp_redirect($redirect_url);
        exit;
    }
}
