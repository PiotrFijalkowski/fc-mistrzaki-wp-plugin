<?php // templates/admin/schedule.php
if (!defined('ABSPATH')) exit;
global $wpdb;
$harmonogram_table = $wpdb->prefix . 'fcm_harmonogram';

// Obsługa zapisu/edycji harmonogramu
if (isset($_POST['fcm_save_schedule']) && current_user_can('manage_options')) {
    if (isset($_POST['fcm_schedule_nonce']) && wp_verify_nonce($_POST['fcm_schedule_nonce'], 'fcm_save_schedule_action')) {
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
            echo '<div class="notice notice-error is-dismissible"><p>Błąd podczas zapisywania harmonogramu.</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Harmonogram został zapisany.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Błąd bezpieczeństwa. Harmonogram nie został zapisany.</p></div>';
    }
}

// Obsługa usuwania harmonogramu
if (isset($_GET['action']) && $_GET['action'] === 'delete_schedule' && current_user_can('manage_options')) {
    $schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;
    if ($schedule_id > 0 && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_schedule_' . $schedule_id)) {
        $result = $wpdb->delete($harmonogram_table, ['id' => $schedule_id]);
        if ($result === false) {
            echo '<div class="notice notice-error is-dismissible"><p>Błąd podczas usuwania harmonogramu.</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Harmonogram został usunięty.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Błąd bezpieczeństwa. Harmonogram nie został usunięty.</p></div>';
    }
}

$current_schedule = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_schedule') {
    $schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;
    if ($schedule_id > 0) {
        $current_schedule = $wpdb->get_row($wpdb->prepare("SELECT * FROM $harmonogram_table WHERE id = %d", $schedule_id));
    }
}

$lokalizacje = fcm_get_lokalizacje();
$grupy = fcm_get_grupy_wiekowe();
$dni_tygodnia = [
    'monday' => 'Poniedziałek',
    'tuesday' => 'Wtorek',
    'wednesday' => 'Środa',
    'thursday' => 'Czwartek',
    'friday' => 'Piątek',
    'saturday' => 'Sobota',
    'sunday' => 'Niedziela',
];

$harmonogramy = $wpdb->get_results("SELECT * FROM $harmonogram_table ORDER BY FIELD(dzien_tygodnia, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), godzina ASC");
?>
<div class="wrap">
    <h1>Harmonogram Zajęć</h1>

    <h2><?php echo $current_schedule ? 'Edytuj Harmonogram' : 'Dodaj Nowy Harmonogram'; ?></h2>
    <form method="post" action="" class="fcm-form">
        <?php wp_nonce_field('fcm_save_schedule_action', 'fcm_schedule_nonce'); ?>
        <?php if ($current_schedule) : ?>
            <input type="hidden" name="schedule_id" value="<?php echo $current_schedule->id; ?>">
        <?php endif; ?>
        <div class="fcm-form-group">
            <label for="grupa_wiekowa">Grupa wiekowa</label>
            <select name="grupa_wiekowa" id="grupa_wiekowa" required class="fcm-form-control">
                <option value="">-- Wybierz --</option>
                <?php foreach ($grupy as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($current_schedule->grupa_wiekowa ?? '', $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fcm-form-group">
            <label for="lokalizacja">Lokalizacja</label>
            <select name="lokalizacja" id="lokalizacja" required class="fcm-form-control">
                <option value="">-- Wybierz --</option>
                <?php foreach ($lokalizacje as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($current_schedule->lokalizacja ?? '', $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fcm-form-group">
            <label for="dzien_tygodnia">Dzień tygodnia</label>
            <select name="dzien_tygodnia" id="dzien_tygodnia" required class="fcm-form-control">
                <option value="">-- Wybierz --</option>
                <?php foreach ($dni_tygodnia as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($current_schedule->dzien_tygodnia ?? '', $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fcm-form-group">
            <label for="godzina">Godzina</label>
            <input type="time" name="godzina" id="godzina" value="<?php echo esc_attr($current_schedule->godzina ?? ''); ?>" required class="fcm-form-control">
        </div>
        <?php submit_button($current_schedule ? 'Zapisz Zmiany' : 'Dodaj Harmonogram', 'primary', 'fcm_save_schedule'); ?>
    </form>

    <h2>Istniejące Harmonogramy</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Grupa</th>
                <th>Lokalizacja</th>
                <th>Dzień Tygodnia</th>
                <th>Godzina</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($harmonogramy) : ?>
                <?php foreach ($harmonogramy as $harmonogram) : ?>
                    <tr>
                        <td><?php echo esc_html($grupy[$harmonogram->grupa_wiekowa] ?? $harmonogram->grupa_wiekowa); ?></td>
                        <td><?php echo esc_html($lokalizacje[$harmonogram->lokalizacja] ?? $harmonogram->lokalizacja); ?></td>
                        <td><?php echo esc_html($dni_tygodnia[$harmonogram->dzien_tygodnia] ?? $harmonogram->dzien_tygodnia); ?></td>
                        <td><?php echo esc_html($harmonogram->godzina); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=fc-mistrzaki-schedule&action=edit_schedule&schedule_id=' . $harmonogram->id); ?>" class="button">Edytuj</a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=fc-mistrzaki-schedule&action=delete_schedule&schedule_id=' . $harmonogram->id), 'delete_schedule_' . $harmonogram->id); ?>" class="button button-danger">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">Brak zdefiniowanych harmonogramów.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>