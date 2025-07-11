<?php
// Plik: templates/frontend/trainer-players-list.php

if (!defined('ABSPATH')) exit;
global $wpdb;
$zawodnicy_table_name = $wpdb->prefix . 'fcm_zawodnicy';
?>
<div class="wrap">
    <h1>Lista Zawodników</h1>
    <?php fcm_display_notices(); ?>

    <div class="zawodnicy-filters">
        <?php
        $filter_grupa = isset($_GET['filter_grupa']) ? sanitize_key($_GET['filter_grupa']) : '';
        $filter_lokalizacja = isset($_GET['filter_lokalizacja']) ? sanitize_key($_GET['filter_lokalizacja']) : '';
        ?>
        <div class="filter-row">
            <strong>Grupa:</strong>
            <a href="<?php echo esc_url(add_query_arg(['fcm_action' => 'players', 'filter_grupa' => false])); ?>" class="button <?php echo empty($filter_grupa) ? 'button-primary' : ''; ?>">Wszystkie</a>
            <?php foreach(fcm_get_grupy_wiekowe() as $key => $label): ?>
                <a href="<?php echo esc_url(add_query_arg(['fcm_action' => 'players', 'filter_grupa' => $key])); ?>" class="button <?php echo ($filter_grupa == $key) ? 'button-primary' : ''; ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="filter-row">
            <strong>Lokalizacja:</strong>
            <a href="<?php echo esc_url(add_query_arg(['fcm_action' => 'players', 'filter_lokalizacja' => false])); ?>" class="button <?php echo empty($filter_lokalizacja) ? 'button-primary' : ''; ?>">Wszystkie</a>
            <?php foreach(fcm_get_lokalizacje() as $key => $label): ?>
                <a href="<?php echo esc_url(add_query_arg(['fcm_action' => 'players', 'filter_lokalizacja' => $key])); ?>" class="button <?php echo ($filter_lokalizacja == $key) ? 'button-primary' : ''; ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <a href="<?php echo esc_url(add_query_arg(['fcm_action' => 'players', 'player_action' => 'add'])); ?>" class="page-title-action button button-primary">Dodaj nowego</a>
    <input type="text" id="zawodnik-search" placeholder="Wyszukaj zawodnika..." class="fcm-form-control" style="float: right;">
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
                    $style_class = '';
                    if ($treningi <= 3 && $treningi > 1) $style_class = 'warning';
                    if ($treningi === 1) $style_class = 'danger';
                    if ($treningi === 0) $style_class = 'inactive';
                    $delete_url = wp_nonce_url(admin_url('admin-post.php?action=fcm_delete_zawodnik&id=' . $zawodnik->id),'fcm_delete_zawodnik_nonce_action_' . $zawodnik->id,'fcm_delete_nonce');
                    echo '<tr class="' . $style_class . '">';
                    echo '<td>' . $zawodnik->id . '</td>';
                    echo '<td>' . esc_html($zawodnik->imie_nazwisko) . '</td>';
                    echo '<td>' . esc_html(fcm_get_lokalizacje()[$zawodnik->lokalizacja] ?? 'Brak') . '</td>';
                    echo '<td>' . esc_html(fcm_get_grupy_wiekowe()[$zawodnik->grupa_wiekowa] ?? 'Brak') . '</td>';
                    echo '<td class="treningi-count">' . $treningi . '</td>';
                    echo '<td><a href="' . esc_url(add_query_arg(['fcm_action' => 'players', 'player_action' => 'edit', 'id' => $zawodnik->id])) . '" class="button">Edytuj</a> ';
                    echo '<a href="' . esc_url($delete_url) . '" class="button button-danger" onclick="return confirm(\'Czy na pewno chcesz usunąć tego zawodnika?\');">Usuń</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">Brak zawodników spełniających kryteria.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>