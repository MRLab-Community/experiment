<?php
/**
 * Gestisce il menu di amministrazione del plugin
 */

// Evita accesso diretto
if (!defined('ABSPATH')) exit;

/**
 * Aggiunge il menu e le sottovoci in Amministrazione
 */
function mknl_add_admin_menu() {
    add_menu_page(
        'Newsletter Manager',           // Titolo della pagina
        'Newsletter',                   // Nome nel menu
        'manage_options',               // Capacità richiesta
        'mk-newsletter',                // Slug
        'mknl_render_dashboard_page',   // Funzione di rendering
        'dashicons-email-alt',          // Icona
        30                              // Posizione
    );

    // Sottovoce: Iscritti
    add_submenu_page(
        'mk-newsletter',
        'Gestisci Iscritti',
        'Iscritti',
        'manage_options',
        'mk-newsletter-subscribers',
        'mknl_render_subscribers_page'
    );

    // Sottovoce: Campagne
    add_submenu_page(
        'mk-newsletter',
        'Crea Campagne',
        'Campagne',
        'manage_options',
        'mk-newsletter-campaigns',
        'mknl_render_campaigns_page'
    );
}
add_action('admin_menu', 'mknl_add_admin_menu');

/**
 * Pagina principale del plugin (Dashboard)
 */
function mknl_render_dashboard_page() {
    // Gestisci l'esecuzione manuale del cron
    if (isset($_GET['trigger_cron']) && wp_verify_nonce($_GET['_wpnonce'], 'trigger_cron')) {
        do_action('mknl_daily_cron');
        echo '<div class="notice notice-success is-dismissible"><p>Campagna automatica eseguita con successo.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Bentornato nel Newsletter Manager</h1>
        <p>Benvenuto nel pannello di gestione della newsletter. Usa il menu a sinistra per:</p>
        <ul>
            <li><a href="<?php echo admin_url('admin.php?page=mk-newsletter-subscribers'); ?>">Gestire gli iscritti</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=mk-newsletter-campaigns'); ?>">Creare e inviare campagne</a></li>
        </ul>

        <p>&nbsp;</p>
        <h2>Strumenti</h2>
        <p>
            <a href="<?php echo wp_nonce_url('?page=mk-newsletter&trigger_cron=1', 'trigger_cron'); ?>" 
               class="button button-primary">
                Esegui campagna automatica ora
            </a>
        </p>
        <p><small>Questa azione invia l'email settimanale a tutti gli iscritti.</small></p>
    </div>
    <?php
}