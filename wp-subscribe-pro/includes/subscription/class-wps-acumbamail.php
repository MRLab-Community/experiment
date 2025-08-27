<?php
/**
 * Acumbamail Subscription
 */

class WPS_Subscription_Acumbamail extends WPS_Subscription_Base {

	public function init( $customer_id, $auth_token ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/acumbamail.php';
		return new AcumbamailAPI( $customer_id, $auth_token );
	}

	public function get_lists( $customer_id, $auth_token ) {

		$api = $this->init( $customer_id, $auth_token );
		$result = $api->getLists();

		$lists = array();
		foreach( $result as $id => $list ) {
			$lists[ $id ] = $list['name'];
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$api = $this->init( $options['customer_id'], $options['api_token'] );

		$fields = $identity;
        if ( !empty( $identity['name'] ) ) {
            $fields['nombre'] = $identity['name'];
            $fields['name'] = $identity['name'];
        }

		$double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? 1 : 0;
		$result = $api->addSubscriber( $options['list_id'], $fields, $double_optin );

		if( isset( $result['error'] ) ) {

			if ( false === strpos( $result['error'], 'already exists' ) ) {
                throw new Exception( $result['error'] );
            }
		}

		return array(
			'status' => 'subscribed'
		);
	}

	public function get_fields() {

		$fields = array(
			'acumbamail_customer_id' => array(
				'id'    => 'acumbamail_customer_id',
				'name'  => 'acumbamail_customer_id',
				'type'  => 'text',
				'title' => esc_html__( 'Acumbamail Customer ID', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The customer ID of your Acumbamail account.', 'wp-subscribe' ),
				'link'  => 'https://acumbamail.com/apidoc/'
			),

			'acumbamail_api_token' => array(
				'id'    => 'acumbamail_api_token',
				'name'  => 'acumbamail_api_token',
				'type'  => 'text',
				'title' => esc_html__( 'Acumbamail API Token', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API token of your Acumbamail account.', 'wp-subscribe' )
			),

			'acumbamail_list_id' => array(
				'id'      => 'acumbamail_list_id',
				'name'    => 'acumbamail_list_id',
				'type'    => 'select',
				'title'   => esc_html__( 'Acumbamail List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('acumbamail'),
				'is_list' => true
			),

			'acumbamail_double_optin' => array(
				'id'    => 'acumbamail_double_optin',
				'name'  => 'acumbamail_double_optin',
				'type'  => 'checkbox',
				'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
			)
		);

		return $fields;
	}
}
