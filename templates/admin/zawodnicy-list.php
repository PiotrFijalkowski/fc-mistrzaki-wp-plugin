<?php
// Plik: templates/admin/zawodnicy-list.php

if (!defined('ABSPATH')) exit;
global $wpdb;
$zawodnicy_table_name = $wpdb->prefix . 'fcm_zawodnicy';
?>
<div class="wrap m100">
    <h1>Lista Zawodników</h1>
    <?php fcm_display_notices(); ?>

    <div class="zawodnicy-filters" style="margin-bottom: 20px;">
        <?php
        $filter_grupa = isset($_GET['filter_grupa']) ? sanitize_key($_GET['filter_grupa']) : '';
        $filter_lokalizacja = isset($_GET['filter_lokalizacja']) ? sanitize_key($_GET['filter_lokalizacja']) : '';
        ?>
        <div class="filter-row" style="margin-bottom: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
            <strong>Grupa:</strong>
            <a href="<?php echo remove_query_arg('filter_grupa'); ?>" class="button <?php echo empty($filter_grupa) ? 'button-primary' : ''; ?>">Wszystkie</a>
            <?php foreach(fcm_get_grupy_wiekowe() as $key => $label): ?>
                <a href="<?php echo add_query_arg('filter_grupa', $key); ?>" class="button <?php echo ($filter_grupa == $key) ? 'button-primary' : ''; ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="filter-row" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <strong>Lokalizacja:</strong>
            <a href="<?php echo remove_query_arg('filter_lokalizacja'); ?>" class="button <?php echo empty($filter_lokalizacja) ? 'button-primary' : ''; ?>">Wszystkie</a>
            <?php foreach(fcm_get_lokalizacje() as $key => $label): ?>
                <a href="<?php echo add_query_arg('filter_lokalizacja', $key); ?>" class="button <?php echo ($filter_lokalizacja == $key) ? 'button-primary' : ''; ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <a href="<?php echo admin_url('admin.php?page=fc-mistrzaki-zawodnicy&action=add'); ?>" class="page-title-action">Dodaj nowego</a>
    <input type="text" id="zawodnik-search" placeholder="Wyszukaj zawodnika..." style="margin-bottom: 15px; width: 100%; max-width: 400px; padding: 5px; float: right;">
    <table class="wp-list-table widefat fixed striped" id="zawodnicy-table">
        <thead><tr><th>ID</th><th>Imię i Nazwisko</th><th>Lokalizacja</th><th>Grupa</th><th>Pozostało treningów</th><th>Akcje</th></tr></thead>
        <tbody>
            <?php
            $query = "SELECT * FROM $zawodnicy_table_name";
            $where_clauses = [];
            $params = [];

            if (!empty($filter_grupa)) {
                $where_clauses[] = "grupa_wiekowa = %s";
                $params[] = $filter_grupa;
            }
            if (!empty($filter_lokalizacja)) {
                $where_clauses[] = "lokalizacja = %s";
                $params[] = $filter_lokalizacja;
            }

            if (!empty($where_clauses)) {
                $query .= " WHERE " . implode(' AND ', $where_clauses);
            }
            $query .= " ORDER BY imie_nazwisko ASC";
            
            $zawodnicy = $wpdb->get_results($wpdb->prepare($query, ...$params));

            if ($zawodnicy) {
                foreach ($zawodnicy as $zawodnik) {
                    $treningi = intval($zawodnik->liczba_treningow);
                    $style = '';
                    if ($treningi <= 3 && $treningi > 1) $style = 'background-color: #fcf8e3;';
                    if ($treningi === 1) $style = 'background-color: #f2dede;';
                    if ($treningi === 0) $style = 'background-color: #f0f0f0;';
                    $delete_url = wp_nonce_url(admin_url('admin-post.php?action=fcm_delete_zawodnik&id=' . $zawodnik->id),'fcm_delete_zawodnik_nonce_action_' . $zawodnik->id,'fcm_delete_nonce');
                    echo '<tr style="' . $style . '">';
                    echo '<td>' . $zawodnik->id . '</td>';
                    echo '<td>' . esc_html($zawodnik->imie_nazwisko) . '</td>';
                    echo '<td>' . esc_html(fcm_get_lokalizacje()[$zawodnik->lokalizacja] ?? 'Brak') . '</td>';
                    echo '<td>' . esc_html(fcm_get_grupy_wiekowe()[$zawodnik->grupa_wiekowa] ?? 'Brak') . '</td>';
                    echo '<td>' . $treningi . '</td>';
                    echo '<td><a href="' . admin_url('admin.php?page=fc-mistrzaki-zawodnicy&action=edit&id=' . $zawodnik->id) . '" class="button">Edytuj</a> ';
                    echo '<a href="' . esc_url($delete_url) . '" class="button button-link-delete" onclick="return confirm(\'Czy na pewno chcesz usunąć tego zawodnika?\');" style="color: #a00;">Usuń</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">Brak zawodników spełniających kryteria.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>