<?php
// Plik: templates/admin/treningi-groups.php

if (!defined('ABSPATH')) exit;
$date = sanitize_text_field($_GET['trening_date']);
?>
<div class="wrap">
    <h1>Wybierz grupę na dzień <?php echo date_i18n('d.m.Y', strtotime($date)); ?></h1>
    <div class="grupy-selection" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px;">
        <?php
        $grupy = fcm_get_grupy_wiekowe();
        foreach ($grupy as $key => $label) {
            $url = add_query_arg('grupa', $key);
            echo '<a href="' . esc_url($url) . '" class="button button-primary">' . esc_html($label) . '</a>';
        }
        ?>
    </div>
    <p style="margin-top:20px;"><a href="<?php echo esc_url(remove_query_arg(['trening_date', 'grupa', 'lokalizacja'])); ?>">&laquo; Powrót do kalendarza</a></p>
</div>