<?php // templates/frontend/trainer-rodzice-assign.php ?>
<div class="wrap">
    <h1>Przypisz ręcznie</h1>
    <?php fcm_display_notices(); ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fcm-form">
        <input type="hidden" name="action" value="fcm_assign_parent">
        <?php wp_nonce_field('fcm_assign_action', 'fcm_assign_nonce'); ?>
        <div class="fcm-form-group">
            <label for="rodzic_email">Email Rodzica</label>
            <input type="email" name="rodzic_email" id="rodzic_email" required class="fcm-form-control">
        </div>
        <div class="fcm-form-group">
            <label for="zawodnik_id">ID Zawodnika</label>
            <input type="number" name="zawodnik_id" id="zawodnik_id" required class="fcm-form-control">
        </div>
        <?php submit_button('Przypisz', 'primary', 'fcm_assign_parent'); ?>
    </form>
</div>