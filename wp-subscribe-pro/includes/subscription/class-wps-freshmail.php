<?php
/**
 * Freshmail Subscription
 */

class WPS_Subscription_Freshmail extends WPS_Subscription_Base {

	public function init( $api_key, $api_secret ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/freshmail.php';
		return new FreshmailAPI( $api_key, $api_secret );
	}

	public function get_lists( $api_key, $api_secret ) {

		$api = $this->init( $api_key, $api_secret );
		$result = $api->doRequest('subscribers_list/lists');

		$lists = array();
		foreach( $result['lists'] as $list ) {
			$lists[ $list['subscriberListHash'] ] = $list['name'];
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$double_optin = isset( $options['double_optin'] ) && $options['double_optin'] ? true : false;
		$data = array(
            'email' => $identity['email'],
            'list'  => $options['list_id'],
            'confirm' => $double_optin ? 1 : 0,
            'state' => $double_optin ? 2 : 1
        );

		$api = $this->init( $options['api_key'], $options['api_secret'] );
		$result = $api->doRequest('subscriber/add', $data);

		if ( isset( $result['errors'] ) ) {

            // 1304: the subscriber already exists
            if ( 1304 === $result['errors'][0]['code']  ) {
                return array(
					'status' => 'subscribed'
				);
            }
			else {
                throw new Exception( $result['errors'][0]['message'] );
            }
        }

		if( isset( $result['status'] ) && 'OK' === $result['status'] ) {
			return array(
				'status' => 'subscribed'
			);
		}

		throw new Exception( esc_html__( 'Unknown error.', 'wp-subscribe' ) );
	}

	public function get_fields() {

		$fields = array(
			'freshmail_api_key' => array(
				'id'    => 'freshmail_api_key',
				'name'  => 'freshmail_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'FreshMail API Key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API Key of your FreshMail account.', 'wp-subscribe' ),
				'link'  => 'https://app.freshmail.com/en/settings/integration/'
			),

			'freshmail_api_secret' => array(
				'id'    => 'freshmail_api_secret',
				'name'  => 'freshmail_api_secret',
				'type'  => 'text',
				'title' => esc_html__( 'FreshMail API Secret', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API Secret of your FreshMail account.', 'wp-subscribe' ),
			),

			'freshmail_list_id' => array(
				'id'      => 'freshmail_list_id',
				'name'    => 'freshmail_list_id',
				'type'    => 'select',
				'title'   => esc_html__( 'FreshMail List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('freshmail'),
				'is_list' => true
			),

			'freshmail_double_optin' => array(
				'id'    => 'freshmail_double_optin',
				'name'  => 'freshmail_double_optin',
				'type'  => 'checkbox',
				'title' => esc_html__( 'Send double opt-in notification', 'wp-subscribe' )
			)
		);

		return $fields;
	}
}
