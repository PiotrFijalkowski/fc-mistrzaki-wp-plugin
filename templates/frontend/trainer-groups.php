<?php
// Plik: templates/frontend/trainer-groups.php

if (!defined('ABSPATH')) exit;
$date = sanitize_text_field($_GET['trening_date']);
?>
<div class="wrap">
    <h1>Wybierz grupę na dzień <?php echo date_i18n('d.m.Y', strtotime($date)); ?></h1>
    <div class="fcm-group-selection-grid">
        <?php
        $grupy = fcm_get_grupy_wiekowe();
        foreach ($grupy as $key => $label) {
            $url = add_query_arg('grupa', $key);
            echo '<div class="fcm-group-card"><a href="' . esc_url($url) . '" class="fcm-group-link">' . esc_html($label) . '</a></div>';
        }
        ?>
    </div>
    <p style="margin-top:20px;"><a href="<?php echo esc_url(remove_query_arg(['trening_date', 'grupa', 'lokalizacja'])); ?>" class="button">&laquo; Powrót do kalendarza</a></p>
</div>