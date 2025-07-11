<?php
// Plik: templates/frontend/trainer-attendance.php

if (!defined('ABSPATH')) exit;
global $wpdb;
$date = sanitize_text_field($_GET['trening_date']);
$grupa_key = sanitize_text_field($_GET['grupa']);
$lokalizacja_key = isset($_GET['lokalizacja']) ? sanitize_key($_GET['lokalizacja']) : '';
$grupa_label = fcm_get_grupy_wiekowe()[$grupa_key] ?? 'Nieznana grupa';
$zawodnicy_table_name = $wpdb->prefix . 'fcm_zawodnicy';
$obecnosci_table_name = $wpdb->prefix . 'fcm_obecnosci';
?>
<div class="wrap">
    <h2>Lista obecności - <?php echo esc_html(date_i18n('d.m.Y', strtotime($date))) . ' - ' . esc_html($grupa_label); ?></h2>
    <p><a href="<?php echo esc_url(remove_query_arg(['grupa', 'lokalizacja'])); ?>" class="button">&laquo; Powrót do wyboru grupy</a></p>
    
    <div class="lokalizacja-selection">
        <?php
        $base_url = add_query_arg(['trening_date' => $date, 'grupa' => $grupa_key]);
        echo '<a href="' . esc_url($base_url) . '" class="button ' . (empty($lokalizacja_key) ? 'button-primary' : '') . '">Wszystkie</a>';
        foreach (fcm_get_lokalizacje() as $key => $label) {
            $url = add_query_arg('lokalizacja', $key, $base_url);
            echo '<a href="' . esc_url($url) . '" class="button ' . ($lokalizacja_key == $key ? 'button-primary' : '') . '">' . esc_html($label) . '</a>';
        }
        ?>
    </div>

    <?php
    $query = "SELECT * FROM $zawodnicy_table_name WHERE grupa_wiekowa = %s";
    $params = [$grupa_key];
    if (!empty($lokalizacja_key)) {
        $query .= " AND lokalizacja = %s";
        $params[] = $lokalizacja_key;
    }
    $query .= " ORDER BY imie_nazwisko ASC";
    $zawodnicy = $wpdb->get_results($wpdb->prepare($query, ...$params));
    
    $obecnosci_today_raw = $wpdb->get_results($wpdb->prepare("SELECT zawodnik_id FROM $obecnosci_table_name WHERE data_treningu = %s", $date));
    $obecnosci_today = wp_list_pluck($obecnosci_today_raw, 'zawodnik_id');

    if ($zawodnicy) {
        echo '<form method="post" action="' . esc_url(get_permalink()) . '">';
        echo '<input type="hidden" name="action" value="fcm_save_attendance">';
        echo '<input type="hidden" name="data_treningu" value="' . esc_attr($date) . '">';
        echo '<input type="hidden" name="grupa" value="' . esc_attr($grupa_key) . '">';
        echo '<input type="hidden" name="lokalizacja" value="' . esc_attr($lokalizacja_key) . '">';
        wp_nonce_field('fcm_save_attendance_nonce_action', 'fcm_save_attendance_nonce');
        
        $displayed_players_ids = wp_list_pluck($zawodnicy, 'id');
        echo '<input type="hidden" name="displayed_players" value="' . esc_attr(implode(',', $displayed_players_ids)) . '">';

        echo '<table class="wp-list-table widefat fixed striped" id="lista-obecnosci">';
        echo '<thead><tr><th style="width: 50px;">Lp.</th><th>Imię i Nazwisko</th><th>Lokalizacja</th><th style="width: 150px;">Pozostało treningów</th><th style="width: 100px;">Obecność</th></tr></thead>
';
        echo '<tbody>';
        $index = 1;
        foreach ($zawodnicy as $zawodnik) {
            $is_present = in_array($zawodnik->id, $obecnosci_today);
            
            $row_class = '';
            $treningi = intval($zawodnik->liczba_treningow);
            if ($treningi <= 3 && $treningi > 1) $row_class = 'warning';
            if ($treningi === 1) $row_class = 'danger';
            if ($treningi === 0) $row_class = 'inactive';

            echo '<tr class="' . $row_class . ' ' . ($is_present ? 'present' : '') . '">';
            echo '<td>' . $index++ . '</td>';
            echo '<td>' . esc_html($zawodnik->imie_nazwisko) . '</td>';
            echo '<td>' . esc_html(fcm_get_lokalizacje()[$zawodnik->lokalizacja] ?? 'Brak') . '</td>';
            echo '<td class="treningi-count">' . esc_html($zawodnik->liczba_treningow) . '</td>';
            echo '<td><input type="checkbox" name="obecnosc[]" value="' . esc_attr($zawodnik->id) . '" ' . checked($is_present, true, false) . '></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        submit_button('Zapisz obecność');
        echo '</form>';
    } else {
        echo '<p>Brak zawodników w tej grupie i lokalizacji.</p>';
    }
    ?>
</div>