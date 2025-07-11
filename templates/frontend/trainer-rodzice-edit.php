<?php // templates/frontend/trainer-rodzice-edit.php
global $wpdb;
$rodzice_table = $wpdb->prefix . 'fcm_rodzice';
$zawodnicy_table = $wpdb->prefix . 'fcm_zawodnicy';
$powiazania_table = $wpdb->prefix . 'fcm_powiazania';

$rodzic_id = isset($_GET['rodzic_id']) ? intval($_GET['rodzic_id']) : 0;
$rodzic = $wpdb->get_row($wpdb->prepare("SELECT * FROM $rodzice_table WHERE id = %d", $rodzic_id));

if (!$rodzic) {
    echo '<div class="notice notice-error"><p>Rodzic nie znaleziony.</p></div>';
    return;
}

$all_zawodnicy = $wpdb->get_results("SELECT id, imie_nazwisko, grupa_wiekowa, lokalizacja FROM $zawodnicy_table ORDER BY imie_nazwisko ASC");
$assigned_zawodnicy = $wpdb->get_col($wpdb->prepare("SELECT zawodnik_id FROM $powiazania_table WHERE rodzic_id = %d AND status = 'aktywne'", $rodzic_id));

$lokalizacje_map = fcm_get_lokalizacje();
$grupy_map = fcm_get_grupy_wiekowe();

?>
<div class="wrap">
    <h1>Edytuj Rodzica: <?php echo esc_html($rodzic->email); ?></h1>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fcm-form">
        <input type="hidden" name="action" value="fcm_edit_parent">
        <input type="hidden" name="rodzic_id" value="<?php echo $rodzic->id; ?>">
        <?php wp_nonce_field('fcm_edit_parent_action', 'fcm_edit_parent_nonce'); ?>
        <div class="fcm-form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo esc_attr($rodzic->email); ?>" required class="fcm-form-control">
        </div>
        <div class="fcm-form-group">
            <label for="password">Nowe Hasło (pozostaw puste, aby nie zmieniać)</label>
            <input type="password" name="password" id="password" class="fcm-form-control">
        </div>
        <div class="fcm-form-group">
            <label for="fcm-zawodnicy-select">Przypisani Zawodnicy</label>
            <select name="zawodnicy_ids[]" id="fcm-zawodnicy-select" multiple="multiple" class="fcm-form-control">
                <?php foreach ($all_zawodnicy as $zawodnik) : ?>
                    <option value="<?php echo esc_attr($zawodnik->id); ?>" <?php selected(in_array($zawodnik->id, $assigned_zawodnicy)); ?>><?php echo esc_html($zawodnik->imie_nazwisko); ?> (<?php echo esc_html($grupy_map[$zawodnik->grupa_wiekowa] ?? $zawodnik->grupa_wiekowa); ?>, <?php echo esc_html($lokalizacje_map[$zawodnik->lokalizacja] ?? $zawodnik->lokalizacja); ?>) (ID: <?php echo $zawodnik->id; ?>)</option>
                <?php endforeach; ?>
            </select>
            <p class="description">Przytrzymaj CTRL (Windows) lub Command (Mac), aby wybrać wielu zawodników.</p>
        </div>
        <?php submit_button('Zapisz zmiany', 'primary', 'fcm_save_parent'); ?>
    </form>
</div>