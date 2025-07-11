<?php // templates/frontend/trainer-announcements.php
if (!defined('ABSPATH')) exit;

// Pobierz aktualne ogłoszenia
$announcements = get_option('fcm_announcements', '');

// Obsługa zapisu ogłoszeń
if (isset($_POST['fcm_save_announcements']) && current_user_can('manage_options')) {
    if (isset($_POST['fcm_announcements_nonce']) && wp_verify_nonce($_POST['fcm_announcements_nonce'], 'fcm_save_announcements_action')) {
        $new_announcements = wp_kses_post($_POST['fcm_announcements_content']);
        update_option('fcm_announcements', $new_announcements);
        echo '<div class="notice notice-success is-dismissible"><p>Ogłoszenia zostały zapisane.</p></div>';
        $announcements = $new_announcements; // Aktualizuj zmienną po zapisie
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Błąd bezpieczeństwa. Ogłoszenia nie zostały zapisane.</p></div>';
    }
}
?>
<div class="wrap">
    <h1>Ogłoszenia dla Rodziców</h1>
    <form method="post" action="" class="fcm-form">
        <?php wp_nonce_field('fcm_save_announcements_action', 'fcm_announcements_nonce'); ?>
        <div class="fcm-form-group">
            <label for="fcm_announcements_content">Treść ogłoszeń</label>
            <textarea name="fcm_announcements_content" id="fcm_announcements_content" rows="10" cols="50" class="fcm-form-control"><?php echo esc_textarea($announcements); ?></textarea>
            <p class="description">Wprowadź ogłoszenia, które będą widoczne dla rodziców w panelu.</p>
        </div>
        <?php submit_button('Zapisz Ogłoszenia', 'primary', 'fcm_save_announcements'); ?>
    </form>
</div>