<?php
function mknl_send_email_to_all_subscribers($subject, $content) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mk_newsletter_subscribers';
    $subscribers = $wpdb->get_results("SELECT name, email FROM $table_name");
    $sent = 0;

    foreach ($subscribers as $sub) {
        $message = "Ciao " . $sub->name . ",\n\n";
        $message .= $content . "\n\n";
        $message .= "— M&K Vintage";

        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (wp_mail($sub->email, $subject, $message, $headers)) {
            $sent++;
        }
    }

    return $sent;
}