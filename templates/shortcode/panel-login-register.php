<?php // templates/shortcode/panel-login-register.php ?>
<div class="fcm-auth-container">
    <div class="fcm-auth-card">
        <div class="fcm-auth-tabs">
            <button class="fcm-tab-button active" data-tab="login">Zaloguj się</button>
            <button class="fcm-tab-button" data-tab="register">Zarejestruj się</button>
        </div>

        <div class="fcm-tab-content active" id="fcm-login-tab">
            <h2>Zaloguj się</h2>
            <?php if (isset($_SESSION['fcm_login_error'])) : ?>
                <p class="fcm-error-message"><?php echo $_SESSION['fcm_login_error']; unset($_SESSION['fcm_login_error']); ?></p>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fcm-form">
                <input type="hidden" name="action" value="fcm_login">
                <input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr(wp_get_referer()); ?>">
                <div class="fcm-form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required class="fcm-form-control">
                </div>
                <div class="fcm-form-group">
                    <label for="password">Hasło:</label>
                    <input type="password" name="password" id="password" required class="fcm-form-control">
                </div>
                <button type="submit" name="fcm_login" class="fcm-button fcm-button-primary">Zaloguj</button>
            </form>
        </div>

        <div class="fcm-tab-content" id="fcm-register-tab">
            <h2>Zarejestruj się</h2>
            <?php if (isset($_GET['fcm_success']) && $_GET['fcm_success'] === 'registration_complete') : ?>
                <p class="fcm-success-message">Rejestracja zakończona pomyślnie! Sprawdź swoją skrzynkę e-mail.</p>
            <?php elseif (isset($_GET['fcm_error'])) : ?>
                <p class="fcm-error-message">
                    <?php
                    switch ($_GET['fcm_error']) {
                        case 'invalid_email':
                            echo 'Nieprawidłowy format adresu e-mail.';
                            break;
                        case 'email_exists':
                            echo 'Ten adres e-mail jest już zarejestrowany.';
                            break;
                        case 'registration_failed':
                            echo 'Wystąpił błąd podczas rejestracji. Spróbuj ponownie.';
                            break;
                        default:
                            echo 'Wystąpił błąd.';
                            break;
                    }
                    ?>
                </p>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fcm-form">
                <input type="hidden" name="action" value="fcm_register_parent">
                <?php wp_nonce_field('fcm_register_action', 'fcm_register_nonce'); ?>
                <div class="fcm-form-group">
                    <label for="reg_email">Email:</label>
                    <input type="email" name="email" id="reg_email" required class="fcm-form-control">
                </div>
                <div class="fcm-form-group">
                    <label for="reg_password">Hasło:</label>
                    <input type="password" name="password" id="reg_password" required class="fcm-form-control">
                </div>
                <div class="fcm-form-group">
                    <label for="zawodnik_id">ID Zawodnika (opcjonalnie):</label>
                    <input type="number" name="zawodnik_id" id="zawodnik_id" class="fcm-form-control">
                </div>
                <button type="submit" class="fcm-button fcm-button-primary">Zarejestruj</button>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.fcm-tab-button').on('click', function() {
        var tab = $(this).data('tab');

        $('.fcm-tab-button').removeClass('active');
        $(this).addClass('active');

        $('.fcm-tab-content').removeClass('active');
        $('#fcm-' + tab + '-tab').addClass('active');
    });

    // Aktywuj odpowiednią zakładkę po przekierowaniu z błędem/sukcesem
    <?php if (isset($_GET['fcm_error']) || (isset($_GET['fcm_success']) && $_GET['fcm_success'] === 'registration_complete')) : ?>
        $('.fcm-tab-button[data-tab="register"]').click();
    <?php endif; ?>
});
</script>