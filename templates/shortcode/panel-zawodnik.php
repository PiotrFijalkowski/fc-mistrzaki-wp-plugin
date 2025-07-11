<?php
// Plik: templates/shortcode/panel-zawodnik.php

if (!defined('ABSPATH')) exit;
global $wpdb;
// Zmienna $zawodnik_id jest dostępna z klasy FCM_Shortcode
$zawodnik = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fcm_zawodnicy WHERE id = %d", $zawodnik_id));

$announcements = get_option('fcm_announcements', '');

$harmonogram_table = $wpdb->prefix . 'fcm_harmonogram';
$lokalizacje_map = fcm_get_lokalizacje();
$grupy_map = fcm_get_grupy_wiekowe();
$dni_tygodnia_map = [
    'monday' => 'Poniedziałek',
    'tuesday' => 'Wtorek',
    'wednesday' => 'Środa',
    'thursday' => 'Czwartek',
    'friday' => 'Piątek',
    'saturday' => 'Sobota',
    'sunday' => 'Niedziela',
];

?>
<div class="fcm-parent-panel">
    <?php if ($zawodnik): ?>
    <div class="fcm-sidebar">
        <div class="fcm-avatar">
            <?php echo esc_html(substr($zawodnik->imie_nazwisko, 0, 1)); ?>
        </div>
        <div class="fcm-player-name"><?php echo esc_html($zawodnik->imie_nazwisko); ?></div>
        <div class="fcm-player-location"><?php echo esc_html($lokalizacje_map[$zawodnik->lokalizacja] ?? 'Brak'); ?></div>

        <ul class="fcm-nav-menu">
            <li><a href="#" class="active">Panel Główny</a></li>
            <li><a href="#">Historia Obecności</a></li>
            <li><a href="#">Nadchodzące Treningi</a></li>
            <li><a href="#" class="active">Panel Główny</a></li>
            <li><a href="#history">Historia Obecności</a></li>
            <li><a href="#upcoming">Nadchodzące Treningi</a></li>
            <li><a href="<?php echo esc_url(add_query_arg('fcm_logout', 'true', get_permalink())); ?>" class="fcm-logout-button">Wyloguj się</a></li>
        </ul>
    </div>

    <div class="fcm-main-content" id="main-panel">
        <h3>Panel Zawodnika</h3>

        <?php if (!empty($announcements)) : ?>
            <div class="fcm-section-card fcm-announcements">
                <h4>Ogłoszenia:</h4>
                <?php echo wp_kses_post($announcements); ?>
            </div>
        <?php endif; ?>

        <div class="fcm-section-card fcm-player-info-card">
            <h4>Informacje o zawodniku:</h4>
            <div class="fcm-info-grid">
                <div class="fcm-info-item">
                    <div class="label">Imię i Nazwisko</div>
                    <div class="value"><?php echo esc_html($zawodnik->imie_nazwisko); ?></div>
                </div>
                <div class="fcm-info-item">
                    <div class="label">Lokalizacja</div>
                    <div class="value"><?php echo esc_html($lokalizacje_map[$zawodnik->lokalizacja] ?? 'Brak'); ?></div>
                </div>
                <div class="fcm-info-item">
                    <div class="label">Pozostało treningów</div>
                    <div class="value"><?php echo esc_html($zawodnik->liczba_treningow); ?></div>
                </div>
            </div>
        </div>
        
        <div class="fcm-section-card" id="history">
            <h4>Historia obecności:</h4>
            <?php
            $obecnosci = $wpdb->get_results($wpdb->prepare("SELECT data_treningu FROM {$wpdb->prefix}fcm_obecnosci WHERE zawodnik_id = %d ORDER BY data_treningu DESC", $zawodnik_id));
            if ($obecnosci) {
                echo '<ul class="fcm-history-list">';
                foreach ($obecnosci as $obecnosc) {
                    echo '<li>' . date_i18n('d F Y', strtotime($obecnosc->data_treningu)) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>Brak odnotowanych obecności.</p>';
            }
            ?>
        </div>

        <div class="fcm-section-card" id="upcoming">
            <h4>Nadchodzące treningi:</h4>
            <?php
            $harmonogramy_cykliczne = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $harmonogram_table WHERE grupa_wiekowa = %s AND lokalizacja = %s",
                $zawodnik->grupa_wiekowa,
                $zawodnik->lokalizacja
            ));

            if ($harmonogramy_cykliczne) {
                $today = new DateTime();
                $two_months_later = (new DateTime())->modify('+2 months');
                $upcoming_trainings = [];

                while ($today <= $two_months_later) {
                    $current_day_of_week = strtolower($today->format('l')); // 'monday', 'tuesday', etc.
                    foreach ($harmonogramy_cykliczne as $harmonogram) {
                        if ($harmonogram->dzien_tygodnia === $current_day_of_week) {
                            $training_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $harmonogram->godzina);
                            if ($training_datetime >= new DateTime()) { // Tylko przyszłe treningi
                                $upcoming_trainings[] = [
                                    'date' => $training_datetime->format('Y-m-d'),
                                    'time' => $harmonogram->godzina,
                                    'grupa' => $grupy_map[$harmonogram->grupa_wiekowa] ?? $harmonogram->grupa_wiekowa,
                                    'lokalizacja' => $lokalizacje_map[$harmonogram->lokalizacja] ?? $harmonogram->lokalizacja,
                                    'dzien_tygodnia' => $dni_tygodnia_map[$harmonogram->dzien_tygodnia] ?? $harmonogram->dzien_tygodnia,
                                ];
                            }
                        }
                    }
                    $today->modify('+1 day');
                }

                // Sortuj treningi chronologicznie
                usort($upcoming_trainings, function($a, $b) {
                    return strtotime($a['date'] . ' ' . $a['time']) - strtotime($b['date'] . ' ' . $b['time']);
                });

                if (!empty($upcoming_trainings)) {
                    echo '<table class="fcm-trainings-table">';
                    echo '<thead><tr><th>Data</th><th>Godzina</th><th>Grupa</th><th>Lokalizacja</th></tr></thead>';
                    echo '<tbody>';
                    foreach ($upcoming_trainings as $training) {
                        echo '<tr>';
                        echo '<td>' . date_i18n('d F Y', strtotime($training['date'])) . ' (' . esc_html($training['dzien_tygodnia']) . ')</td>';
                        echo '<td>' . esc_html($training['time']) . '</td>';
                        echo '<td>' . esc_html($training['grupa']) . '</td>';
                        echo '<td>' . esc_html($training['lokalizacja']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>Brak nadchodzących treningów w ciągu najbliższych 2 miesięcy.</p>';
                }

            } else {
                echo '<p>Brak zdefiniowanego harmonogramu dla tej grupy i lokalizacji.</p>';
            }
            ?>
        </div>
    </div>
    <?php else: ?>
        <p>Nie znaleziono danych zawodnika.</p>
    <?php endif; ?>
</div>