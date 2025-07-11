<?php
if (!defined('ABSPATH')) exit;

class FCM_DB {
    public static function activate() {
        global $wpdb;
        $zawodnicy_table_name = $wpdb->prefix . 'fcm_zawodnicy';
        $obecnosci_table_name = $wpdb->prefix . 'fcm_obecnosci';
        $rodzice_table_name = $wpdb->prefix . 'fcm_rodzice';
        $powiazania_table_name = $wpdb->prefix . 'fcm_powiazania';
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql_zawodnicy = "CREATE TABLE $zawodnicy_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            imie_nazwisko tinytext NOT NULL,
            lokalizacja varchar(100) NOT NULL,
            grupa_wiekowa varchar(55) NOT NULL,
            liczba_treningow int(11) NOT NULL DEFAULT 10,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_zawodnicy);

        $sql_obecnosci = "CREATE TABLE $obecnosci_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            zawodnik_id mediumint(9) NOT NULL,
            data_treningu date NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY obecnosc (zawodnik_id,data_treningu)
        ) $charset_collate;";
        dbDelta($sql_obecnosci);

        $sql_rodzice = "CREATE TABLE $rodzice_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            password varchar(255) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";
        dbDelta($sql_rodzice);

        $sql_powiazania = "CREATE TABLE $powiazania_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            rodzic_id mediumint(9) NOT NULL,
            zawodnik_id mediumint(9) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'oczekujace',
            PRIMARY KEY  (id),
            UNIQUE KEY powiazanie (rodzic_id,zawodnik_id)
        ) $charset_collate;";
        dbDelta($sql_powiazania);

        $sql_harmonogram = "CREATE TABLE {$wpdb->prefix}fcm_harmonogram (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            grupa_wiekowa varchar(55) NOT NULL,
            lokalizacja varchar(100) NOT NULL,
            dzien_tygodnia varchar(20) NOT NULL,
            godzina time NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unikalny_trening (grupa_wiekowa, lokalizacja, dzien_tygodnia, godzina)
        ) $charset_collate;";
        dbDelta($sql_harmonogram);

        $is_empty = $wpdb->get_var("SELECT COUNT(*) FROM $zawodnicy_table_name") == 0;
        if ($is_empty) {
            self::seed_dummy_data();
        }
    }

    private static function seed_dummy_data() {
        global $wpdb;
        $zawodnicy_table_name = $wpdb->prefix . 'fcm_zawodnicy';
        $grupy = fcm_get_grupy_wiekowe();
        $lokalizacje = fcm_get_lokalizacje();
        $imiona = ['Jan', 'Piotr', 'Anna', 'Katarzyna', 'Marek', 'Ewa', 'Tomasz', 'Magdalena', 'Krzysztof', 'Agnieszka'];
        $nazwiska = ['Kowalski', 'Nowak', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Zieliński', 'Szymański', 'Woźniak', 'Dąbrowski', 'Kozłowski'];

        foreach ($grupy as $grupa_key => $grupa_label) {
            foreach ($lokalizacje as $lok_key => $lok_label) {
                for ($i = 0; $i < 2; $i++) {
                    $wpdb->insert($zawodnicy_table_name, [
                        'imie_nazwisko' => $imiona[array_rand($imiona)] . ' ' . $nazwiska[array_rand($nazwiska)],
                        'lokalizacja' => $lok_key,
                        'grupa_wiekowa' => $grupa_key,
                        'liczba_treningow' => rand(5, 15)
                    ]);
                }
            }
        }
    }
}
