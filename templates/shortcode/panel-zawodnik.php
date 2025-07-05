<?php
// Plik: templates/shortcode/panel-zawodnik.php

if (!defined('ABSPATH')) exit;
global $wpdb;
// Zmienna $zawodnik_id jest dostępna z klasy FCM_Shortcode
$zawodnik = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fcm_zawodnicy WHERE id = %d", $zawodnik_id));
?>
<div class="fcm-parent-panel">
    <h3>Panel Zawodnika</h3>
    <?php if ($zawodnik): ?>
        <p><strong>Imię i Nazwisko:</strong> <?php echo esc_html($zawodnik->imie_nazwisko); ?></p>
        <p><strong>Lokalizacja:</strong> <?php echo esc_html(fcm_get_lokalizacje()[$zawodnik->lokalizacja] ?? 'Brak'); ?></p>
        <p><strong>Pozostało treningów:</strong> <?php echo esc_html($zawodnik->liczba_treningow); ?></p>
        
        <h4>Historia obecności:</h4>
        <?php
        $obecnosci = $wpdb->get_results($wpdb->prepare("SELECT data_treningu FROM {$wpdb->prefix}fcm_obecnosci WHERE zawodnik_id = %d ORDER BY data_treningu DESC", $zawodnik_id));
        if ($obecnosci) {
            echo '<ul>';
            foreach ($obecnosci as $obecnosc) {
                echo '<li>' . date_i18n('d F Y', strtotime($obecnosc->data_treningu)) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Brak odnotowanych obecności.</p>';
        }
        ?>
        <a href="<?php echo esc_url(add_query_arg('fcm_logout', 'true', get_permalink())); ?>">Wyloguj się</a>
    <?php else: ?>
        <p>Nie znaleziono danych zawodnika.</p>
    <?php endif; ?>
</div>

