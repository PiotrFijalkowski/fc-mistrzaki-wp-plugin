<?php // templates/frontend/trainer-schedule.php
if (!defined('ABSPATH')) exit;
global $wpdb;
$harmonogram_table = $wpdb->prefix . 'fcm_harmonogram';

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

    <button id="add-schedule-btn" class="button button-primary">Dodaj Harmonogram</button>

    <div id="fcm-schedule-modal" class="fcm-modal-overlay">
        <div class="fcm-modal-content">
            <div class="fcm-modal-header">
                <h3 id="fcm-modal-title">Dodaj/Edytuj Harmonogram</h3>
                <button class="fcm-modal-close" id="fcm-schedule-modal-close-btn">&times;</button>
            </div>
            <div class="fcm-modal-body">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fcm-form">
                    <input type="hidden" name="action" value="fcm_save_schedule_frontend">
                    <?php wp_nonce_field('fcm_save_schedule_action', 'fcm_schedule_nonce'); ?>
                    <input type="hidden" name="schedule_id" id="schedule_id" value="">
                    <div class="fcm-form-group">
                        <label for="grupa_wiekowa">Grupa wiekowa</label>
                        <select name="grupa_wiekowa" id="grupa_wiekowa" required class="fcm-form-control">
                            <option value="">-- Wybierz --</option>
                            <?php foreach ($grupy as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fcm-form-group">
                        <label for="lokalizacja">Lokalizacja</label>
                        <select name="lokalizacja" id="lokalizacja" required class="fcm-form-control">
                            <option value="">-- Wybierz --</option>
                            <?php foreach ($lokalizacje as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fcm-form-group">
                        <label for="dzien_tygodnia">Dzień tygodnia</label>
                        <select name="dzien_tygodnia" id="dzien_tygodnia" required class="fcm-form-control">
                            <option value="">-- Wybierz --</option>
                            <?php foreach ($dni_tygodnia as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fcm-form-group">
                        <label for="godzina">Godzina</label>
                        <input type="time" name="godzina" id="godzina" value="" required class="fcm-form-control">
                    </div>
                    <?php submit_button('Dodaj Harmonogram', 'primary', 'fcm_save_schedule'); ?>
                </form>
            </div>
        </div>
    </div>

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
                            <button type="button" class="button fcm-edit-schedule-btn" data-schedule-id="<?php echo esc_attr($harmonogram->id); ?>" data-grupa-wiekowa="<?php echo esc_attr($harmonogram->grupa_wiekowa); ?>" data-lokalizacja="<?php echo esc_attr($harmonogram->lokalizacja); ?>" data-dzien-tygodnia="<?php echo esc_attr($harmonogram->dzien_tygodnia); ?>" data-godzina="<?php echo esc_attr($harmonogram->godzina); ?>">Edytuj</button>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=fcm_delete_schedule_frontend&schedule_id=' . $harmonogram->id), 'delete_schedule_' . $harmonogram->id); ?>" class="button button-danger fcm-delete-schedule-btn">Usuń</a>
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