<?php
// Plik: templates/admin/treningi-calendar.php

if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
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
    ?>
    <div class="calendar-wrap">
        <div class="calendar-nav">
            <a href="<?php echo esc_url($prev_month_link); ?>" class="button">&laquo; Poprzedni</a>
            <h2 class="calendar-title"><?php echo date_i18n('F Y', $date->getTimestamp()); ?></h2>
            <a href="<?php echo esc_url($next_month_link); ?>" class="button">Następny &raquo;</a>
        </div>
        <table class="wp-list-table widefat fixed striped" id="trening-calendar">
            <thead><tr><th>Pon</th><th>Wt</th><th>Śr</th><th>Czw</th><th>Pt</th><th>Sob</th><th>Ndz</th></tr></thead>
            <tbody><tr>
            <?php
            $first_day_of_month = $date->format('N');
            for ($i = 1; $i < $first_day_of_month; $i++) echo '<td class="pad"></td>';
            
            $days_in_month = $date->format('t');
            for ($day = 1; $day <= $days_in_month; $day++) {
                $current_date = $date->setDate($year, $month, $day);
                $date_str = $current_date->format('Y-m-d');
                $url = admin_url('admin.php?page=fc-mistrzaki&trening_date=' . $date_str);
                $is_today = ($date_str == date('Y-m-d')) ? 'today' : '';

                if (($day + $first_day_of_month - 2) % 7 == 0 && $day != 1) echo '</tr><tr>';
                
                echo "<td class='{$is_today}'><a href='" . esc_url($url) . "'><strong>" . $day . "</strong></a></td>";
            }
            
            $last_day_of_month = $date->modify('last day of this month')->format('N');
            for ($i = $last_day_of_month; $i < 7; $i++) echo '<td class="pad"></td>';
            ?>
            </tr></tbody>
        </table>
    </div>
    <?php
    // Stats summary
    global $wpdb;
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
