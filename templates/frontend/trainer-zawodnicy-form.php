<?php
// Plik: templates/frontend/trainer-zawodnicy-form.php

if (!defined('ABSPATH')) exit;
global $wpdb;
$zawodnik_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$zawodnik = null;
if ($zawodnik_id > 0) {
    $zawodnik = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fcm_zawodnicy WHERE id = %d", $zawodnik_id));
}
?>
<div class="wrap m100">
    <h1><?php echo $zawodnik ? 'Edytuj zawodnika' : 'Dodaj nowego zawodnika'; ?></h1>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="fcm_add_edit_zawodnik">
        <input type="hidden" name="zawodnik_id" value="<?php echo esc_attr($zawodnik_id); ?>">
        <?php wp_nonce_field('fcm_zawodnik_nonce_action', 'fcm_zawodnik_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="imie_nazwisko">Imię i Nazwisko</label></th>
                <td><input type="text" id="imie_nazwisko" name="imie_nazwisko" value="<?php echo esc_attr($zawodnik->imie_nazwisko ?? ''); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="lokalizacja">Lokalizacja</label></th>
                <td>
                    <select name="lokalizacja" id="lokalizacja" required>
                        <option value="">-- Wybierz --</option>
                        <?php
                        $lokalizacje = fcm_get_lokalizacje();
                        $selected_lokalizacja = $zawodnik->lokalizacja ?? '';
                        foreach ($lokalizacje as $key => $label) {
                            echo '<option value="' . esc_attr($key) . '"' . selected($selected_lokalizacja, $key, false) . '>' . esc_html($label) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="grupa_wiekowa">Grupa wiekowa</label></th>
                <td>
                    <select name="grupa_wiekowa" id="grupa_wiekowa" required>
                        <option value="">-- Wybierz --</option>
                        <?php
                        $grupy = fcm_get_grupy_wiekowe();
                        $selected_grupa = $zawodnik->grupa_wiekowa ?? '';
                        foreach ($grupy as $key => $label) {
                            echo '<option value="' . esc_attr($key) . '"' . selected($selected_grupa, $key, false) . '>' . esc_html($label) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="liczba_treningow">Liczba treningów</label></th>
                <td><input type="number" id="liczba_treningow" name="liczba_treningow" value="<?php echo esc_attr($zawodnik->liczba_treningow ?? 10); ?>" class="regular-text" min="0"></td>
            </tr>
            <tr>
                <th scope="row"><label for="parent_id">Przypisany Rodzic</label></th>
                <td>
                    <select name="parent_id" id="parent_id">
                        <option value="0">-- Brak --</option>
                        <?php
                        $rodzice_table = $wpdb->prefix . 'fcm_rodzice';
                        $powiazania_table = $wpdb->prefix . 'fcm_powiazania';
                        $all_parents = $wpdb->get_results("SELECT id, email FROM $rodzice_table");
                        $current_parent_id = 0;
                        if ($zawodnik_id > 0) {
                            $current_parent = $wpdb->get_row($wpdb->prepare("SELECT rodzic_id FROM $powiazania_table WHERE zawodnik_id = %d AND status = 'aktywne'", $zawodnik_id));
                            if ($current_parent) {
                                $current_parent_id = $current_parent->rodzic_id;
                            }
                        }
                        foreach ($all_parents as $parent) {
                            echo '<option value="' . esc_attr($parent->id) . '"' . selected($current_parent_id, $parent->id, false) . '>' . esc_html($parent->email) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button($zawodnik ? 'Zapisz zmiany' : 'Dodaj zawodnika'); ?>
    </form>
    <a href="<?php echo esc_url(add_query_arg(['fcm_action' => 'players_admin'], get_permalink())); ?>">&laquo; Powrót do listy</a>

    <?php if ($zawodnik) : ?>
    <div id="historia-obecnosci" style="margin-top: 40px;">
        <h3>Historia obecności</h3>
        <?php
        $obecnosci = $wpdb->get_results($wpdb->prepare("SELECT data_treningu FROM {$wpdb->prefix}fcm_obecnosci WHERE zawodnik_id = %d ORDER BY data_treningu DESC", $zawodnik_id));
        if ($obecnosci) {
            echo '<ul class="ul-disc" style="margin-left: 20px;">';
            foreach ($obecnosci as $obecnosc) {
                echo '<li>' . date_i18n('d F Y', strtotime($obecnosc->data_treningu)) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Brak odnotowanych obecności.</p>';
        }
        ?>
    </div>
    <?php endif; ?>
</div>