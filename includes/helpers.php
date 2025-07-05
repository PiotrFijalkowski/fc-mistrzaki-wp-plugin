<?php
if (!defined('ABSPATH')) exit;

function fcm_get_lokalizacje() {
    return ['sp22' => 'SP nr 22, ul. Marii Konopnickiej 3', 'zsz5' => 'ZSZ5, ul. Antoniuk Fabryczny 40', 'zstio' => 'ZSTiO, ul. Sienkiewicza 57'];
}

function fcm_get_grupy_wiekowe() {
    return ['grupa-2-3' => 'Grupa 2-3 lata', 'grupa-4-5' => 'Grupa 4-5 lat', 'grupa-6-8' => 'Grupa 6-8 lat', 'grupa-9-10' => 'Grupa 9-10 lat'];
}

function fcm_display_notices() {
    if (!isset($_GET['fcm_notice'])) return;
    $notice_type = sanitize_key($_GET['fcm_notice']);
    $messages = [
        'success' => 'Operacja zakończona pomyślnie.',
        'error' => 'Wystąpił błąd.',
        'delete_success' => 'Zawodnik został usunięty.',
        'delete_error' => 'Wystąpił błąd podczas usuwania.',
        'attendance_saved' => 'Zaktualizowano obecność dla ' . (isset($_GET['saved_count']) ? intval($_GET['saved_count']) : 0) . ' zawodników.'
    ];
    $is_error = strpos($notice_type, 'error') !== false;
    echo "<div class='notice " . ($is_error ? 'notice-error' : 'notice-success') . " is-dismissible'><p>{$messages[$notice_type]}</p></div>";
}
