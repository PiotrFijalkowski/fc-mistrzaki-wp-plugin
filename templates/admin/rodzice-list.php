<?php // templates/admin/rodzice-list.php
global $wpdb;
$rodzice_table = $wpdb->prefix . 'fcm_rodzice';
$rodzice = $wpdb->get_results("SELECT * FROM $rodzice_table");
?>
<div class="wrap">
    <h1>Lista Rodziców</h1>
    <?php fcm_display_notices(); ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Przypisany zawodnik</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rodzice) : ?>
                <?php foreach ($rodzice as $rodzic) : ?>
                    <tr>
                        <td><?php echo $rodzic->id; ?></td>
                        <td><?php echo esc_html($rodzic->email); ?></td>
                        <td>
                            <?php
                            $powiazania_table = $wpdb->prefix . 'fcm_powiazania';
                            $zawodnicy_table = $wpdb->prefix . 'fcm_zawodnicy';
                            $powiazania = $wpdb->get_results($wpdb->prepare("SELECT z.imie_nazwisko, z.id FROM $powiazania_table p JOIN $zawodnicy_table z ON p.zawodnik_id = z.id WHERE p.rodzic_id = %d AND p.status = 'aktywne'", $rodzic->id));
                            if ($powiazania) {
                                foreach ($powiazania as $powiazanie) {
                                    echo esc_html($powiazanie->imie_nazwisko) . ' (ID: ' . $powiazanie->id . ')<br>';
                                }
                            } else {
                                echo 'Brak przypisanego zawodnika';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=fc-mistrzaki-rodzice&action=edit&rodzic_id=' . $rodzic->id); ?>" class="button">Edytuj</a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=fcm_delete_parent&rodzic_id=' . $rodzic->id), 'delete_parent_' . $rodzic->id); ?>" class="button button-danger">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">Brak zarejestrowanych rodziców.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>