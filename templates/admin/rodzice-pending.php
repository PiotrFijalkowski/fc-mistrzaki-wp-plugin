<?php // templates/admin/rodzice-pending.php
global $wpdb;
$powiazania_table = $wpdb->prefix . 'fcm_powiazania';
$rodzice_table = $wpdb->prefix . 'fcm_rodzice';
$zawodnicy_table = $wpdb->prefix . 'fcm_zawodnicy';

$pending_requests = $wpdb->get_results(
    "SELECT p.id, r.email, z.imie_nazwisko, z.id as zawodnik_id
    FROM $powiazania_table p
    JOIN $rodzice_table r ON p.rodzic_id = r.id
    JOIN $zawodnicy_table z ON p.zawodnik_id = z.id
    WHERE p.status = 'oczekujace'"
);
?>
<div class="wrap">
    <h1>Oczekujące zgłoszenia</h1>
    <?php fcm_display_notices(); ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Email Rodzica</th>
                <th>Zawodnik</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pending_requests) : ?>
                <?php foreach ($pending_requests as $request) : ?>
                    <tr>
                        <td><?php echo esc_html($request->email); ?></td>
                        <td><?php echo esc_html($request->imie_nazwisko); ?> (ID: <?php echo $request->zawodnik_id; ?>)</td>
                        <td>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=fcm_approve_powiazanie&powiazanie_id=' . $request->id), 'approve_' . $request->id); ?>" class="button button-primary">Zatwierdź</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">Brak oczekujących zgłoszeń.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>