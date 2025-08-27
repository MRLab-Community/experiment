<?php
/**
 * MailerLite Subscription
 */

class WPS_Subscription_MailerLite extends WPS_Subscription_Base {

	public function init( $api_key ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/mailerlite/autoload.php';
		return new \MailerLiteApi\MailerLite( $api_key );
	}

	public function get_lists( $api_key ) {

		$mailerlite = $this->init( $api_key );
		$result = $mailerlite->groups()->get();

		$lists = array();
		foreach( $result as $list ) {
			$lists[ $list->id ] = $list->name;
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$mailerlite = $this->init( $options['api_key'] );
		$mailerlite = $mailerlite->groups();

		$name = $this->get_fullname( $identity );
		$double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? true : false;
		$result = $mailerlite->addSubscriber( $options['list_id'], array(
			'email'	=> $identity['email'],
			'fields' => array( 'name' => $name ),
			'type' => $double_optin ? 'unconfirmed' : 'subscribed'
		));

		if( isset( $result->error ) ) {
			throw new Exception( $result->error->message );
		}

		if( isset( $result->id ) && isset( $result->email ) ) {
			return array(
				'status' => 'subscribed'
			);
		}

		throw new Exception( esc_html__( 'Unknown error.', 'wp-subscribe' ) );
	}

	public function get_fields() {

		$fields = array(
			'mailerlite_api_key' => array(
				'id'    => 'mailerlite_api_key',
				'name'  => 'mailerlite_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'MailerLite API key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API key of your MailerLite account.', 'wp-subscribe' ),
				'link'  => 'https://kb.mailerlite.com/does-mailerlite-offer-an-api/'
			),

			'mailerlite_list_id' => array(
				'id'      => 'mailerlite_list_id',
				'name'    => 'mailerlite_list_id',
				'type'    => 'select',
				'title'   => esc_html__( 'MailerLite List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('mailerlite'),
				'is_list' => true
			),

			'mailerlite_double_optin' => array(
				'id'    => 'mailerlite_double_optin',
				'name'  => 'mailerlite_double_optin',
				'type'  => 'checkbox',
				'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
			)
		);

		return $fields;
	}
}
