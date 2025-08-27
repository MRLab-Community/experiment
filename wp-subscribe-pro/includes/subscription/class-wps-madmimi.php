<?php
/**
 * MadMimi Subscription
 */

class WPS_Subscription_MadMimi extends WPS_Subscription_Base {

	public function init( $username, $api_key ) {

		require_once wps()->plugin_dir() . '/includes/subscription/libs/madmimi/MadMimi.class.php';
		return new MadMimi( $username, $api_key );
	}

	public function get_lists( $username, $api_key ) {

		$api = $this->init( $username, $api_key );
		$result = simplexml_load_string( $api->Lists() );

		$lists = array();
		foreach( $result as $list ) {
			$lists[ $list['id']->__toString() ] = $list['name']->__toString();
		}

		return $lists;
	}

    public function subscribe( $identity, $options ) {

		$data = array();
		if ( !empty( $identity['name'] ) ) {
			$data['first_name'] = $identity['name'];
		}

		$api = $this->init( $options['username'], $options['api_key'] );
		$result = $api->AddMembership( $options['list_id'], $identity['email'], $data );

		if ( !empty( $result ) ) {
			throw new Exception( $result );
        }

		return array(
			'status' => 'subscribed'
		);
	}

	public function get_fields() {

		$fields = array(
			'madmimi_username' => array(
				'id'    => 'madmimi_username',
				'name'  => 'madmimi_username',
				'type'  => 'text',
				'title' => esc_html__( 'Mad Mimi Email', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The email of your Mad Mimi account.', 'wp-subscribe' ),
			),

			'madmimi_api_key' => array(
				'id'    => 'madmimi_api_key',
				'name'  => 'madmimi_api_key',
				'type'  => 'text',
				'title' => esc_html__( 'Mad Mimi API Key', 'wp-subscribe' ),
				'desc'  => esc_html__( 'The API key of your Mad Mimi account.', 'wp-subscribe' ),
				'link'  => 'https://help.madmimi.com/where-can-i-find-my-api-key/',
			),

			'madmimi_list_id' => array(
				'id'      => 'madmimi_list_id',
				'name'    => 'madmimi_list_id',
				'type'    => 'select',
				'title'   => esc_html__( 'Mad Mimi List', 'wp-subscribe' ),
				'options' => array( 'none' => esc_html__( 'Select List', 'wp-subscribe' ) ) + wps_get_service_list('madmimi'),
				'is_list' => true
			)
		);

		return $fields;
	}
}
