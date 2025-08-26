<?php
/**
 * Plugin Name: M&K Newsletter Manager
 * Plugin URI:  https://tuosito.com/plugins/mk-newsletter-manager
 * Description: Plugin completo per gestire newsletter, iscritti e campagne direttamente da WordPress.
 * Version:     1.0.0
 * Author:      Il Tuo Nome
 * License:     GPL-2.0+
 */

// Evita accesso diretto
if (!defined('ABSPATH')) exit;

// Costanti
define('MKNL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MKNL_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include file principali
require_once MKNL_PLUGIN_PATH . 'includes/admin-menu.php';
require_once MKNL_PLUGIN_PATH . 'includes/subscribers.php';
require_once MKNL_PLUGIN_PATH . 'includes/campaigns.php';
require_once MKNL_PLUGIN_PATH . 'includes/email-sender.php';

// Hook di attivazione: crea tabella iscritti
function mknl_activate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mk_newsletter_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL UNIQUE,
        ip varchar(45) NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY email (email)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mknl_activate_plugin');

// Shortcode per il modulo frontend
function mknl_subscription_form_shortcode() {
    ob_start();
    include MKNL_PLUGIN_PATH . 'templates/subscription-form.php';
    return ob_get_clean();
}
add_shortcode('mk_newsletter_form', 'mknl_subscription_form_shortcode');

// Carica CSS admin
function mknl_admin_styles() {
    wp_enqueue_style('mknl-admin-css', MKNL_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'mknl_admin_styles');

// Registra il cron
function mknl_schedule_campaigns() {
    if (!wp_next_scheduled('mknl_daily_cron')) {
        wp_schedule_event(time(), 'daily', 'mknl_daily_cron');
    }
}
add_action('wp', 'mknl_schedule_campaigns');

// Hook del cron
function mknl_daily_cron_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mk_newsletter_subscribers';

    // Invia ogni 7 giorni
    $last_sent = get_option('mknl_last_automatic_campaign', false);
    $days_interval = 7;

    if (!$last_sent || (time() - $last_sent) >= ($days_interval * DAY_IN_SECONDS)) {
        $subject = 'Aggiornamenti settimanali da M&K Vintage';
        $content = "Ciao [nome],\n\nEcco i nuovi arrivi della settimana e gli eventi in programma.\n\nVisita il negozio per scoprire le ultime novità!\n\nA presto,\nIl team di M&K Vintage";

        mknl_send_email_to_all_subscribers($subject, $content);
        update_option('mknl_last_automatic_campaign', time());
    }
}
add_action('mknl_daily_cron', 'mknl_daily_cron_handler');