<?php
// Plik: templates/shortcode/panel-login.php

if (!defined('ABSPATH')) exit;
// Zmienna $login_error jest dostępna z klasy FCM_Shortcode
?>
<div class="fcm-login-form">
    <h3>Panel Rodzica - Logowanie</h3>
    <form method="post" action="">
        <?php if (!empty($login_error)): ?>
            <p class="fcm-error"><?php echo $login_error; ?></p>
        <?php endif; ?>
        <div>
            <label for="fcm_zawodnik_id">ID Zawodnika:</label>
            <input type="text" id="fcm_zawodnik_id" name="fcm_zawodnik_id" required>
        </div>
        <div>
            <label for="fcm_password">Hasło:</label>
            <input type="password" id="fcm_password" name="fcm_password" required>
        </div>
        <input type="submit" name="fcm_login" value="Zaloguj się">
    </form>
</div>
<style>
    .fcm-login-form div { margin-bottom: 10px; }
    .fcm-login-form label { display: block; margin-bottom: 5px; }
    .fcm-error { color: red; }
</style>
