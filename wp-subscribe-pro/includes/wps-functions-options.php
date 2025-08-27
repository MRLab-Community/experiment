<?php
/**
 * Plugin options functions.
 */

/**
 * Get mailing services
 *
 * @use filter	wp_subscribe_mailing_services
 * @return array
 */
function wps_get_mailing_services( $type = 'raw' ) {

	$services = array(

		'activecampaign' => array(
            'title'       => esc_html__( 'ActiveCampaign', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your ActiveCampaign account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_ActiveCampaign'
        ),

		'acumbamail' => array(
            'title'       => esc_html__( 'Acumbamail', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Acumbamail account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Acumbamail'
        ),

        'aweber' => array(
            'title'       => esc_html__( 'Aweber', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Aweber account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Aweber'
        ),

		'benchmark' => array(
            'title'       => esc_html__( 'BenchmarkEmail', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your BenchmarkEmail account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Benchmark'
        ),

		'constantcontact' => array(
            'title'       => esc_html__( 'Constant Contact', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Constant Contact account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_ConstantContact'
        ),

		'drip' => array(
            'title'       => esc_html__( 'Drip.co', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Drip account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Drip'
        ),

		'feedburner' => array(
            'title'       => esc_html__( 'FeedBurner', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your FeedBurner account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_FeedBurner'
        ),

		'freshmail' => array(
            'title'       => esc_html__( 'FreshMail', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your FreshMail account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Freshmail'
        ),

		'getresponse' => array(
        	'title'       => esc_html__( 'GetResponse', 'wp-subscribe' ),
        	'description' => esc_html__( 'Adds subscribers to your GetResponse account.', 'wp-subscribe' ),
			'class'       => 'WPS_Subscription_GetResponse'
		),

		'knews' => array(
            'title'       => esc_html__( 'K-news', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to the plugin K-news.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_KNews'
        ),

		'madmimi' => array(
            'title'       => esc_html__( 'Mad Mimi', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Mad Mimi account.', 'wp-subscribe' ),
			'class'       => 'WPS_Subscription_MadMimi'
        ),

		'mailchimp' => array(
            'title'       => esc_html__( 'MailChimp', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your MailChimp account.', 'wp-subscribe' ),
			'class'       => 'WPS_Subscription_MailChimp'
        ),

		'mailerlite' => array(
            'title'       => esc_html__( 'MailerLite', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your MailerLite account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_MailerLite'
        ),

		'mailpoet' => array(
            'title'       => esc_html__( 'MailPoet', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to the plugin MailPoet.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_MailPoet'
        ),

        'mailpoet3' => array(
            'title'      => esc_html__( 'MailPoet 3', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to the new MailPoet 3 plugin.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_MailPoet3'
        ),

		'mailrelay' => array(
            'title'       => esc_html__( 'MailRelay', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to the plugin MailRelay.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_MailRelay'
        ),

		'mailster' => array(
            'title'       => esc_html__( 'Mailster', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to the plugin Mailster.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Mailster'
        ),

		'mymail' => array(
            'title'       => esc_html__( 'MyMail', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to the plugin MyMail.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_MyMail'
        ),

		'sendgrid' => array(
            'title'       => esc_html__( 'SendGrid', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your SendGrid account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_SendGrid'
        ),

        'sendinblue' => array(
            'title'       => esc_html__( 'SendInBlue', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your SendInBlue account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Sendinblue'
        ),

        'sendy' => array(
            'title'       => esc_html__( 'Sendy', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Sendy application.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Sendy'
        ),

        'convertkit' => array(
            'title'       => esc_html__( 'ConvertKit', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your ConvertKit account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_ConvertKit'
        ),
				'markethero' => array(
            'title'       => esc_html__( 'Market Hero', 'wp-subscribe' ),
            'description' => esc_html__( 'Adds subscribers to your Market Hero account.', 'wp-subscribe' ),
            'class'       => 'WPS_Subscription_Markethero'
        )
	);

	$services = apply_filters( 'wp_subscribe_mailing_services', $services );

	if( 'options' === $type ) {
		return wp_list_pluck( $services, 'title' );
	}

	return $services;
}

/**
 * Get default color palettes
 *
 * @use filter	wp_subscribe_form_color_palettes
 * @return array
 */
function wps_get_default_color_palettes() {

	$default_palettes = array(

		// Black and White
		'black_and_white' => array(
			'colors' => array(
				'background'        => '#f5f5f5',
				'title'             => '#2a2f2d',
				'text'              => '#959494',
				'field_text'        => '#999999',
				'field_background'  => '#e7e7e7',
				'button_text'       => '#2a2f2d',
				'button_background' => '#ffa054',
				'footer_text'       => '#959494'
			)
		),

		// Default
		'wp_subscribe_default' => array(
			'colors' => array(
				'background'        => '#f47555',
				'title'             => '#FFFFFF',
				'text'              => '#FFFFFF',
				'field_text'        => '#FFFFFF',
				'field_background'  => '#d56144',
				'button_text'       => '#f47555',
				'button_background' => '#FFFFFF',
				'footer_text'       => '#FFFFFF'
			)
		)
	);

	return apply_filters( 'wp_subscribe_form_color_palettes', $default_palettes );
}

/**
 * Get defaults for option page
 * @return arrray
 */
function wps_get_option_defaults() {

	return array(
    	'enable_popup' => 0,
    	'popup_content' => 'subscribe_form',
    	'popup_form_options' => array(
    		'service'                 => 'feedburner',
    		'include_name_field'      => false,
            'include_consent_field'      => false,
        ),
        'popup_form_labels' => array(
            'title'             => wp_kses_post( __( 'Get more stuff like this<br/> <span>in your inbox</span>', 'wp-subscribe' ) ),
            'text'              => esc_html__( 'Subscribe to our mailing list and get interesting stuff and updates to your email inbox.', 'wp-subscribe' ),
            'name_placeholder'  => esc_html__( 'Enter your name here', 'wp-subscribe' ),
            'email_placeholder' => esc_html__( 'Enter your email here', 'wp-subscribe' ),
            'button_text'       => esc_html__( 'Sign Up Now', 'wp-subscribe' ),
            'success_message'   => esc_html__( 'Thank you for subscribing.', 'wp-subscribe' ),
            'error_message'     => esc_html__( 'Something went wrong.', 'wp-subscribe' ),
            'footer_text'       => sprintf(__( 'The form collects name and email so that we can add you to our newsletter list for project updates. Checkout our %sprivacy policy%s for the full story on how we protect and manage your submitted data!', 'wp-subscribe' ), '<a href="#">', '</a>'),
            'consent_text'       => esc_html__( 'I consent to my submitted data being collected via this form*', 'wp-subscribe' ),
        ),
		'popup_form_colors' => array(
			'background_color'        => '#f47555',
            'title_color'             => '#FFFFFF',
            'text_color'              => '#FFFFFF',
            'field_text_color'        => '#FFFFFF',
            'field_background_color'  => '#d56144',
            'button_text_color'       => '#f47555',
            'button_background_color' => '#FFFFFF',
            'footer_text_color'       => '#FFFFFF'
        ),
		'popup_custom_html' => '<div class="popup-content">'.__('Some text with padding.', 'wp-subscribe').'</div>',
		'popup_animation_in' => 'fadeIn',
		'popup_animation_out' => 'fadeOut',
		'popup_triggers' => array(
			'on_enter'        => 0,
			'on_timeout'      => '1',
			'timeout'         => 15,
			'on_reach_bottom' => '1',
			'on_exit_intent'  => '1',
			'hide_on_mobile'  => 0,
			'hide_on_screen'  => 400
		),
		'popup_show_on' => array(
			'front_page' => '1',
			'single'     => '1',
			'archive'    => '1',
			'search'     => '1',
			'404_page'   => '1'
		),
		'popup_width' => '600',
		'popup_overlay_color' => '#0b0b0b',
		'popup_overlay_opacity' => '0.7',
		'cookie_expiration' => 14,
		'cookie_hash' => time(),
		'enable_single_post_form' => 0,
		'popup_posts_labels' => array(
			'title' => esc_html__( 'Before you go', 'wp-subscribe' ),
			'text'  => esc_html__( 'You may also be interested in these posts:', 'wp-subscribe' )
		),
		'popup_posts_colors' => array(
			'background_color' => '#f47555',
			'title_color'      => '#ffffff',
			'text_color'       => '#ffffff',
			'line_color'       => '#ffffff'
		),
		'popup_posts_meta' => array(
			'category' => false,
			'excerpt'  => true
		),
		'single_post_form_location' => 'bottom',
		'single_post_form_options' => array(
    		'service'                 => 'feedburner',
    		'include_name_field'      => false,
            'feedburner_id'           => '',
            'mailchimp_api_key'       => '',
            'mailchimp_list_id'       => '',
            'getresponse_api_key'     => '',
            'getresponse_list_id'     => '',
            'aweber_list_id'          => '',
            'thanks_page'             => '0',
            'thanks_page_url'         => '',
            'mailerlite_api_key'      => '',
            'mailerlite_list_id'      => '',
            'benchmark_user'          => '',
            'benchmark_pass'          => '',
            'benchmark_list_name'     => '',
            'constantcontact_api_key' => '',
            'constantcontact_token'   => '',
            'constantcontact_list_id' => '',
            'mailrelay_api_key'       => '',
	        'mailrelay_host'          => '',
	        'mailrelay_group_id'      => '',
	        'activecampaignapiurl'    => '',
	        'activecampaignapikey'    => '',
	        'activecampaignlistid'    => ''
        ),
        'single_post_form_labels' => array(
            'title'             => esc_html__( 'Get more stuff like this', 'wp-subscribe' ),
            'text'              => esc_html__( 'Subscribe to our mailing list and get interesting stuff and updates to your email inbox.', 'wp-subscribe' ),
            'name_placeholder'  => esc_html__( 'Enter your name here', 'wp-subscribe' ),
            'email_placeholder' => esc_html__( 'Enter your email here', 'wp-subscribe' ),
            'button_text'       => esc_html__( 'Sign Up Now', 'wp-subscribe' ),
            'success_message'   => esc_html__( 'Thank you for subscribing.', 'wp-subscribe' ),
            'error_message'     => esc_html__( 'Something went wrong.', 'wp-subscribe' ),
            'footer_text'       => sprintf(__( 'The form collects name and email so that we can add you to our newsletter list for project updates. Checkout our %sprivacy policy%s for the full story on how we protect and manage your submitted data!', 'wp-subscribe' ), '<a href="#">', '</a>'),
            'consent_text'      => esc_html__( 'I consent to my submitted data being collected via this form*', 'wp-subscribe' ),
        ),
		'single_post_form_colors' => array(
			'background_color'        => '#f47555',
            'title_color'             => '#FFFFFF',
            'text_color'              => '#FFFFFF',
            'field_text_color'        => '#FFFFFF',
            'field_background_color'  => '#d56144',
            'button_text_color'       => '#f47555',
            'button_background_color' => '#FFFFFF',
            'footer_text_color'       => '#FFFFFF'
        )
	);
}

/**
 * Get plugin options
 * @param  array $defaults
 * @return array
 */
function wps_get_options() {

	return apply_filters( 'wp_subscribe_options', wps()->settings->get('all') );
}

/**
 * Get popup removal delay
 * @return int
 */
function wps_get_popup_removal_delay() {

	$popup_removal_delay = 800;
	$popup_animation_out = wps()->settings->get( 'popup_animation_out' );
	if ( '0' == $popup_animation_out ) {
		$popup_removal_delay = 0;
	} else if ( 'hinge' == $popup_animation_out ) {
		$popup_removal_delay = 2000;
	}

	return $popup_removal_delay;
}
