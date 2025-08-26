<?php
function mknl_render_subscribers_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mk_newsletter_subscribers';

    // Elimina iscritto
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, ['id' => $id]);
        echo '<div class="notice notice-success"><p>Iscritto eliminato.</p></div>';
    }

    $subscribers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC");

    ?>
    <div class="wrap">
        <h1>Iscritti alla Newsletter</h1>
        <p>Totale: <?php echo count($subscribers); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Data</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td><?php echo $sub->id; ?></td>
                    <td><?php echo esc_html($sub->name); ?></td>
                    <td><?php echo esc_html($sub->email); ?></td>
                    <td><?php echo esc_html($sub->date); ?></td>
                    <td>
                        <a href="<?php echo wp_nonce_url('?page=mk-newsletter-subscribers&action=delete&id=' . $sub->id, 'delete_subscriber'); ?>" 
                           onclick="return confirm('Eliminare questo iscritto?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}