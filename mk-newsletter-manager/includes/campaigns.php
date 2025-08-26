<?php
// Template predefiniti
$mknl_email_templates = [
    'benvenuto' => [
        'subject' => 'Benvenuto in M&K Vintage!',
        'content' => "Ciao [nome],\n\nGrazie per esserti iscritto alla nostra newsletter.\nOgni settimana ti invieremo aggiornamenti su nuovi arrivi, collezioni esclusive e eventi speciali.\n\nA presto,\nIl team di M&K Vintage"
    ],
    'nuovo-arrivo' => [
        'subject' => 'Nuovo arrivo in negozio!',
        'content' => "Ciao [nome],\n\nAbbiamo appena aggiunto nuovi capi vintage nel nostro negozio.\nVieni a scoprire gli ultimi arrivi prima che finiscano!\n\n👉 Visita il sito\n\nM&K Vintage"
    ],
    'offerta-speciale' => [
        'subject' => 'Offerta speciale solo per te',
        'content' => "Ciao [nome],\n\nGrazie per essere parte della nostra community.\nPer te, uno sconto del 10% sul prossimo acquisto.\nUsa il codice: VINTAGE10\n\nValido per 7 giorni.\n\nA presto,\nM&K Vintage"
    ]
];

function mknl_render_campaigns_page() {
    global $wpdb, $mknl_email_templates;
    $table_name = $wpdb->prefix . 'mk_newsletter_subscribers';

    if ($_POST['mknl_send_campaign'] && wp_verify_nonce($_POST['mknl_nonce'], 'send_campaign')) {
        $subject = sanitize_text_field($_POST['subject']);
        $content = wp_kses_post($_POST['content']);
        $count = mknl_send_email_to_all_subscribers($subject, $content);
        echo '<div class="notice notice-success"><p>Campagna inviata a ' . $count . ' iscritti.</p></div>';
    }

    $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mk_newsletter_subscribers");
    ?>
    <div class="wrap">
        <h1>Campagne di Newsletter</h1>
        <p>Iscritti totali: <strong><?php echo $total; ?></strong></p>

        <form method="post">
            <?php wp_nonce_field('send_campaign', 'mknl_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label>Template predefinito</label></th>
                    <td>
                        <select id="template-select" style="width: 100%;">
                            <option value="">— Seleziona un template —</option>
                            <?php foreach ($mknl_email_templates as $key => $tpl): ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($tpl['subject']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>Titolo Email</label></th>
                    <td><input type="text" name="subject" id="subject" class="regular-text" required value=""></td>
                </tr>
                <tr>
                    <th><label>Messaggio</label></th>
                    <td><textarea name="content" id="content" rows="10" class="large-text" required></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="mknl_send_campaign" class="button button-primary">
                    Invia Campagna a Tutti
                </button>
            </p>
        </form>

        <script>
        document.getElementById('template-select').addEventListener('change', function() {
            const templates = <?php echo json_encode($mknl_email_templates); ?>;
            const key = this.value;
            if (key && templates[key]) {
                document.getElementById('subject').value = templates[key].subject;
                document.getElementById('content').value = templates[key].content;
            }
        });
        </script>
    </div>
    <?php
}