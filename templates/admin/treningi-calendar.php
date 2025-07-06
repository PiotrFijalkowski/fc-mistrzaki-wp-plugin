<?php
// Plik: templates/admin/treningi-calendar.php

if (!defined('ABSPATH')) exit;
global $wpdb;
?>
<div class="wrap m100">
    <h1>Treningi</h1>
    <?php fcm_display_notices(); ?>
    <?php
    $month = isset($_GET['cal_month']) ? intval($_GET['cal_month']) : date('m');
    $year = isset($_GET['cal_year']) ? intval($_GET['cal_year']) : date('Y');

    $date = new DateTimeImmutable("$year-$month-01");
    
    $prev_month = $date->modify('-1 month');
    $prev_month_link = add_query_arg(['cal_month' => $prev_month->format('m'), 'cal_year' => $prev_month->format('Y')]);

    $next_month = $date->modify('+1 month');
    $next_month_link = add_query_arg(['cal_month' => $next_month->format('m'), 'cal_year' => $next_month->format('Y')]);

    $first_day_of_cal_month = $date->format('Y-m-01');
    $last_day_of_cal_month = $date->format('Y-m-t');
    $trening_days_raw = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT data_treningu FROM {$wpdb->prefix}fcm_obecnosci WHERE data_treningu BETWEEN %s AND %s",
        $first_day_of_cal_month,
        $last_day_of_cal_month
    ));
    $trening_days = wp_list_pluck($trening_days_raw, 'data_treningu');
    $today_dt = new DateTimeImmutable('today');
    ?>
    <div class="calendar-wrap m100">
        <div class="calendar-nav">
            <div class="d-flex justify-content-between align-items-center next-prev">
                <a href="<?php echo esc_url($prev_month_link); ?>" class="button">&laquo; Poprzedni</a>
                <h2 class="calendar-title"><?php echo date_i18n('F Y', $date->getTimestamp()); ?></h2>
                    <a href="<?php echo esc_url($next_month_link); ?>" class="button">Następny &raquo;</a>

            </div>
        </div>
        <table class="wp-list-table widefat fixed striped" id="trening-calendar">
            <thead><tr><th class="dayname-padding">Pon</th><th class="dayname-padding">Wt</th><th class="dayname-padding">Śr</th><th class="dayname-padding">Czw</th><th class="dayname-padding">Pt</th><th class="dayname-padding">Sob</th><th class="dayname-padding">Ndz</th></tr></thead>
            <tbody>
                <?php
                $first_day_of_month = (int) $date->format('N');
                $days_in_month = (int) $date->format('t');
                $day_counter = 1;
                $cell_counter = 0;

                echo '<tr>';

                for ($i = 1; $i < $first_day_of_month; $i++) {
                    echo '<td class="pad"></td>';
                    $cell_counter++;
                }

                while ($day_counter <= $days_in_month) {
                    if ($cell_counter > 0 && $cell_counter % 7 == 0) {
                        echo '</tr><tr>';
                    }

                    $current_date = $date->setDate($year, $month, $day_counter);
                    $date_str = $current_date->format('Y-m-d');
                    $url = '#'; // Link jest teraz triggerem dla JS
                    
                    $classes = [];
                    if ($current_date < $today_dt && $date_str !== $today_dt->format('Y-m-d')) {
                        $classes[] = 'past';
                    }
                    if ($date_str === $today_dt->format('Y-m-d')) {
                        $classes[] = 'today';
                    }
                    if (in_array($date_str, $trening_days)) {
                        $classes[] = 'trening';
                    }
                    $class_string = implode(' ', $classes);

                    // Dodajemy atrybuty data-* dla JavaScriptu
                    echo "<td class='{$class_string}' data-date='{$date_str}' data-date-formatted='" . date_i18n('d F Y', strtotime($date_str)) . "'><a href='{$url}'><strong>" . $day_counter . "</strong></a></td>";

                    $day_counter++;
                    $cell_counter++;
                }

                while ($cell_counter % 7 != 0) {
                    echo '<td class="pad"></td>';
                    $cell_counter++;
                }

                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <?php
    $zawodnicy_table_name = $wpdb->prefix . 'fcm_zawodnicy';
    $aktywni = $wpdb->get_var("SELECT COUNT(*) FROM $zawodnicy_table_name WHERE liczba_treningow > 0");
    $nieaktywni = $wpdb->get_var("SELECT COUNT(*) FROM $zawodnicy_table_name WHERE liczba_treningow = 0");
    ?>
    <div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 15px;">
        <h3>Podsumowanie</h3>
        <p><strong>Liczba zawodników z aktywnymi treningami:</strong> <?php echo intval($aktywni); ?></p>
        <p><strong>Liczba zawodników z nieaktywnymi treningami:</strong> <?php echo intval($nieaktywni); ?></p>
    </div>
</div>

<!-- Struktura HTML okna dialogowego -->
<div class="fcm-modal-overlay" id="fcm-group-modal">
    <div class="fcm-modal-content">
        <div class="fcm-modal-header">
            <h3 id="fcm-modal-title">Wybierz grupę</h3>
            <button class="fcm-modal-close" id="fcm-modal-close-btn">&times;</button>
        </div>
        <div class="fcm-modal-body">
            <div class="grupy-selection" id="fcm-modal-groups">
                <!-- Przyciski grup zostaną wstawione tutaj przez JavaScript -->
            </div>
        </div>
    </div>
</div>
