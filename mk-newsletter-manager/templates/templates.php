<?php
// Gestione iscrizione
if ($_POST['mknl_subscribe'] && wp_verify_nonce($_POST['mknl_nonce'], 'subscribe')) {
    $name  = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $ip    = $_SERVER['REMOTE_ADDR'];

    if (!$name || !$email || !is_email($email)) {
        $error = 'Dati non validi.';
    } else {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mk_newsletter_subscribers';

        $result = $wpdb->insert($table_name, [
            'name'  => $name,
            'email' => $email,
            'ip'    => $ip
        ], ['%s', '%s', '%s']);

        if ($result) {
            $success = 'Grazie! Sei iscritto alla newsletter.';
        } else {
            $error = 'Sei già iscritto.';
        }
    }
}
?>

<div class="mknl-subscription-form">
    <h3>Iscriviti alla Newsletter</h3>
    <p>Ricevi aggiornamenti su nuovi arrivi e collezioni esclusive.</p>

    <?php if (isset($error)): ?>
        <div class="error"><p><?php echo esc_html($error); ?></p></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="updated"><p><?php echo esc_html($success); ?></p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('subscribe', 'mknl_nonce'); ?>
        <input type="text" name="name" placeholder="Il tuo nome" required>
        <input type="email" name="email" placeholder="La tua email" required>
        <button type="submit" name="mknl_subscribe">ISCRIVITI</button>
    </form>
</div>